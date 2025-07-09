<template>
  <div class="chat-room-list">
    <div class="room-list-header">
      <h3>{{ t('nextcloud_chat', 'Rooms') }}</h3>
      <NcButton @click="showCreateRoomDialog = true" type="primary">
        <template #icon>
          <PlusIcon />
        </template>
        {{ t('nextcloud_chat', 'New Room') }}
      </NcButton>
    </div>

    <div class="room-search">
      <NcTextField
        :value="searchQuery"
        :label="t('nextcloud_chat', 'Search rooms')"
        :placeholder="t('nextcloud_chat', 'Search rooms...')"
        @update:value="searchQuery = $event"
      >
        <template #trailing-button-icon>
          <MagnifyIcon />
        </template>
      </NcTextField>
    </div>

    <div class="room-list">
      <NcListItem
        v-for="room in filteredRooms"
        :key="room.roomId"
        :title="room.name"
        :subtitle="room.lastMessage"
        :counter-number="room.unreadCount"
        :active="currentRoom?.roomId === room.roomId"
        @click="selectRoom(room)"
      >
        <template #icon>
          <NcAvatar
            v-if="room.avatarUrl"
            :url="room.avatarUrl"
            :size="32"
          />
          <div v-else class="room-avatar">
            {{ room.name.charAt(0).toUpperCase() }}
          </div>
        </template>

        <template #actions>
          <NcActionButton @click="openRoomSettings(room)">
            <template #icon>
              <CogIcon />
            </template>
            {{ t('nextcloud_chat', 'Room settings') }}
          </NcActionButton>
          <NcActionButton @click="leaveRoom(room.roomId)">
            <template #icon>
              <ExitToAppIcon />
            </template>
            {{ t('nextcloud_chat', 'Leave room') }}
          </NcActionButton>
        </template>
      </NcListItem>
    </div>

    <!-- Create Room Dialog -->
    <NcDialog
      :open="showCreateRoomDialog"
      @update:open="showCreateRoomDialog = false"
    >
      <template #header>
        {{ t('nextcloud_chat', 'Create New Room') }}
      </template>
      <CreateRoomForm @room-created="onRoomCreated" @cancel="showCreateRoomDialog = false" />
    </NcDialog>
  </div>
</template>

<script>
import {
  NcButton,
  NcTextField,
  NcListItem,
  NcAvatar,
  NcActionButton,
  NcDialog
} from '@nextcloud/vue'
import { mapState, mapActions } from 'vuex'
import CreateRoomForm from './CreateRoomForm.vue'

export default {
  name: 'ChatRoomList',
  components: {
    NcButton,
    NcTextField,
    NcListItem,
    NcAvatar,
    NcActionButton,
    NcDialog,
    CreateRoomForm
  },
  data() {
    return {
      searchQuery: '',
      showCreateRoomDialog: false
    }
  },
  computed: {
    ...mapState(['rooms', 'currentRoom']),
    filteredRooms() {
      if (!this.searchQuery) return this.rooms
      return this.rooms.filter(room =>
        room.name.toLowerCase().includes(this.searchQuery.toLowerCase())
      )
    }
  },
  methods: {
    ...mapActions(['leaveRoom']),
    selectRoom(room) {
      this.$store.commit('setCurrentRoom', room)
      this.$emit('room-selected', room)
    },
    openRoomSettings(room) {
      this.$emit('open-room-settings', room)
    },
    onRoomCreated(room) {
      this.showCreateRoomDialog = false
      this.selectRoom(room)
    }
  }
}
</script>

<style scoped>
.chat-room-list {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.room-list-header {
  padding: 16px;
  border-bottom: 1px solid var(--color-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.room-search {
  padding: 8px 16px;
  border-bottom: 1px solid var(--color-border);
}

.room-list {
  flex: 1;
  overflow-y: auto;
}

.room-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--color-primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}
</style>
