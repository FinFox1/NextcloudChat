<template>
  <div class="matrix-server-settings">
    <h3>{{ t('nextcloud_chat', 'Matrix Server Configuration') }}</h3>
    
    <form @submit.prevent="saveSettings">
      <div class="form-group">
        <NcTextField
          :value="settings.serverUrl"
          :label="t('nextcloud_chat', 'Matrix Server URL')"
          :placeholder="'https://matrix.example.com'"
          @update:value="settings.serverUrl = $event"
          required
        />
        <p class="field-help">
          {{ t('nextcloud_chat', 'The URL of your Matrix homeserver') }}
        </p>
      </div>

      <div class="form-group">
        <NcTextField
          :value="settings.serverName"
          :label="t('nextcloud_chat', 'Matrix Server Name')"
          :placeholder="'matrix.example.com'"
          @update:value="settings.serverName = $event"
          required
        />
        <p class="field-help">
          {{ t('nextcloud_chat', 'The server name (domain) for Matrix user IDs') }}
        </p>
      </div>

      <div class="form-group">
        <NcTextField
          :value="settings.applicationToken"
          :label="t('nextcloud_chat', 'Application Service Token')"
          :placeholder="t('nextcloud_chat', 'Enter application service token')"
          @update:value="settings.applicationToken = $event"
          type="password"
        />
        <p class="field-help">
          {{ t('nextcloud_chat', 'Token for authenticating with the Matrix server') }}
        </p>
      </div>

      <div class="form-group">
        <NcTextField
          :value="settings.identityServerUrl"
          :label="t('nextcloud_chat', 'Identity Server URL')"
          :placeholder="'https://vector.im'"
          @update:value="settings.identityServerUrl = $event"
        />
        <p class="field-help">
          {{ t('nextcloud_chat', 'Optional identity server for user discovery') }}
        </p>
      </div>

      <div class="form-actions">
        <NcButton @click="testConnection" type="secondary" :disabled="!settings.serverUrl">
          {{ t('nextcloud_chat', 'Test Connection') }}
        </NcButton>
        <NcButton type="primary" native-type="submit">
          {{ t('nextcloud_chat', 'Save Settings') }}
        </NcButton>
      </div>
    </form>

    <div v-if="connectionStatus" :class="['connection-result', connectionStatus.type]">
      <CheckCircleIcon v-if="connectionStatus.type === 'success'" />
      <AlertCircleIcon v-else />
      <span>{{ connectionStatus.message }}</span>
    </div>
  </div>
</template>

<script>
import {
  NcTextField,
  NcButton
} from '@nextcloud/vue'
import { generateUrl } from '@nextcloud/router'
import { showSuccess, showError } from '@nextcloud/dialogs'

export default {
  name: 'MatrixServerSettings',
  components: {
    NcTextField,
    NcButton
  },
  data() {
    return {
      settings: {
        serverUrl: '',
        serverName: '',
        applicationToken: '',
        identityServerUrl: ''
      },
      connectionStatus: null
    }
  },
  mounted() {
    this.loadSettings()
  },
  methods: {
    async loadSettings() {
      try {
        const response = await fetch(generateUrl('/apps/nextcloud_chat/api/matrix/config'))
        const config = await response.json()
        
        this.settings = {
          serverUrl: config.server_url || '',
          serverName: config.server_name || '',
          applicationToken: config.application_token || '',
          identityServerUrl: config.identity_server_url || ''
        }
      } catch (error) {
        console.error('Failed to load Matrix settings:', error)
      }
    },

    async saveSettings() {
      try {
        const response = await fetch(generateUrl('/apps/nextcloud_chat/api/matrix/config'), {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.settings)
        })

        if (response.ok) {
          showSuccess(this.t('nextcloud_chat', 'Matrix server settings saved'))
          this.$emit('settings-saved')
        } else {
          throw new Error('Failed to save settings')
        }
      } catch (error) {
        console.error('Failed to save Matrix settings:', error)
        showError(this.t('nextcloud_chat', 'Failed to save Matrix server settings'))
      }
    },

    async testConnection() {
      this.connectionStatus = null
      
      try {
        const response = await fetch(`${this.settings.serverUrl}/_matrix/client/versions`)
        
        if (response.ok) {
          this.connectionStatus = {
            type: 'success',
            message: this.t('nextcloud_chat', 'Connection successful')
          }
        } else {
          throw new Error('Connection failed')
        }
      } catch (error) {
        this.connectionStatus = {
          type: 'error',
          message: this.t('nextcloud_chat', 'Connection failed: {error}', { error: error.message })
        }
      }
    }
  }
}
</script>

<style scoped>
.matrix-server-settings {
  max-width: 600px;
}

.form-group {
  margin-bottom: 24px;
}

.field-help {
  margin-top: 4px;
  font-size: 14px;
  color: var(--color-text-maxcontrast);
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 24px;
}

.connection-result {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px;
  border-radius: 8px;
  margin-top: 16px;
}

.connection-result.success {
  background: var(--color-success-light);
  color: var(--color-success-dark);
}

.connection-result.error {
  background: var(--color-error-light);
  color: var(--color-error-dark);
}
</style>
