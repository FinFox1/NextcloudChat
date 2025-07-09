import { createStore } from 'vuex'
import { createClient } from 'matrix-js-sdk'
import { generateUrl } from '@nextcloud/router'

export default createStore({
  state: {
    matrixClient: null,
    currentRoom: null,
    rooms: [],
    elementConfig: null,
    isAuthenticated: false,
    currentUser: null,
    activeCalls: [],
    notifications: []
  },
  
  getters: {
    isConnected: state => state.matrixClient && state.isAuthenticated,
    getRoomById: state => id => state.rooms.find(room => room.roomId === id),
    getActiveCall: state => roomId => state.activeCalls.find(call => call.roomId === roomId)
  },
  
  mutations: {
    setMatrixClient(state, client) {
      state.matrixClient = client
    },
    
    setCurrentRoom(state, room) {
      state.currentRoom = room
    },
    
    setRooms(state, rooms) {
      state.rooms = rooms
    },
    
    addRoom(state, room) {
      const existingIndex = state.rooms.findIndex(r => r.roomId === room.roomId)
      if (existingIndex >= 0) {
        state.rooms[existingIndex] = room
      } else {
        state.rooms.push(room)
      }
    },
    
    removeRoom(state, roomId) {
      state.rooms = state.rooms.filter(room => room.roomId !== roomId)
    },
    
    setElementConfig(state, config) {
      state.elementConfig = config
    },
    
    setAuthenticated(state, status) {
      state.isAuthenticated = status
    },
    
    setCurrentUser(state, user) {
      state.currentUser = user
    },
    
    addActiveCall(state, call) {
      state.activeCalls.push(call)
    },
    
    removeActiveCall(state, roomId) {
      state.activeCalls = state.activeCalls.filter(call => call.roomId !== roomId)
    },
    
    addNotification(state, notification) {
      state.notifications.push({
        id: Date.now(),
        ...notification
      })
    },
    
    removeNotification(state, id) {
      state.notifications = state.notifications.filter(n => n.id !== id)
    }
  },
  
  actions: {
    async initializeMatrix({ commit, dispatch }) {
      try {
        // Fetch configuration from Nextcloud
        const response = await fetch(generateUrl('/apps/nextcloud_chat/api/config'))
        const config = await response.json()
        
        commit('setElementConfig', config.element_config)
        
        // Initialize Matrix client
        const client = createClient({
          baseUrl: config.matrix_server,
          userId: OC.getCurrentUser().uid + ':' + config.element_config.default_server_config['m.homeserver'].server_name
        })
        
        commit('setMatrixClient', client)
        
        // Set up event listeners
        client.on('Room.timeline', (event, room) => {
          dispatch('handleRoomEvent', { event, room })
        })
        
        client.on('sync', (state) => {
          if (state === 'PREPARED') {
            dispatch('loadRooms')
          }
        })
        
        // Authenticate
        await dispatch('authenticateMatrix')
        
      } catch (error) {
        console.error('Failed to initialize Matrix:', error)
        commit('addNotification', {
          type: 'error',
          message: 'Failed to connect to Matrix server'
        })
      }
    },
    
    async authenticateMatrix({ commit, state }) {
      try {
        const response = await fetch(generateUrl('/apps/nextcloud_chat/api/authenticate'), {
          method: 'POST'
        })
        const data = await response.json()
        
        if (data.access_token) {
          state.matrixClient.setAccessToken(data.access_token)
          await state.matrixClient.startClient()
          commit('setAuthenticated', true)
          commit('setCurrentUser', state.matrixClient.getUserId())
        }
      } catch (error) {
        console.error('Matrix authentication failed:', error)
        commit('addNotification', {
          type: 'error',
          message: 'Authentication failed'
        })
      }
    },
    
    async loadRooms({ commit, state }) {
      if (!state.matrixClient) return
      
      try {
        const rooms = state.matrixClient.getRooms().map(room => ({
          roomId: room.roomId,
          name: room.name || 'Unnamed Room',
          topic: room.currentState.getStateEvents('m.room.topic', '')?.getContent()?.topic || '',
          memberCount: room.getJoinedMemberCount(),
          avatarUrl: room.getAvatarUrl() || '',
          lastMessage: this.getLastMessage(room),
          unreadCount: room.getUnreadNotificationCount()
        }))
        
        commit('setRooms', rooms)
      } catch (error) {
        console.error('Failed to load rooms:', error)
      }
    },
    
    async createRoom({ commit, state }, { name, topic, isPublic }) {
      try {
        const response = await fetch(generateUrl('/apps/nextcloud_chat/api/rooms'), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ name, topic, public: isPublic })
        })
        
        const data = await response.json()
        if (data.room_id) {
          // Room will be added via sync
          commit('addNotification', {
            type: 'success',
            message: `Room "${name}" created successfully`
          })
          return data.room_id
        }
      } catch (error) {
        console.error('Failed to create room:', error)
        commit('addNotification', {
          type: 'error',
          message: 'Failed to create room'
        })
      }
    },
    
    async joinRoom({ commit }, roomId) {
      try {
        const response = await fetch(generateUrl(`/apps/nextcloud_chat/api/rooms/${roomId}/join`), {
          method: 'POST'
        })
        
        if (response.ok) {
          commit('addNotification', {
            type: 'success',
            message: 'Joined room successfully'
          })
        }
      } catch (error) {
        console.error('Failed to join room:', error)
        commit('addNotification', {
          type: 'error',
          message: 'Failed to join room'
        })
      }
    },
    
    async leaveRoom({ commit }, roomId) {
      try {
        const response = await fetch(generateUrl(`/apps/nextcloud_chat/api/rooms/${roomId}/leave`), {
          method: 'POST'
        })
        
        if (response.ok) {
          commit('removeRoom', roomId)
          commit('addNotification', {
            type: 'success',
            message: 'Left room successfully'
          })
        }
      } catch (error) {
        console.error('Failed to leave room:', error)
        commit('addNotification', {
          type: 'error',
          message: 'Failed to leave room'
        })
      }
    },
    
    async startCall({ commit }, roomId) {
      try {
        const response = await fetch(generateUrl(`/apps/nextcloud_chat/api/rooms/${roomId}/call`), {
          method: 'POST'
        })
        
        const data = await response.json()
        if (data.call_url) {
          commit('addActiveCall', {
            roomId,
            callUrl: data.call_url,
            startTime: Date.now()
          })
          return data.call_url
        }
      } catch (error) {
        console.error('Failed to start call:', error)
        commit('addNotification', {
          type: 'error',
          message: 'Failed to start call'
        })
      }
    },
    
    async endCall({ commit }, roomId) {
      try {
        const response = await fetch(generateUrl(`/apps/nextcloud_chat/api/rooms/${roomId}/call`), {
          method: 'DELETE'
        })
        
        if (response.ok) {
          commit('removeActiveCall', roomId)
        }
      } catch (error) {
        console.error('Failed to end call:', error)
      }
    },
    
    handleRoomEvent({ commit }, { event, room }) {
      // Handle incoming messages and events
      if (event.getType() === 'm.room.message') {
        // Update room in store
        commit('addRoom', {
          roomId: room.roomId,
          name: room.name || 'Unnamed Room',
          topic: room.currentState.getStateEvents('m.room.topic', '')?.getContent()?.topic || '',
          memberCount: room.getJoinedMemberCount(),
          avatarUrl: room.getAvatarUrl() || '',
          lastMessage: event.getContent().body,
          unreadCount: room.getUnreadNotificationCount()
        })
      }
    }
  }
})
