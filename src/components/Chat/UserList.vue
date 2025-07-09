<template>
  <div class="user-list">
    <div class="user-list-header">
      <h4>{{ t('nextcloud_chat', 'Members') }} ({{ users.length }})</h4>
      <NcButton @click="showInviteDialog = true" type="tertiary">
        <template #icon>
          <AccountPlusIcon />
        </template>
      </NcButton>
    </div>

    <div class="user-search" v-if="users.length > 10">
      <NcTextField
        :value="searchQuery"
        :placeholder="t('nextcloud_chat', 'Search members...')"
        @update:value="searchQuery = $event"
      >
        <template #trailing-button-icon>
          <MagnifyIcon />
        </template>
      </NcTextField>
    </div>

    <div class="users">
      <div
        v-for="user in filteredUsers"
        :key="user.userId"
        :class="['user-item', { 'online': user.presence === 'online' }]"
      >
        <NcAvatar
          :user="user.userId"
          :display-name="user.displayName"
          :size="32"
        />
        
        <div class="user-info">
          <div class="user-name">{{ user.displayName }}</div>
          <div class="user-status">
            <span :class="['presence-indicator', user.presence]"></span>
            <span class="presence-text">{{ getPresenceText(user.presence) }}</span>
          </div>
        </div>

        <div class="user-actions">
          <NcActions>
            <NcActionButton @click="startDirectChat(user)">
              <template #icon>
                <MessageIcon />
              </template>
              {{ t('nextcloud_chat', 'Message') }}
            </NcActionButton>
            
            <NcActionButton @click="startVideoCall(user)">
              <template #icon>
                <VideoIcon />
              </template>
              {{ t('nextcloud_chat', 'Video call') }}
            </NcActionButton>
            
            <NcActionButton v-if="canModerate" @click="kickUser(user)">
              <template #icon>
                <AccountRemoveIcon />
              </template>
              {{ t('nextcloud_chat', 'Remove from room') }}
            </NcActionButton>
          </NcActions>
        </div>
      </div>
    </div>

    <!-- Invite Dialog -->
    <NcDialog
      :open="showInviteDialog"
      @update:open="showInviteDialog = false"
    >
      <template #header>
        {{ t('nextcloud_chat', 'Invite Users') }}
      </template>
      <InviteUsersForm
        :room-id="roomId"
        @users-invited="onUsersInvited"
        @cancel="showInviteDialog = false"
      />
    </NcDialog>
  </div>
</template>

<script>
import {
  NcAvatar,
  NcButton,
  NcTextField,
  NcActions,
  NcActionButton,
  NcDialog
} from '@nextcloud/vue'
import { mapState } from 'vuex'
import InviteUsersForm from './InviteUsersForm.vue'

export default {
  name: 'UserList',
  components: {
    NcAvatar,
    NcButton,
    NcTextField,
    NcActions,
    NcActionButton,
    NcDialog,
    InviteUsersForm
  },
  props: {
    roomId: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      users: [],
      searchQuery: '',
      showInviteDialog: false
    }
  },
  computed: {
    ...mapState(['matrixClient', 'currentUser']),
    filteredUsers() {
      if (!this.searchQuery) return this.users
      return this.users.filter(user =>
        user.displayName.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
        user.userId.toLowerCase().includes(this.searchQuery.toLowerCase())
      )
    },
    canModerate() {
      // Check if current user has moderation permissions
      const room = this.matrixClient?.getRoom(this.roomId)
      const member = room?.getMember(this.currentUser)
      return member?.powerLevel >= 50
    }
  },
  mounted() {
    this.loadUsers()
    this.setupUserListeners()
  },
  beforeUnmount() {
    this.removeUserListeners()
  },
  methods: {
    loadUsers() {
      if (!this.matrixClient || !this.roomId) return

      const room = this.matrixClient.getRoom(this.roomId)
      if (room) {
        const members = room.getJoinedMembers()
        this.users = members.map(member => ({
          userId: member.userId,
          displayName: member.name || member.userId,
          avatarUrl: member.getAvatarUrl(),
          presence: this.getUserPresence(member.userId),
          powerLevel: member.powerLevel || 0
        }))
      }
    },

    setupUserListeners() {
      if (this.matrixClient) {
        this.matrixClient.on('RoomMember.membership', this.onMembershipChange)
        this.matrixClient.on('User.presence', this.onPresenceChange)
      }
    },

    removeUserListeners() {
      if (this.matrixClient) {
        this.matrixClient.off('RoomMember.membership', this.onMembershipChange)
        this.matrixClient.off('User.presence', this.onPresenceChange)
      }
    },

    onMembershipChange(event, member) {
      if (member.roomId === this.roomId) {
        this.loadUsers()
      }
    },

    onPresenceChange(event, user) {
      const userIndex = this.users.findIndex(u => u.userId === user.userId)
      if (userIndex >= 0) {
        this.users[userIndex].presence = user.presence
      }
    },

    getUserPresence(userId) {
      const user = this.matrixClient?.getUser(userId)
      return user?.presence || 'offline'
    },

    getPresenceText(presence) {
      switch (presence) {
        case 'online':
          return this.t('nextcloud_chat', 'Online')
        case 'unavailable':
          return this.t('nextcloud_chat', 'Away')
        case 'offline':
          return this.t('nextcloud_chat', 'Offline')
        default:
          return this.t('nextcloud_chat', 'Unknown')
      }
    },

    async startDirectChat(user) {
      try {
        // Create or find existing direct message room
        const dmRoomId = await this.matrixClient.createRoom({
          invite: [user.userId],
          is_direct: true,
          preset: 'trusted_private_chat'
        })

        this.$store.commit('setCurrentRoom', { roomId: dmRoomId })
        this.$router.push('/')
      } catch (error) {
        console.error('Failed to start direct chat:', error)
      }
    },

    async startVideoCall(user) {
      // Start a direct video call
      await this.startDirectChat(user)
      // Then start a call in that room
      // Implementation would depend on your calling system
    },

    async kickUser(user) {
      if (confirm(this.t('nextcloud_chat', 'Are you sure you want to remove {user} from this room?', { user: user.displayName }))) {
        try {
          await this.matrixClient.kick(this.roomId, user.userId)
        } catch (error) {
          console.error('Failed to kick user:', error)
        }
      }
    },

    onUsersInvited() {
      this.showInviteDialog = false
      this.loadUsers()
    }
  }
}
</script>

<style scoped>
.user-list {
  height: 100%;
  display: flex;
  flex-direction: column;
  background: var(--color-background-dark);
}

.user-list-header {
  padding: 16px;
  border-bottom: 1px solid var(--color-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.user-list-header h4 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}

.user-search {
  padding: 8px 16px;
  border-bottom: 1px solid var(--color-border);
}

.users {
  flex: 1;
  overflow-y: auto;
  padding: 8px;
}

.user-item {
  display: flex;
  align-items: center;
  padding: 8px;
  border-radius: 8px;
  gap: 12px;
  transition: background-color 0.2s;
}

.user-item:hover {
  background: var(--color-background-hover);
}

.user-info {
  flex: 1;
  min-width: 0;
}

.user-name {
  font-weight: 500;
  font-size: 14px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.user-status {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: var(--color-text-maxcontrast);
}

.presence-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--color-text-maxcontrast);
}

.presence-indicator.online {
  background: var(--color-success);
}

.presence-indicator.unavailable {
  background: var(--color-warning);
}

.presence-indicator.offline {
  background: var(--color-text-maxcontrast);
}

.user-actions {
  opacity: 0;
  transition: opacity 0.2s;
}

.user-item:hover .user-actions {
  opacity: 1;
}
</style>
