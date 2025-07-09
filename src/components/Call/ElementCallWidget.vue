<template>
  <div class="element-call-widget" v-if="visible">
    <div class="call-header">
      <h3>{{ t('nextcloud_chat', 'Video Call') }}</h3>
      <NcButton @click="endCall" type="error">
        {{ t('nextcloud_chat', 'End Call') }}
      </NcButton>
    </div>
    <div id="element-call-container" ref="callContainer">
      <!-- Element Call will be embedded here -->
    </div>
  </div>
</template>

<script>
import { NcButton } from '@nextcloud/vue'

export default {
  name: 'ElementCallWidget',
  components: {
    NcButton
  },
  props: {
    roomId: {
      type: String,
      required: true
    },
    elementConfig: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      visible: false,
      callWidget: null
    }
  },
  mounted() {
    this.initializeElementCall()
  },
  methods: {
    async initializeElementCall() {
      try {
        // Element Call can be embedded as a widget
        const callUrl = this.elementConfig.element_call.url
        const roomCallUrl = `${callUrl}/?roomId=${this.roomId}`
        
        const iframe = document.createElement('iframe')
        iframe.src = roomCallUrl
        iframe.style.width = '100%'
        iframe.style.height = '500px'
        iframe.style.border = 'none'
        iframe.allow = 'camera; microphone; display-capture'
        
        this.$refs.callContainer.appendChild(iframe)
        this.callWidget = iframe
        this.visible = true
        
      } catch (error) {
        console.error('Failed to initialize Element Call:', error)
      }
    },
    
    endCall() {
      this.visible = false
      if (this.callWidget) {
        this.callWidget.remove()
        this.callWidget = null
      }
      this.$emit('call-ended')
    }
  }
}
</script>

<style scoped>
.element-call-widget {
  position: fixed;
  top: 50px;
  right: 20px;
  width: 400px;
  background: var(--color-main-background);
  border: 1px solid var(--color-border);
  border-radius: var(--border-radius-large);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  z-index: 1000;
}

.call-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid var(--color-border);
}

#element-call-container {
  height: 500px;
}
</style>
