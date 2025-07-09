<template>
  <div class="admin-settings">
    <NcSettingsSection :title="t('nextcloud_chat', 'Matrix Server Configuration')">
      <MatrixServerSettings @settings-saved="onSettingsSaved" />
    </NcSettingsSection>

    <NcSettingsSection :title="t('nextcloud_chat', 'Element Configuration')">
      <ElementSettings @settings-saved="onSettingsSaved" />
    </NcSettingsSection>

    <NcSettingsSection :title="t('nextcloud_chat', 'Connection Status')">
      <ConnectionStatus />
    </NcSettingsSection>

    <NcSettingsSection :title="t('nextcloud_chat', 'Documentation')">
      <div class="documentation-links">
        <p>{{ t('nextcloud_chat', 'For setup instructions and documentation, please refer to:') }}</p>
        <ul>
          <li><a href="https://github.com/element-hq/element-web" target="_blank">Element Web Documentation</a></li>
          <li><a href="https://github.com/element-hq/element-call" target="_blank">Element Call Documentation</a></li>
          <li><a href="https://github.com/element-hq/ess-helm" target="_blank">Element Server Suite Setup</a></li>
          <li><a href="https://matrix.org/docs/" target="_blank">Matrix Protocol Documentation</a></li>
        </ul>
      </div>
    </NcSettingsSection>
  </div>
</template>

<script>
import { NcSettingsSection } from '@nextcloud/vue'
import { showSuccess } from '@nextcloud/dialogs'
import MatrixServerSettings from './MatrixServerSettings.vue'
import ElementSettings from './ElementSettings.vue'
import ConnectionStatus from './ConnectionStatus.vue'

export default {
  name: 'AdminSettings',
  components: {
    NcSettingsSection,
    MatrixServerSettings,
    ElementSettings,
    ConnectionStatus
  },
  methods: {
    onSettingsSaved() {
      showSuccess(this.t('nextcloud_chat', 'Settings saved successfully'))
    }
  }
}
</script>

<style scoped>
.admin-settings {
  max-width: 900px;
}

.documentation-links ul {
  list-style-type: disc;
  margin-left: 20px;
}

.documentation-links a {
  color: var(--color-primary);
  text-decoration: none;
}

.documentation-links a:hover {
  text-decoration: underline;
}
</style>
