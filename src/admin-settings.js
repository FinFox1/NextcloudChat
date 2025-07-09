import { createApp } from 'vue'
import { NextcloudVuePlugin } from '@nextcloud/vue'
import AdminSettings from './components/Settings/AdminSettings.vue'

const app = createApp(AdminSettings)
app.use(NextcloudVuePlugin)
app.mount('#nextcloud_chat_admin_settings')
