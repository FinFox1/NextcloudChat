<template>
  <div class="invite-users-form">
    <div class="search-section">
      <NcTextField
        :value="searchQuery"
        :label="t('nextcloud_chat', 'Search users')"
        :placeholder="t('nextcloud_chat', 'Enter username or email')"
        @update:value="searchQuery = $event"
        @input="searchUsers"
      />
    </div>

    <div class="user-results" v-if="searchResults.length > 0">
      <h4>{{ t('nextcloud_chat', 'Search Results') }}</h4>
      <div
        v-for="user in searchResults"
        :key="user.userId"
        :class="['user-result', { 'selected': selectedUsers.includes(user.userId) }]"
        @click="toggleUser(user)"
      >
        <NcAvatar
          :user="user.userId"
          :display-name="user.displayName"
          :size="32"
        />
        <div class="user-info">
          <div class="user-name">{{ user.displayName }}</div>
          <div class="user-id">{{ user.userId }}</div>
        </div>
        <div class="selection-indicator">
          <CheckIcon v-if="selectedUsers.includes(user.userId)" />
        </div>
      </div>
    </div>

    <div class="selected-users" v-if="selectedUsers.length > 0">
      <h4>{{ t('nextcloud_chat', 'Selected Users') }} ({{ selectedUsers.length }})</h4>
      <div class="selected-user-list">
        <div
          v-for="userId in selectedUsers"
          :key="userId"
          class="selected-user"
        >
          <span>{{ getUserDisplayName(userId) }}</span>
          <NcButton @click="removeUser(userId)" type="tertiary">
            <template #icon>
              <CloseIcon />
            </template>
          </NcButton>
        </div>
      </div>
    </div>

    <div class="form-actions">
      <NcButton @click="$emit('cancel')" type="secondary">
        {{ t('nextcloud_chat', 'Cancel') }}
      </NcButton>
      <NcButton
        @click="inviteUsers"
        type="primary"
        :disabled="selectedUsers.length === 0"
      >
        {{ t('nextcloud_chat', 'Invite Users') }}
      </NcButton>
    </div>
  </div>
</template>

<script>
import {
  NcTextField,
  NcButton,
  NcAvatar
} from '@nextcloud/vue'
import { generateUrl } from '@nextcloud/router'

export default {
  name: 'InviteUsersForm',
  components: {
    NcTextField,
    NcButton,
    NcAvatar
  },
  props: {
    roomId: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      searchQuery: '',
      searchResults: [],
      selectedUsers: [],
      searchTimeout: null
    }
  },
  methods: {
    searchUsers() {
      if (this.searchTimeout) {
        clearTimeout(this.searchTimeout)
      }

      this.searchTimeout = setTimeout(async () => {
        if (this.searchQuery.length < 2) {
          this.searchResults = []
          return
        }

        try {
          const response = await fetch(generateUrl('/apps/nextcloud_chat/api/users') + `?search=${encodeURIComponent(this.searchQuery)}`)
          const data = await response.json()
          this.searchResults = data.users || []
        } catch (error) {
          console.error('Failed to search users:', error)
        }
      }, 300)
    },

    toggleUser(user) {
      const index = this.selectedUsers.indexOf(user.userId)
      if (index >= 0) {
        this.selectedUsers.splice(index, 1)
      } else {
        this.selectedUsers.push(user.userId)
      }
    },

    removeUser(userId) {
      const index = this.selectedUsers.indexOf(userId)
      if (index >= 0) {
        this.selectedUsers.splice(index, 1)
      }
    },

    getUserDisplayName(userId) {
      const user = this.searchResults.find(u => u.userId === userId)
      return user?.displayName || userId
    },

    async inviteUsers() {
      try {
        for (const userId of this.selectedUsers) {
          await fetch(generateUrl(`/apps/nextcloud_chat/api/rooms/${this.roomId}/invite`), {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ user_id: userId })
          })
        }

        this.$emit('users-invited', this.selectedUsers)
      } catch (error) {
        console.error('Failed to invite users:', error)
      }
    }
  }
}
</script>

<style scoped>
.invite-users-form {
  padding: 16px;
  max-width: 500px;
}

.search-section {
  margin-bottom: 16px;
}

.user-results {
  margin-bottom: 16px;
  max-height: 300px;
  overflow-y: auto;
}

.user-result {
  display: flex;
  align-items: center;
  padding: 8px;
  border-radius: 8px;
  cursor: pointer;
  gap: 12px;
  transition: background-color 0.2s;
}

.user-result:hover {
  background: var(--color-background-hover);
}

.user-result.selected {
  background: var(--color-primary-light);
}

.user-info {
  flex: 1;
}

.user-name {
  font-weight: 500;
}

.user-id {
  font-size: 12px;
  color: var(--color-text-maxcontrast);
}

.selection-indicator {
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.selected-users {
  margin-bottom: 16px;
}

.selected-user-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.selected-user {
  display: flex;
  align-items: center;
  background: var(--color-background-dark);
  border-radius: 16px;
  padding: 4px 8px;
  gap: 8px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}
</style>
