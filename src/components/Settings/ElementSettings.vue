<template>
  <div class="element-settings">
    <h3>{{ t('nextcloud_chat', 'Element Configuration') }}</h3>

    <form @submit.prevent="saveSettings">
      <div class="form-group">
        <NcTextField
          :value="settings.elementWebUrl"
          :label="t('nextcloud_chat', 'Element Web URL')"
          :placeholder="'https://app.element.io'"
          @update:value="settings.elementWebUrl = $event"
        />
        <p class="field-help">
          {{ t('nextcloud_chat', 'URL of your Element Web instance') }}
        </p>
      </div>

      <div class="form-group">
        <NcTextField
          :value="settings.elementCallUrl"
          :label="t('nextcloud_chat', 'Element Call URL')"
          :placeholder="'https://call.element.io'"
          @update:value="settings.elementCallUrl = $event"
        />
        <p class="field-help">
          {{ t('nextcloud_chat', 'URL of your Element Call instance for video calls') }}
        </p>
      </div>

      <div class="form-section">
        <h4>{{ t('nextcloud_chat', 'Features') }}</h4>
        
        <NcCheckboxRadioSwitch
          :checked="settings.enableE2EE"
          @update:checked="settings.enableE2EE = $event"
        >
          {{ t('nextcloud_chat', 'Enable End-to-End Encryption') }}
        </NcCheckboxRadioSwitch>

        <NcCheckboxRadioSwitch
          :checked="settings.enableFederation"
          @update:checked="settings.enableFederation = $event"
        >
          {{ t('nextcloud_chat', 'Enable Federation') }}
        </NcCheckboxRadioSwitch>

        <NcCheckboxRadioSwitch
          :checked="settings.enablePresence"
          @update:checked="settings.enablePresence = $event"
        >
          {{ t('nextcloud_chat', 'Enable Presence Status') }}
        </NcCheckboxRadioSwitch>

        <NcCheckboxRadioSwitch
          :checked="settings.enableTypingIndicators"
          @update:checked="settings.enableTypingIndicators = $event"
        >
          {{ t('nextcloud_chat', 'Enable Typing Indicators') }}
        </NcCheckboxRadioSwitch>
      </div>

      <div class="form-section">
        <h4>{{ t('nextcloud_chat', 'Element Call Settings') }}</h4>
        
        <div class="form-group">
          <NcTextField
            :value="settings.callParticipantLimit"
            :label="t('nextcloud_chat', 'Participant Limit')"
            type="number"
            min="2"
            max="50"
            @update:value="settings.callParticipantLimit = parseInt($event)"
          />
          <p class="field-help">
            {{ t('nextcloud_chat', 'Maximum number of participants in a call') }}
          </p>
        </div>

        <NcCheckboxRadioSwitch
          :checked="settings.useElementCallExclusively"
          @update:checked="settings.useElementCallExclusively = $event"
        >
          {{ t('nextcloud_chat', 'Use Element Call exclusively') }}
        </NcCheckboxRadioSwitch>
      </div>

      <div class="form-section">
        <h4>{{ t('nextcloud_chat', 'UI Customization') }}</h4>
        
        <div class="form-group">
          <NcTextField
            :value="settings.brandName"
            :label="t('nextcloud_chat', 'Brand Name')"
            :placeholder="'Nextcloud Chat'"
            @update:value="settings.brandName = $event"
          />
        </div>

        <div class="form-group">
          <NcSelect
            v-model="settings.defaultTheme"
            :options="themeOptions"
            :label="t('nextcloud_chat', 'Default Theme')"
          />
        </div>

        <NcCheckboxRadioSwitch
          :checked="settings.showLabsSettings"
          @update:checked="settings.showLabsSettings = $event"
        >
          {{ t('nextcloud_chat', 'Show Labs Settings') }}
        </NcCheckboxRadioSwitch>
      </div>

      <div class="form-actions">
        <NcButton @click="resetToDefaults" type="secondary">
          {{ t('nextcloud_chat', 'Reset to Defaults') }}
        </NcButton>
        <NcButton type="primary" native-type="submit">
          {{ t('nextcloud_chat', 'Save Settings') }}
        </NcButton>
      </div>
    </form>
  </div>
