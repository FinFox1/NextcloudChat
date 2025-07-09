<template>
  <div class="settings-view">
    <div class="settings-header">
      <h2>{{ t('nextcloud_chat', 'Chat Settings') }}</h2>
    </div>
    
    <div class="settings-content">
      <NcSettingsSection :title="t('nextcloud_chat', 'Matrix Server Configuration')">
        <NcTextField
          :value="matrixServerUrl"
          :label="t('nextcloud_chat', 'Matrix Server URL')"
          :placeholder="'https://matrix.example.com'"
          @update:value="matrixServerUrl = $event"
        />
        
        <NcTextField
          :value="matrixServerName"
          :label="t('nextcloud_chat', 'Matrix Server Name')"
          :placeholder="'matrix.example.com'"
          @update:value="matrixServerName = $event"
        />
        
        <NcTextField
          :value="elementCallUrl"
          :label="t('nextcloud_chat', 'Element Call URL')"
          :placeholder="'https://call.element.io'"
          @update:value="elementCallUrl = $event"
        />
        
        <NcButton @click="saveMatrixConfig" type="primary">
          {{ t('nextcloud_chat', 'Save Matrix Configuration') }}
        </NcButton>
      </NcSettingsSection>
      
      <NcSettingsSection :title="t('nextcloud_chat', 'Element Web Configuration')">
        <NcTextField
          :value="elementWebUrl"
          :label="t('nextcloud_chat', 'Element Web URL')"
          :placeholder="'https://app.element.io'"
          @update:value="elementWebUrl = $event"
        />
        
        <NcCheckboxRadioSwitch
          :checked="enableE2EE"
          @update:checked="enableE2EE = $event"
        >
          {{ t('nextcloud_chat', 'Enable End-to-End Encryption') }}
        </NcCheckboxRadioSwitch>
        
        <NcCheckboxRadioSwitch
          :checked="enableFederation"
          @update:checked="enableFederation = $event"
        >
          {{ t('nextcloud_chat', 'Enable Federation') }}
        </NcCheckboxRadioSwitch>
        
        <NcButton @click="saveElementConfig" type="primary">
          {{ t('nextcloud_chat', 'Save Element Configuration') }}
        </NcButton>
      </NcSettingsSection>
      
      <NcSettingsSection :title="t('nextcloud_chat', 'Connection Status')">
        <div class="connection-status">
          <div class="status-item">
            <span class="status-label">{{ t('nextcloud_chat', 'Matrix Server') }}:</span>
            <span :class="['status-indicator', matrixStatus]">
              {{ matrixStatus === 'connected' ? t('nextcloud_chat', 'Connected') : t('nextcloud_chat', 'Disconnected') }}
            </span>
          </div>
          
          <div class="status-item">
            <span class="status-label">{{ t('nextcloud_chat', 'Element Call') }}:</span>
            <span :class="['status-indicator', callStatus]">
              {{ callStatus === 'available' ? t('nextcloud_chat', 'Available') : t('nextcloud_chat', 'Unavailable') }}
            </span>
          </div>
        </div>
        
        <NcButton @click="testConnection">
          {{ t('nextcloud_chat', 'Test Connection') }}
        </NcButton>
      </NcSettingsSection>
      
      <NcSettingsSection :title="t('nextcloud_chat', 'User Preferences')">
        <NcCheckboxRadioSwitch
          :checked="showNotifications"
          @update:checked="showNotifications = $event"
        >
          {{ t('nextcloud_chat', 'Show desktop notifications') }}
        </NcCheckboxRadioSwitch>
        
        <NcCheckboxRadioSwitch
          :checked="soundEnabled"
          @update:checked="soundEnabled = $event"
        >
          {{ t('nextcloud_chat', 'Enable notification sounds') }}
        </NcCheckboxRadioSwitch>
        
        <NcSelect
          v-model="selectedTheme"
          :options="themeOptions"
          :label="t('nextcloud_chat', 'Theme')"
        />
        
        <NcButton @click="saveUserPreferences" type="primary">
          {{ t('nextcloud_chat', 'Save Preferences') }}
        </NcButton>
      </NcSettingsSection>
    </div>
  </div>
</template>

<script>
import {
  NcSettingsSection,
  NcTextField,
  NcButton,
  NcCheckboxRadioSwitch,
  NcSelect
} from '@nextcloud/vue'
import { generateUrl } from '@nextcloud/router'
import { showSuccess, showError } from '@nextcloud/dialogs'

