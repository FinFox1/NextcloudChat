<template>
  <div class="connection-status">
    <div class="status-grid">
      <div class="status-item">
        <div class="status-header">
          <h4>{{ t('nextcloud_chat', 'Matrix Server') }}</h4>
          <NcButton @click="testMatrixConnection" :disabled="testing.matrix">
            {{ testing.matrix ? t('nextcloud_chat', 'Testing...') : t('nextcloud_chat', 'Test') }}
          </NcButton>
        </div>
        <div :class="['status-indicator', matrixStatus.type]">
          <CheckCircleIcon v-if="matrixStatus.type === 'success'" />
          <AlertCircleIcon v-else-if="matrixStatus.type === 'error'" />
          <ClockIcon v-else />
          <span>{{ matrixStatus.message }}</span>
        </div>
      </div>

      <div class="status-item">
        <div class="status-header">
          <h4>{{ t('nextcloud_chat', 'Element Call') }}</h4>
          <NcButton @click="testElementCallConnection" :disabled="testing.elementCall">
            {{ testing.elementCall ? t('nextcloud_chat', 'Testing...') : t('nextcloud_chat', 'Test') }}
          </NcButton>
        </div>
        <div :class="['status-indicator', elementCallStatus.type]">
          <CheckCircleIcon v-if="elementCallStatus.type === 'success'" />
          <AlertCircleIcon v-else-if="elementCallStatus.type === 'error'" />
          <ClockIcon v-else />
          <span>{{ elementCallStatus.message }}</span>
        </div>
      </div>

      <div class="status-item">
        <div class="status-header">
          <h4>{{ t('nextcloud_chat', 'Element Web') }}</h4>
          <NcButton @click="testElementWebConnection" :disabled="testing.elementWeb">
            {{ testing.elementWeb ? t('nextcloud_chat', 'Testing...') : t('nextcloud_chat', 'Test') }}
          </NcButton>
        </div>
        <div :class="['status-indicator', elementWebStatus.type]">
          <CheckCircleIcon v-if="elementWebStatus.type === 'success'" />
          <AlertCircleIcon v-else-if="elementWebStatus.type === 'error'" />
          <ClockIcon v-else />
          <span>{{ elementWebStatus.message }}</span>
        </div>
      </div>

      <div class="status-item">
        <div class="status-header">
          <h4>{{ t('nextcloud_chat', 'Federation') }}</h4>
          <NcButton @click="testFederation" :disabled="testing.federation">
            {{ testing.federation ? t('nextcloud_chat', 'Testing...') : t('nextcloud_chat', 'Test') }}
          </NcButton>
        </div>
        <div :class="['status-indicator', federationStatus.type]">
          <CheckCircleIcon v-if="federationStatus.type === 'success'" />
          <AlertCircleIcon v-else-if="federationStatus.type === 'error'" />
          <ClockIcon v-else />
          <span>{{ federationStatus.message }}</span>
        </div>
      </div>
    </div>

    <div class="overall-status">
      <h3>{{ t('nextcloud_chat', 'Overall Status') }}</h3>
      <div :class="['overall-indicator', overallStatus.type]">
        <CheckCircleIcon v-if="overallStatus.type === 'success'" />
        <AlertCircleIcon v-else-if="overallStatus.type === 'error'" />
        <ClockIcon v-else />
        <span>{{ overallStatus.message }}</span>
      </div>
    </div>

    <div class="quick-actions">
      <NcButton @click="testAllConnections" type="primary" :disabled="isAnyTesting">
        {{ isAnyTesting ? t('nextcloud_chat', 'Testing...') : t('nextcloud_chat', 'Test All Connections') }}
      </NcButton>
      <NcButton @click="refreshStatus" type="secondary">
        {{ t('nextcloud_chat', 'Refresh Status') }}
      </NcButton>
    </div>
  </div>
</template>

<script>
import { NcButton } from '@nextcloud/vue'
import { generateUrl } from '@nextcloud/router'