</template>

<script>
import {
  NcTextField,
  NcButton,
  NcCheckboxRadioSwitch,
  NcSelect
} from '@nextcloud/vue'
import { generateUrl } from '@nextcloud/router'
import { showSuccess, showError } from '@nextcloud/dialogs'

export default {
  name: 'ElementSettings',
  components: {
    NcTextField,
    NcButton,
    NcCheckboxRadioSwitch,
    NcSelect
  },
  data() {
    return {
      settings: {
        elementWebUrl: '',
        elementCallUrl: '',
        enableE2EE: true,
        enableFederation: true,
        enablePresence: true,
        enableTypingIndicators: true,
        callParticipantLimit: 8,
        useElementCallExclusively: false,
        brandName: 'Nextcloud Chat',
        defaultTheme: 'auto',
        showLabsSettings: false
      },
      themeOptions: [
        { id: 'auto', label: this.t('nextcloud_chat', 'Auto') },
        { id: 'light', label: this.t('nextcloud_chat', 'Light') },
        { id: 'dark', label: this.t('nextcloud_chat', 'Dark') }
      ]
    }
  },
  mounted() {
    this.loadSettings()
  },
  methods: {
    async loadSettings() {
      try {
        const response = await fetch(generateUrl('/apps/nextcloud_chat/api/element/config'))
        const config = await response.json()
        
        this.settings = {
          elementWebUrl: config.element_web_url || '',
          elementCallUrl: config.element_call?.url || '',
          enableE2EE: config.features?.e2e_encryption !== false,
          enableFederation: config.default_federate !== false,
          enablePresence: config.enable_presence_by_hs_url ? Object.values(config.enable_presence_by_hs_url)[0] : true,
          enableTypingIndicators: config.features?.typing_indicators !== false,
          callParticipantLimit: config.element_call?.participant_limit || 8,
          useElementCallExclusively: config.element_call?.use_exclusively || false,
          brandName: config.brand || 'Nextcloud Chat',
          defaultTheme: config.default_theme || 'auto',
          showLabsSettings: config.show_labs_settings || false
        }
      } catch (error) {
        console.error('Failed to load Element settings:', error)
      }
    },

    async saveSettings() {
      try {
        const config = {
          element_web_url: this.settings.elementWebUrl,
          element_call: {
            url: this.settings.elementCallUrl,
            participant_limit: this.settings.callParticipantLimit,
            use_exclusively: this.settings.useElementCallExclusively
          },
          features: {
            e2e_encryption: this.settings.enableE2EE ? 'enable' : 'disable',
            typing_indicators: this.settings.enableTypingIndicators ? 'enable' : 'disable'
          },
          default_federate: this.settings.enableFederation,
          enable_presence_by_hs_url: this.settings.enablePresence,
          brand: this.settings.brandName,
          default_theme: this.settings.defaultTheme,
          show_labs_settings: this.settings.showLabsSettings
        }

        const response = await fetch(generateUrl('/apps/nextcloud_chat/api/element/config'), {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ config })
        })

        if (response.ok) {
          showSuccess(this.t('nextcloud_chat', 'Element settings saved'))
          this.$emit('settings-saved')
        } else {
          throw new Error('Failed to save settings')
        }
      } catch (error) {
        console.error('Failed to save Element settings:', error)
        showError(this.t('nextcloud_chat', 'Failed to save Element settings'))
      }
    },

    resetToDefaults() {
      this.settings = {
        elementWebUrl: 'https://app.element.io',
        elementCallUrl: 'https://call.element.io',
        enableE2EE: true,
        enableFederation: true,
        enablePresence: true,
        enableTypingIndicators: true,
        callParticipantLimit: 8,
        useElementCallExclusively: false,
        brandName: 'Nextcloud Chat',
        defaultTheme: 'auto',
        showLabsSettings: false
      }
    }
  }
}
</script>

<style scoped>
.element-settings {
  max-width: 600px;
}

.form-section {
  margin: 32px 0;
  padding: 16px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
}

.form-section h4 {
  margin-top: 0;
  margin-bottom: 16px;
  font-size: 16px;
  font-weight: 600;
}

.form-group {
  margin-bottom: 16px;
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
</style>
