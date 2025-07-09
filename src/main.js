import { createApp } from 'vue'
import { createRouter, createWebHashHistory } from 'vue-router'
import { createStore } from 'vuex'
import App from './App.vue'
import { NextcloudVuePlugin } from '@nextcloud/vue'

// Import Element Web components
import { createClient } from 'matrix-js-sdk'

// Router setup
const routes = [
  { path: '/', component: () => import('./views/ChatView.vue') },
  { path: '/settings', component: () => import('./views/SettingsView.vue') },
  { path: '/call/:roomId', component: () => import('./views/CallView.vue') }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

// Store setup
const store = createStore({
  state: {
    matrixClient: null,
    currentRoom: null,
    elementConfig: null,
    isAuthenticated: false
  },
  mutations: {
    setMatrixClient(state, client) {
      state.matrixClient = client
    },
    setCurrentRoom(state, room) {
      state.currentRoom = room
    },
    setElementConfig(state, config) {
      state.elementConfig = config
    },
    setAuthenticated(state, status) {
      state.isAuthenticated = status
    }
  },
  actions: {
    async initializeMatrix({ commit }) {
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
        
        // Authenticate
        await this.dispatch('authenticateMatrix')
        
      } catch (error) {
        console.error('Failed to initialize Matrix:', error)
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
        }
      } catch (error) {
        console.error('Matrix authentication failed:', error)
      }
    }
  }
})

// Create Vue app
const app = createApp(App)
app.use(NextcloudVuePlugin)
app.use(router)
app.use(store)

// Mount app
app.mount('#content')

// Initialize Matrix when app starts
store.dispatch('initializeMatrix')
