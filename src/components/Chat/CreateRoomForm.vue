<template>
  <div class="create-room-form">
    <form @submit.prevent="createRoom">
      <NcTextField
        :value="roomName"
        :label="t('nextcloud_chat', 'Room name')"
        :placeholder="t('nextcloud_chat', 'Enter room name')"
        @update:value="roomName = $event"
        required
      />

      <NcTextField
        :value="roomTopic"
        :label="t('nextcloud_chat', 'Room topic')"
        :placeholder="t('nextcloud_chat', 'Optional room description')"
        @update:value="roomTopic = $event"
      />

      <NcCheckboxRadioSwitch
        :checked="isPublic"
        @update:checked="isPublic = $event"
      >
        {{ t('nextcloud_chat', 'Make room public') }}
      </NcCheckboxRadioSwitch>

      <NcCheckboxRadioSwitch
        :checked="enableE2EE"
        @update:checked="enableE2EE = $event"
        :disabled="isPublic"
      >
        {{ t('nextcloud_chat', 'Enable end-to-end encryption') }}
      </NcCheckboxRadioSwitch>

      <div class="form-actions">
        <NcButton @click="$emit('cancel')" type="secondary">
          {{ t('nextcloud_chat', 'Cancel') }}
        </NcButton>
        <NcButton type="primary" native-type="submit" :disabled="!roomName.trim()">
          {{ t('nextcloud_chat', 'Create Room') }}
        </NcButton>
      </div>
    </form>
  </div>
</template>

<script>
import {
  NcTextField,
  NcCheckboxRadioSwitch,
  NcButton
} from '@nextcloud/vue'
import { mapActions } from 'vuex'

export default {
  name: 'CreateRoomForm',
  components: {
    NcTextField,
    NcCheckboxRadioSwitch,
    NcButton
  },
  data() {
    return {
      roomName: '',
      roomTopic: '',
      isPublic: false,
      enableE2EE: true
    }
  },
  methods: {
    ...mapActions(['createRoom']),
    async createRoom() {
      try {
        const roomId = await this.createRoom({
          name: this.roomName,
          topic: this.roomTopic,
          isPublic: this.isPublic,
          enableE2EE: this.enableE2EE && !this.isPublic
        })

        this.$emit('room-created', {
          roomId,
          name: this.roomName,
          topic: this.roomTopic
        })

        // Reset form
        this.roomName = ''
        this.roomTopic = ''
        this.isPublic = false
        this.enableE2EE = true
      } catch (error) {
        console.error('Failed to create room:', error)
      }
    }
  }
}
</script>

<style scoped>
.create-room-form {
  padding: 16px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 16px;
}
</style>
