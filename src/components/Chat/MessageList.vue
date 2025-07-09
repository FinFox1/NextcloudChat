<template>
  <div class="message-list" ref="messageContainer">
    <div v-if="loading" class="loading">
      {{ t('nextcloud_chat', 'Loading messages...') }}
    </div>
    
    <div v-else-if="messages.length === 0" class="no-messages">
      {{ t('nextcloud_chat', 'No messages yet. Start the conversation!') }}
    </div>
    
    <div v-else class="messages">
      <div
        v-for="message in messages"
        :key="message.id"
        :class="['message', { 'own-message': message.sender === currentUserId }]"
      >
        <div class="message-avatar">
          <NcAvatar
            :user="message.sender"
            :display-name="message.senderName"
            :size="32"
          />
        </div>
        
        <div class="message-content">
          <div class="message-header">
            <span class="sender-name">{{ message.senderName }}</span>
            <span class="message-time">{{ formatTime(message.timestamp) }}</span>
          </div>
          
          <div class="message-body">
            <div v-if="message.type === 'text'" class="text-message">
              {{ message.content }}
            </div>
            
            <div v-else-if="message.type === 'image'" class="image-message">
              <img :src="message.url" :alt="message.filename" @click="openImage(message.url)" />
            </div>
            
            <div v-else-if="message.type === 'file'" class="file-message">
              <NcButton @click="downloadFile(message.url, message.filename)">
                <template #icon>
                  <FileDocumentIcon />
                </template>
                {{ message.filename }}
              </NcButton>
            </div>
            
            <div v-else-if="message.type === 'call'" class="call-message">
              <div class="call-info">
                <VideoIcon />
                <span>{{ message.callType }} call</span>
                <span class="call-duration">{{ message.duration }}</span>
              </div>
            </div>
          </div>
          
          <div v-if="message.reactions && message.reactions.length > 0" class="message-reactions">
            <span
              v-for="reaction in message.reactions"
              :key="reaction.emoji"
              :class="['reaction', { 'own-reaction': reaction.users.includes(currentUserId) }]"
              @click="toggleReaction(message.id, reaction.emoji)"
            >
              {{ reaction.emoji }} {{ reaction.count }}
            </span>
          </div>
        </div>
        
        <div class="message-actions">
          <NcActions>
            <NcActionButton @click="replyToMessage(message)">
              <template #icon>
                <ReplyIcon />
              </template>
              {{ t('nextcloud_chat', 'Reply') }}
            </NcActionButton>
            <NcActionButton @click="showReactionPicker(message)">
              <template #icon>
                <EmoticonIcon />
              </template>
              {{ t('nextcloud_chat', 'React') }}
            </NcActionButton>
            <NcActionButton v-if="message.sender === currentUserId" @click="deleteMessage(message.id)">
              <template #icon>
                <DeleteIcon />
              </template>
              {{ t('nextcloud_chat', 'Delete') }}
            </NcActionButton>
          </NcActions>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {
  NcAvatar,
  NcButton,
  NcActions,
  NcActionButton
} from '@nextcloud/vue'
import { mapState } from 'vuex'

