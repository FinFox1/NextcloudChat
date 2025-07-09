<template>
  <div class="message-input">
    <div v-if="replyingTo" class="reply-preview">
      <div class="reply-content">
        <span class="reply-label">{{ t('nextcloud_chat', 'Replying to') }} {{ replyingTo.senderName }}:</span>
        <span class="reply-text">{{ replyingTo.content }}</span>
      </div>
      <NcButton @click="cancelReply" type="tertiary">
        <template #icon>
          <CloseIcon />
        </template>
      </NcButton>
    </div>

    <div class="input-container">
      <div class="file-actions">
        <NcButton @click="$refs.fileInput.click()" type="tertiary">
          <template #icon>
            <AttachmentIcon />
          </template>
        </NcButton>
        
        <input
          ref="fileInput"
          type="file"
          @change="handleFileUpload"
          style="display: none"
          multiple
        />
      </div>

      <div class="text-input">
        <NcTextField
          :value="messageText"
          :placeholder="t('nextcloud_chat', 'Type a message...')"
          @update:value="messageText = $event"
          @keydown="handleKeyDown"
          multiline
          :rows="Math.min(messageText.split('\n').length, 4)"
        />
      </div>

      <div class="send-actions">
        <NcButton @click="showEmojiPicker = !showEmojiPicker" type="tertiary">
          <template #icon>
            <EmoticonIcon />
          </template>
        </NcButton>
        
        <NcButton
          @click="sendMessage"
          type="primary"
          :disabled="!messageText.trim() && !selectedFiles.length"
        >
          <template #icon>
            <SendIcon />
          </template>
        </NcButton>
      </div>
    </div>

    <div v-if="selectedFiles.length > 0" class="file-preview">
      <div
        v-for="(file, index) in selectedFiles"
        :key="index"
        class="file-item"
      >
        <div class="file-info">
          <FileDocumentIcon v-if="!isImage(file)" />
          <img v-else :src="getFilePreview(file)" class="image-preview" />
          <span class="file-name">{{ file.name }}</span>
          <span class="file-size">{{ formatFileSize(file.size) }}</span>
        </div>
        <NcButton @click="removeFile(index)" type="tertiary">
          <template #icon>
            <CloseIcon />
          </template>
        </NcButton>
      </div>
    </div>

    <!-- Emoji Picker -->
    <div v-if="showEmojiPicker" class="emoji-picker">
      <EmojiPicker @emoji-selected="insertEmoji" />
    </div>

    <!-- Typing Indicator -->
    <div v-if="typingUsers.length > 0" class="typing-indicator">
      <span>{{ getTypingText() }}</span>
    </div>
  </div>
</template>

<script>
import {
  NcTextField,
  NcButton
} from '@nextcloud/vue'
import { mapState } from 'vuex'
import EmojiPicker from './EmojiPicker.vue'

