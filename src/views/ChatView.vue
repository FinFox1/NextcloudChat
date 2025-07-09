<template>
  <div class="chat-container">
    <!-- Element Web Integration -->
    <div id="element-web-container" ref="elementContainer">
      <!-- Element Web will be embedded here -->
    </div>
    
    <!-- Element Call Integration (when in call) -->
    <ElementCallWidget
      v-if="inCall"
      :room-id="currentRoomId"
      :element-config="elementConfig"
      @call-ended="onCallEnded"
    />
  </div>
</template>

<script>
import { mapState } from 'vuex'
import ElementCallWidget from '../components/Call/ElementCallWidget.vue'

export default {
  name: 'ChatView',
  components: {
    ElementCallWidget
  },
  data() {
    return {
      inCall: false,
      currentRoomId: null
    }
  },
  computed: {
    ...mapState(['matrixClient', 'elementConfig', 'isAuthenticated'])
  },
  mounted() {
    this.initializeElementWeb()
  },
  methods: {
    async initializeElementWeb() {
      if (!this.isAuthenticated) {
        // Wait for authentication
        this.$store.watch(
          state => state.isAuthenticated,
          (authenticated) => {
            if (authenticated) {
              this.embedElementWeb()
            }
          }
        )
      } else {
        this.embedElementWeb()
      }
    },
    
    embedElementWeb() {
      // Create Element Web configuration
      const config = {
        ...this.elementConfig,
        default_server_config: {
          ...this.elementConfig.default_server_config,
          'm.homeserver': {
            ...this.elementConfig.default_server_config['m.homeserver']
          }
        }
      }
      
      // Embed Element Web
      const iframe = document.createElement('iframe')
      iframe.src = this.generateElementWebUrl(config)
      iframe.style.width = '100%'
      iframe.style.height = '100%'
      iframe.style.border = 'none'
      
      this.$refs.elementContainer.appendChild(iframe)
      
      // Listen for Element Call events
      window.addEventListener('message', this.handleElementMessage)
    },
    
    generateElementWebUrl(config) {
      // In production, this would point to your Element Web deployment
      // For development, you might use the public instance with config
      const baseUrl = 'https://app.element.io'
      const configParam = encodeURIComponent(JSON.stringify(config))
      return `${baseUrl}?config=${configParam}`
    },
    
    handleElementMessage(event) {
      if (event.data.type === 'element_call_started') {
        this.inCall = true
        this.currentRoomId = event.data.roomId
      }
    },
    
    onCallEnded() {
      this.inCall = false
      this.currentRoomId = null
    }
  },
  
  beforeUnmount() {
    window.removeEventListener('message', this.handleElementMessage)
  }
}
</script>

<style scoped>
.chat-container {
  height: 100%;
  position: relative;
}

#element-web-container {
  height: 100%;
  width: 100%;
}
</style>
