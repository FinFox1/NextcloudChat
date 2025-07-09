<template>
  <div class="chat-room" v-if="room">
    <div class="room-header">
      <div class="room-info">
        <NcAvatar
          v-if="room.avatarUrl"
          :url="room.avatarUrl"
          :size="40"
        />
        <div v-else class="room-avatar-large">
          {{ room.name.charAt(0).toUpperCase() }}
        </div>
        <div class="room-details">
          <h3>{{ room.name }}</h3>
          <p v-if="room.topic">{{ room.topic }}</p>
          <p class="member-count">{{ room.memberCount }} {{ t('nextcloud_chat', 'members') }}</p>
        </div>
      </div>
      
      <div class="room-actions">
        <NcButton @click="startVideoCall" type="primary">
          <template #icon>
            <VideoIcon />
          </template>
          {{ t('nextcloud_chat', 'Video Call') }}
        </NcButton>
        <NcButton @click="showUserList = !showUserList">
          <template #icon>
            <AccountMultipleIcon />
          </template>
        </NcButton>
        <NcButton @click="$emit('open-settings')">
          <template #icon>
            <CogIcon />
          </template>
        </NcButton>
      </div>
    </div>

    <div class="room-content">
      <div class="messages-container">
        <MessageList :room-id="room.roomId" />
      </div>
      
      <div v-if="showUserList" class="user-list-sidebar">
        <UserList :room-id="room.roomId" />
      </div>
    </div>

    <div class="message-input-container">
      <MessageInput :room-id="room.roomId" />
    </div>
  </div>
  
  <div v-else class="no-room-selected">
    <div class="placeholder">
      <h3>{{ t('nextcloud_chat', 'Select a room to start chatting') }}</h3>
      <p>{{ t('nextcloud_chat', 'Choose a room from the list or create a new one') }}</p>
    </div>
  </div>
</template>

<script>
import {
  NcAvatar,
  NcButton
} from '@nextcloud/vue'
import { mapActions } from 'vuex'
import MessageList from './MessageList.vue'
import MessageInput from './MessageInput.vue'
import UserList from './UserList.vue'

export default {
  name: 'ChatRoom',
  components: {
    NcAvatar,
    NcButton,
    MessageList,
    MessageInput,
    UserList
  },
  props: {
    room: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      showUserList: false
    }
  },
  methods: {
    ...mapActions(['startCall']),
    async startVideoCall() {
      if (this.room) {
        await this.startCall(this.room.roomId)
        this.$router.push(`/call/${this.room.roomId}`)
      }
    }
  }
}
</script>

<style scoped>
.chat-room {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.room-header {
  padding: 16px;
  border-bottom: 1px solid var(--color-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--color-background-dark);
}

.room-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.room-avatar-large {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--color-primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 18px;
}

.room-details h3 {
  margin: 0;
  font-size: 18px;
}

.room-details p {
  margin: 2px 0;
  color: var(--color-text-maxcontrast);
  font-size: 14px;
}

.room-actions {
  display: flex;
  gap: 8px;
}

.room-content {
  flex: 1;
  display: flex;
  min-height: 0;
}

.messages-container {
  flex: 1;
  min-width: 0;
}

.user-list-sidebar {
  width: 250px;
  border-left: 1px solid var(--color-border);
  background: var(--color-background-dark);
}

.message-input-container {
  border-top: 1px solid var(--color-border);
  background: var(--color-main-background);
}

.no-room-selected {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.placeholder {
  text-align: center;
  color: var(--color-text-maxcontrast);
}

.placeholder h3 {
  margin-bottom: 8px;
}
</style>