export default {
  name: 'SettingsView',
  components: {
    NcSettingsSection,
    NcTextField,
    NcButton,
    NcCheckboxRadioSwitch,
    NcSelect
  },
  data() {
    return {
      matrixServerUrl: '',
      matrixServerName: '',
      elementCallUrl: '',
      elementWebUrl: '',
      enableE2EE: true,
      enableFederation: true,
      showNotifications: true,
      soundEnabled: true,
      selectedTheme: 'auto',
      matrixStatus: 'disconnected',
      callStatus: 'unavailable',
      themeOptions: [
        { id: 'auto', label: this.t('nextcloud_chat', 'Auto') },
        { id: 'light', label: this.t('nextcloud_chat', 'Light') },
        { id: 'dark', label: this.t('nextcloud_chat', 'Dark') }
      ]
    }
  },
  mounted() {
    this.loadSettings()
    this.checkConnectionStatus()
  },
  methods: {
    async loadSettings() {
      try {
        const response = await fetch(generateUrl('/apps/nextcloud_chat/api/config'))
        const config = await response.json()
        
        this.matrixServerUrl = config.matrix_server || ''
        this.elementCallUrl = config.element_config?.element_call?.url || ''
        this.elementWebUrl = config.element_config?.element_web_url || ''
        this.enableE2EE = config.element_config?.features?.e2e_encryption !== false
        this.enableFederation = config.element_config?.default_federate !== false
        
        // Load user preferences from localStorage
        const prefs = JSON.parse(localStorage.getItem('nextcloud_chat_preferences') || '{}')
        this.showNotifications = prefs.showNotifications !== false
        this.soundEnabled = prefs.soundEnabled !== false
        this.selectedTheme = prefs.theme || 'auto'
        
      } catch (error) {
        console.error('Failed to load settings:', error)
        showError(this.t('nextcloud_chat', 'Failed to load settings'))
      }
    },
    
    async saveMatrixConfig() {
      try {
        const response = await fetch(generateUrl('/apps/nextcloud_chat/api/element/config'), {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            config: {
              matrix_server_url: this.matrixServerUrl,
              matrix_server_name: this.matrixServerName,
              element_call_url: this.elementCallUrl
            }
          })
        })
        
        if (response.ok) {
          showSuccess(this.t('nextcloud_chat', 'Matrix configuration saved'))
          this.checkConnectionStatus()
        } else {
          throw new Error('Failed to save configuration')
        }
      } catch (error) {
        console.error('Failed to save Matrix config:', error)
        showError(this.t('nextcloud_chat', 'Failed to save Matrix configuration'))
      }
    },
    
    async saveElementConfig() {
      try {
        const response = await fetch(generateUrl('/apps/nextcloud_chat/api/element/config'), {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            config: {
              element_web_url: this.elementWebUrl,
              enable_e2ee: this.enableE2EE,
              enable_federation: this.enableFederation
            }
          })
        })
        
        if (response.ok) {
          showSuccess(this.t('nextcloud_chat', 'Element configuration saved'))
        } else {
          throw new Error('Failed to save configuration')
        }
      } catch (error) {
        console.error('Failed to save Element config:', error)
        showError(this.t('nextcloud_chat', 'Failed to save Element configuration'))
      }
    },
    
    saveUserPreferences() {
      const preferences = {
        showNotifications: this.showNotifications,
        soundEnabled: this.soundEnabled,
        theme: this.selectedTheme
      }
      
      localStorage.setItem('nextcloud_chat_preferences', JSON.stringify(preferences))
      showSuccess(this.t('nextcloud_chat', 'Preferences saved'))
    },
    
    async checkConnectionStatus() {
      try {
        // Check Matrix server status
        if (this.matrixServerUrl) {
          const matrixResponse = await fetch(`${this.matrixServerUrl}/_matrix/client/versions`)
          this.matrixStatus = matrixResponse.ok ? 'connected' : 'disconnected'
        }
        
        // Check Element Call status
        if (this.elementCallUrl) {
          const callResponse = await fetch(`${this.elementCallUrl}/health`)
          this.callStatus = callResponse.ok ? 'available' : 'unavailable'
        }
      } catch (error) {
        console.error('Connection check failed:', error)
        this.matrixStatus = 'disconnected'
        this.callStatus = 'unavailable'
      }
    },
    
    async testConnection() {
      await this.checkConnectionStatus()
      
      if (this.matrixStatus === 'connected') {
        showSuccess(this.t('nextcloud_chat', 'Matrix server connection successful'))
      } else {
        showError(this.t('nextcloud_chat', 'Failed to connect to Matrix server'))
      }
    }
  }
}
</script>

<style scoped>
.settings-view {
  padding: 20px;
  max-width: 800px;
}

.settings-header h2 {
  margin-bottom: 20px;
}

.settings-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.connection-status {
  margin: 16px 0;
}

.status-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 8px 0;
}

.status-label {
  font-weight: 500;
}

.status-indicator {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
}

.status-indicator.connected,
.status-indicator.available {
  background: var(--color-success);
  color: white;
}

.status-indicator.disconnected,
.status-indicator.unavailable {
  background: var(--color-error);
  color: white;
}
</style>
