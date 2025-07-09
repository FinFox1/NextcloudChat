<template>
  <div id="app">
    <NcAppNavigation>
      <template #list>
        <NcAppNavigationItem
          :title="t('nextcloud_chat', 'Rooms')"
          icon="icon-comment"
          :to="{ path: '/' }"
        />
        <NcAppNavigationItem
          :title="t('nextcloud_chat', 'Settings')"
          icon="icon-settings"
          :to="{ path: '/settings' }"
        />
      </template>
    </NcAppNavigation>

    <NcAppContent>
      <router-view />
    </NcAppContent>
  </div>
</template>

<script>
import {
  NcAppNavigation,
  NcAppNavigationItem,
  NcAppContent
} from '@nextcloud/vue'

export default {
  name: 'NextcloudChatApp',
  components: {
    NcAppNavigation,
    NcAppNavigationItem,
    NcAppContent
  }
}
</script>

<style scoped>
#app {
  height: 100vh;
  display: flex;
}
</style>