export default {
  name: 'MessageList',
  components: {
    NcAvatar,
    NcButton,
    NcActions,
    NcActionButton
  },
  props: {
    roomId: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      messages: [],
      loading: false
    }
  },
  computed: {
    ...mapState(['matrixClient', 'currentUser']),
    currentUserId() {
      return this.currentUser
    }
  },
  mounted() {
    this.loadMessages()
    this.setupMessageListener()
  },
  beforeUnmount() {
    this.removeMessageListener()
  },
  methods: {
    async loadMessages() {
      if (!this.matrixClient || !this.roomId) return
      
      this.loading = true
      try {
        const room = this.matrixClient.getRoom(this.roomId)
        if (room) {
          const timeline = room.getLiveTimeline()
          const events = timeline.getEvents()
          
          this.messages = events
            .filter(event => event.getType() === 'm.room.message')
            .map(event => this.formatMessage(event))
            .reverse()
        }
      } catch (error) {
        console.error('Failed to load messages:', error)
      } finally {
        this.loading = false
      }
    },
    
    setupMessageListener() {
      if (this.matrixClient) {
        this.matrixClient.on('Room.timeline', this.onNewMessage)
      }
    },
    
    removeMessageListener() {
      if (this.matrixClient) {
        this.matrixClient.off('Room.timeline', this.onNewMessage)
      }
    },
    
    onNewMessage(event, room) {
      if (room.roomId === this.roomId && event.getType() === 'm.room.message') {
        const message = this.formatMessage(event)
        this.messages.push(message)
        this.$nextTick(() => {
          this.scrollToBottom()
        })
      }
    },
    
    formatMessage(event) {
      const content = event.getContent()
      const sender = event.getSender()
      
      return {
        id: event.getId(),
        sender,
        senderName: this.getUserDisplayName(sender),
        timestamp: event.getTs(),
        type: this.getMessageType(content),
        content: content.body || '',
        url: content.url,
        filename: content.filename,
        reactions: this.getMessageReactions(event)
      }
    },
    
    getMessageType(content) {
      if (content.msgtype === 'm.image') return 'image'
      if (content.msgtype === 'm.file') return 'file'
      if (content.msgtype === 'm.call') return 'call'
      return 'text'
    },
    
    getUserDisplayName(userId) {
      const room = this.matrixClient.getRoom(this.roomId)
      const member = room?.getMember(userId)
      return member?.name || userId
    },
    
    getMessageReactions(event) {
      // Implementation would depend on Matrix reactions spec
      return []
    },
    
    formatTime(timestamp) {
      const date = new Date(timestamp)
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    },
    
    scrollToBottom() {
      const container = this.$refs.messageContainer
      if (container) {
        container.scrollTop = container.scrollHeight
      }
    },
    
    replyToMessage(message) {
      this.$emit('reply-to-message', message)
    },
    
    showReactionPicker(message) {
      // Implementation for reaction picker
      console.log('Show reaction picker for message:', message.id)
    },
    
    toggleReaction(messageId, emoji) {
      // Implementation for toggling reactions
      console.log('Toggle reaction:', messageId, emoji)
    },
    
    deleteMessage(messageId) {
      // Implementation for deleting messages
      console.log('Delete message:', messageId)
    },
    
    openImage(url) {
      // Implementation for opening images in lightbox
      window.open(url, '_blank')
    },
    
    downloadFile(url, filename) {
      const link = document.createElement('a')
      link.href = url
      link.download = filename
      link.click()
    }
  }
}
</script>

<style scoped>
.message-list {
  height: 100%;
  overflow-y: auto;
  padding: 16px;
}

.loading,
.no-messages {
  text-align: center;
  color: var(--color-text-maxcontrast);
  padding: 32px;
}

.message {
  display: flex;
  margin-bottom: 16px;
  gap: 12px;
}

.message.own-message {
  flex-direction: row-reverse;
}

.message.own-message .message-content {
  background: var(--color-primary-light);
  border-radius: 16px 4px 16px 16px;
}

.message-content {
  flex: 1;
  background: var(--color-background-hover);
  border-radius: 4px 16px 16px 16px;
  padding: 8px 12px;
  max-width: 70%;
}

.message-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.sender-name {
  font-weight: 600;
  font-size: 14px;
}

.message-time {
  font-size: 12px;
  color: var(--color-text-maxcontrast);
}

.message-body {
  margin-bottom: 4px;
}

.image-message img {
  max-width: 100%;
  max-height: 300px;
  border-radius: 8px;
  cursor: pointer;
}

.file-message,
.call-message {
  display: flex;
  align-items: center;
  gap: 8px;
}

.call-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.message-reactions {
  display: flex;
  gap: 4px;
  margin-top: 4px;
}

.reaction {
  background: var(--color-background-dark);
  border-radius: 12px;
  padding: 2px 6px;
  font-size: 12px;
  cursor: pointer;
}

.reaction.own-reaction {
  background: var(--color-primary);
  color: white;
}

.message-actions {
  opacity: 0;
  transition: opacity 0.2s;
}

.message:hover .message-actions {
  opacity: 1;
}
</style>