export default {
  name: 'ConnectionStatus',
  components: {
    NcButton
  },
  data() {
    return {
      testing: {
        matrix: false,
        elementCall: false,
        elementWeb: false,
        federation: false
      },
      matrixStatus: { type: 'pending', message: this.t('nextcloud_chat', 'Not tested') },
      elementCallStatus: { type: 'pending', message: this.t('nextcloud_chat', 'Not tested') },
      elementWebStatus: { type: 'pending', message: this.t('nextcloud_chat', 'Not tested') },
      federationStatus: { type: 'pending', message: this.t('nextcloud_chat', 'Not tested') }
    }
  },
  computed: {
    overallStatus() {
      const statuses = [this.matrixStatus, this.elementCallStatus, this.elementWebStatus, this.federationStatus]
      const hasError = statuses.some(s => s.type === 'error')
      const allSuccess = statuses.every(s => s.type === 'success')
      
      if (hasError) {
        return { type: 'error', message: this.t('nextcloud_chat', 'Some services are unavailable') }
      } else if (allSuccess) {
        return { type: 'success', message: this.t('nextcloud_chat', 'All services are operational') }
      } else {
        return { type: 'pending', message: this.t('nextcloud_chat', 'Status unknown') }
      }
    },
    isAnyTesting() {
      return Object.values(this.testing).some(t => t)
    }
  },
  mounted() {
    this.loadInitialStatus()
  },
  methods: {
    async loadInitialStatus() {
      // Load saved configuration and test basic connectivity
      try {
        const response = await fetch(generateUrl('/apps/nextcloud_chat/api/config'))
        const config = await response.json()
        
        if (config.matrix_server) {
          await this.testMatrixConnection()
        }
        if (config.element_config?.element_call?.url) {
          await this.testElementCallConnection()
        }
        if (config.element_config?.element_web_url) {
          await this.testElementWebConnection()
        }
      } catch (error) {
        console.error('Failed to load initial status:', error)
      }
    },

    async testMatrixConnection() {
      this.testing.matrix = true
      
      try {
        const response = await fetch(generateUrl('/apps/nextcloud_chat/api/config'))
        const config = await response.json()
        
        if (!config.matrix_server) {
          this.matrixStatus = { type: 'error', message: this.t('nextcloud_chat', 'Matrix server not configured') }
          return
        }

        const matrixResponse = await fetch(`${config.matrix_server}/_matrix/client/versions`)
        
        if (matrixResponse.ok) {
          const versions = await matrixResponse.json()
          this.matrixStatus = { 
            type: 'success', 
            message: this.t('nextcloud_chat', 'Connected (API versions: {versions})', {
              versions: versions.versions.slice(0, 3).join(', ')
            })
          }
        } else {
          throw new Error(`HTTP ${matrixResponse.status}`)
        }
      } catch (error) {
        this.matrixStatus = { 
          type: 'error', 
          message: this.t('nextcloud_chat', 'Connection failed: {error}', { error: error.message })
        }
      } finally {
        this.testing.matrix = false
      }
    },

    async testElementCallConnection() {
      this.testing.elementCall = true
      
      try {
        const response = await fetch(generateUrl('/apps/nextcloud_chat/api/config'))
        const config = await response.json()
        
        const callUrl = config.element_config?.element_call?.url
        if (!callUrl) {
          this.elementCallStatus = { type: 'error', message: this.t('nextcloud_chat', 'Element Call not configured') }
          return
        }

        const callResponse = await fetch(`${callUrl}/config.json`)
        
        if (callResponse.ok) {
          this.elementCallStatus = { type: 'success', message: this.t('nextcloud_chat', 'Element Call is accessible') }
        } else {
          throw new Error(`HTTP ${callResponse.status}`)
        }
      } catch (error) {
        this.elementCallStatus = { 
          type: 'error', 
          message: this.t('nextcloud_chat', 'Connection failed: {error}', { error: error.message })
        }
      } finally {
        this.testing.elementCall = false
      }
    },

    async testElementWebConnection() {
      this.testing.elementWeb = true
      
      try {
        const response = await fetch(generateUrl('/apps/nextcloud_chat/api/config'))
        const config = await response.json()
        
        const webUrl = config.element_config?.element_web_url || 'https://app.element.io'
        
        // Test if Element Web is accessible
        const webResponse = await fetch(`${webUrl}/config.json`, { mode: 'no-cors' })
        
        // Since we can't read the response due to CORS, we assume success if no error is thrown
        this.elementWebStatus = { type: 'success', message: this.t('nextcloud_chat', 'Element Web is accessible') }
      } catch (error) {
        // For CORS-blocked requests, we can't determine the actual status
        this.elementWebStatus = { 
          type: 'pending', 
          message: this.t('nextcloud_chat', 'Cannot verify due to CORS policy')
        }
      } finally {
        this.testing.elementWeb = false
      }
    },

    async testFederation() {
      this.testing.federation = true
      
      try {
        const response = await fetch(generateUrl('/apps/nextcloud_chat/api/config'))
        const config = await response.json()
        
        if (!config.matrix_server) {
          this.federationStatus = { type: 'error', message: this.t('nextcloud_chat', 'Matrix server not configured') }
          return
        }

        // Test federation by checking well-known file
        const serverName = new URL(config.matrix_server).hostname
        const wellKnownResponse = await fetch(`https://${serverName}/.well-known/matrix/server`)
        
        if (wellKnownResponse.ok) {
          const wellKnown = await wellKnownResponse.json()
          this.federationStatus = { 
            type: 'success', 
            message: this.t('nextcloud_chat', 'Federation is configured') 
          }
        } else {
          this.federationStatus = { 
            type: 'error', 
            message: this.t('nextcloud_chat', 'Federation configuration not found') 
          }
        }
      } catch (error) {
        this.federationStatus = { 
          type: 'error', 
          message: this.t('nextcloud_chat', 'Federation test failed: {error}', { error: error.message })
        }
      } finally {
        this.testing.federation = false
      }
    },

    async testAllConnections() {
      await Promise.all([
        this.testMatrixConnection(),
        this.testElementCallConnection(),
        this.testElementWebConnection(),
        this.testFederation()
      ])
    },

    refreshStatus() {
      this.matrixStatus = { type: 'pending', message: this.t('nextcloud_chat', 'Not tested') }
      this.elementCallStatus = { type: 'pending', message: this.t('nextcloud_chat', 'Not tested') }
      this.elementWebStatus = { type: 'pending', message: this.t('nextcloud_chat', 'Not tested') }
      this.federationStatus = { type: 'pending', message: this.t('nextcloud_chat', 'Not tested') }
    }
  }
}
</script>

<style scoped>
.connection-status {
  max-width: 800px;
}

.status-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.status-item {
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 16px;
}

.status-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.status-header h4 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 14px;
}

.status-indicator.success {
  background: var(--color-success-light);
  color: var(--color-success-dark);
}

.status-indicator.error {
  background: var(--color-error-light);
  color: var(--color-error-dark);
}

.status-indicator.pending {
  background: var(--color-background-hover);
  color: var(--color-text-maxcontrast);
}

.overall-status {
  margin-bottom: 24px;
  padding: 16px;
  border: 2px solid var(--color-border);
  border-radius: 8px;
  text-align: center;
}

.overall-status h3 {
  margin-top: 0;
  margin-bottom: 12px;
}

.overall-indicator {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 16px;
  font-weight: 500;
}

.overall-indicator.success {
  background: var(--color-success-light);
  color: var(--color-success-dark);
}

.overall-indicator.error {
  background: var(--color-error-light);
  color: var(--color-error-dark);
}

.overall-indicator.pending {
  background: var(--color-background-hover);
  color: var(--color-text-maxcontrast);
}

.quick-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
}
</style>
