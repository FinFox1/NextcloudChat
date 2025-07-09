<template>
  <div class="call-view">
    <div class="call-header">
      <h2>{{ roomName }}</h2>
      <div class="call-controls">
        <NcButton @click="toggleMute" :class="{ active: isMuted }">
          <template #icon>
            <MicrophoneIcon v-if="!isMuted" />
            <MicrophoneOffIcon v-else />
          </template>
        </NcButton>
        <NcButton @click="toggleVideo" :class="{ active: !isVideoEnabled }">
          <template #icon>
            <VideoIcon v-if="isVideoEnabled" />
            <VideoOffIcon v-else />
          </template>
        </NcButton>
        <NcButton @click="endCall" type="error">
          <template #icon>
            <PhoneHangupIcon />
          </template>
          End Call
        </NcButton>
      </div>
    </div>
    
    <div class="call-container">
      <div id="element-call-embed" ref="callEmbed"></div>
    </div>
    
    <div class="call-info" v-if="callInfo">
      <p>{{ participants.length }} participants</p>
      <p>Duration: {{ callDuration }}</p>
    </div>
  </div>
</template>

<script>
import { NcButton } from '@nextcloud/vue'
import { mapState, mapActions } from 'vuex'

export default {
  name: 'CallView',
  components: {
    NcButton
  },
  props: {
    roomId: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      isMuted: false,
      isVideoEnabled: true,
      participants: [],
      callStartTime: null,
      callDuration: '00:00'
    }
  },
  computed: {
    ...mapState(['elementConfig', 'currentRoom']),
    roomName() {
      return this.currentRoom?.name || 'Video Call'
    },
    callInfo() {
      return this.$store.getters.getActiveCall(this.roomId)
    }
  },
  mounted() {
    this.initializeCall()
    this.startDurationTimer()
  },
  beforeUnmount() {
    this.cleanup()
  },
  methods: {
    ...mapActions(['startCall', 'endCall']),
    
    async initializeCall() {
      try {
        const callUrl = await this.startCall(this.roomId)
        if (callUrl) {
          this.embedElementCall(callUrl)
        }
      } catch (error) {
        console.error('Failed to initialize call:', error)
        this.$router.push('/')
      }
    },
    
    embedElementCall(callUrl) {
      const iframe = document.createElement('iframe')
      iframe.src = callUrl
      iframe.style.width = '100%'
      iframe.style.height = '100%'
      iframe.style.border = 'none'
      iframe.allow = 'camera; microphone; display-capture; fullscreen'
      
      this.$refs.callEmbed.appendChild(iframe)
      
      // Listen for call events
      window.addEventListener('message', this.handleCallMessage)
    },
    
    handleCallMessage(event) {
      if (event.data.type === 'element-call') {
        switch (event.data.action) {
          case 'participants-changed':
            this.participants = event.data.participants
            break
          case 'mute-changed':
            this.isMuted = event.data.muted
            break
          case 'video-changed':
            this.isVideoEnabled = event.data.enabled
            break
          case 'call-ended':
            this.handleCallEnded()
            break
        }
      }
    },
    
    toggleMute() {
      this.isMuted = !this.isMuted
      // Send message to Element Call iframe
      this.sendCallMessage('toggle-mute')
    },
    
    toggleVideo() {
      this.isVideoEnabled = !this.isVideoEnabled
      // Send message to Element Call iframe
      this.sendCallMessage('toggle-video')
    },
    
    sendCallMessage(action, data = {}) {
      const iframe = this.$refs.callEmbed.querySelector('iframe')
      if (iframe) {
        iframe.contentWindow.postMessage({
          type: 'element-call-control',
          action,
          ...data
        }, '*')
      }
    },
    
    async handleCallEnded() {
      await this.endCall(this.roomId)
      this.$router.push('/')
    },
    
    startDurationTimer() {
      this.callStartTime = Date.now()
      this.durationInterval = setInterval(() => {
        const elapsed = Date.now() - this.callStartTime
        const minutes = Math.floor(elapsed / 60000)
        const seconds = Math.floor((elapsed % 60000) / 1000)
        this.callDuration = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
      }, 1000)
    },
    
    cleanup() {
      if (this.durationInterval) {
        clearInterval(this.durationInterval)
      }
      window.removeEventListener('message', this.handleCallMessage)
    }
  }
}
</script>

<style scoped>
.call-view {
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.call-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: var(--color-main-background);
  border-bottom: 1px solid var(--color-border);
}

.call-controls {
  display: flex;
  gap: 8px;
}

.call-controls button.active {
  background: var(--color-error);
  color: white;
}

.call-container {
  flex: 1;
  position: relative;
}

#element-call-embed {
  width: 100%;
  height: 100%;
}

.call-info {
  padding: 8px 16px;
  background: var(--color-background-dark);
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  color: var(--color-text-maxcontrast);
}
</style>