export default {
  name: 'MessageInput',
  components: {
    NcTextField,
    NcButton,
    EmojiPicker
  },
  props: {
    roomId: {
      type: String,
      required: true
    },
    replyingTo: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      messageText: '',
      selectedFiles: [],
      showEmojiPicker: false,
      typingUsers: [],
      typingTimer: null
    }
  },
  computed: {
    ...mapState(['matrixClient', 'currentUser'])
  },
  methods: {
    async sendMessage() {
      if (!this.messageText.trim() && this.selectedFiles.length === 0) return

      try {
        if (this.selectedFiles.length > 0) {
          await this.sendFilesMessage()
        } else {
          await this.sendTextMessage()
        }

        // Clear input
        this.messageText = ''
        this.selectedFiles = []
        this.cancelReply()
      } catch (error) {
        console.error('Failed to send message:', error)
      }
    },

    async sendTextMessage() {
      const content = {
        msgtype: 'm.text',
        body: this.messageText
      }

      if (this.replyingTo) {
        content['m.relates_to'] = {
          'm.in_reply_to': {
            event_id: this.replyingTo.id
          }
        }
      }

      await this.matrixClient.sendEvent(this.roomId, 'm.room.message', content)
    },

    async sendFilesMessage() {
      for (const file of this.selectedFiles) {
        await this.uploadAndSendFile(file)
      }
    },

    async uploadAndSendFile(file) {
      try {
        // Upload file to Matrix media repository
        const upload = await this.matrixClient.uploadContent(file)
        
        const content = {
          msgtype: this.isImage(file) ? 'm.image' : 'm.file',
          body: file.name,
          url: upload.content_uri,
          info: {
            size: file.size,
            mimetype: file.type
          }
        }

        if (this.isImage(file)) {
          // Add image dimensions if possible
          const img = new Image()
          img.onload = () => {
            content.info.w = img.width
            content.info.h = img.height
          }
          img.src = URL.createObjectURL(file)
        }

        await this.matrixClient.sendEvent(this.roomId, 'm.room.message', content)
      } catch (error) {
        console.error('Failed to upload file:', error)
      }
    },

    handleFileUpload(event) {
      const files = Array.from(event.target.files)
      this.selectedFiles = [...this.selectedFiles, ...files]
      event.target.value = '' // Reset input
    },

    removeFile(index) {
      this.selectedFiles.splice(index, 1)
    },

    isImage(file) {
      return file.type.startsWith('image/')
    },

    getFilePreview(file) {
      if (this.isImage(file)) {
        return URL.createObjectURL(file)
      }
      return null
    },

    formatFileSize(bytes) {
      if (bytes === 0) return '0 B'
      const k = 1024
      const sizes = ['B', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    },

    handleKeyDown(event) {
      if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault()
        this.sendMessage()
      } else if (event.key === 'Escape') {
        this.cancelReply()
      } else {
        this.handleTyping()
      }
    },

    handleTyping() {
      // Send typing notification
      if (this.matrixClient && this.roomId) {
        this.matrixClient.sendTyping(this.roomId, true, 3000)
        
        // Clear previous timer
        if (this.typingTimer) {
          clearTimeout(this.typingTimer)
        }
        
        // Stop typing after 3 seconds
        this.typingTimer = setTimeout(() => {
          this.matrixClient.sendTyping(this.roomId, false)
        }, 3000)
      }
    },

    insertEmoji(emoji) {
      this.messageText += emoji
      this.showEmojiPicker = false
    },

    cancelReply() {
      this.$emit('cancel-reply')
    },

    getTypingText() {
      if (this.typingUsers.length === 1) {
        return this.t('nextcloud_chat', '{user} is typing...', { user: this.typingUsers[0] })
      } else if (this.typingUsers.length === 2) {
        return this.t('nextcloud_chat', '{user1} and {user2} are typing...', {
          user1: this.typingUsers[0],
          user2: this.typingUsers[1]
        })
      } else if (this.typingUsers.length > 2) {
        return this.t('nextcloud_chat', 'Several people are typing...')
      }
      return ''
    }
  }
}
</script>

<style scoped>
.message-input {
  background: var(--color-main-background);
  border-top: 1px solid var(--color-border);
}

.reply-preview {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 16px;
  background: var(--color-background-hover);
  border-left: 3px solid var(--color-primary);
}

.reply-content {
  display: flex;
  flex-direction: column;
  font-size: 14px;
}

.reply-label {
  font-weight: 600;
  color: var(--color-primary);
}

.reply-text {
  color: var(--color-text-maxcontrast);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  max-width: 300px;
}

.input-container {
  display: flex;
  align-items: flex-end;
  padding: 8px 16px;
  gap: 8px;
}

.text-input {
  flex: 1;
}

.file-actions,
.send-actions {
  display: flex;
  gap: 4px;
}

.file-preview {
  padding: 8px 16px;
  border-top: 1px solid var(--color-border);
  background: var(--color-background-hover);
}

.file-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 4px 0;
}

.file-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.image-preview {
  width: 32px;
  height: 32px;
  object-fit: cover;
  border-radius: 4px;
}

.file-name {
  font-weight: 500;
}

.file-size {
  color: var(--color-text-maxcontrast);
  font-size: 12px;
}

.emoji-picker {
  position: absolute;
  bottom: 100%;
  right: 16px;
  z-index: 1000;
  background: var(--color-main-background);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.typing-indicator {
  padding: 4px 16px;
  font-size: 12px;
  color: var(--color-text-maxcontrast);
  font-style: italic;
}
</style>
