<template>
  <div class="emoji-picker">
    <div class="emoji-categories">
      <button
        v-for="category in categories"
        :key="category.id"
        :class="['category-btn', { active: activeCategory === category.id }]"
        @click="setActiveCategory(category.id)"
      >
        {{ category.icon }}
      </button>
    </div>

    <div class="emoji-grid">
      <button
        v-for="emoji in filteredEmojis"
        :key="emoji.code"
        class="emoji-btn"
        @click="selectEmoji(emoji.emoji)"
        :title="emoji.name"
      >
        {{ emoji.emoji }}
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EmojiPicker',
  data() {
    return {
      activeCategory: 'smileys',
      categories: [
        { id: 'smileys', icon: '😀', name: 'Smileys & Emotion' },
        { id: 'people', icon: '👋', name: 'People & Body' },
        { id: 'animals', icon: '🐶', name: 'Animals & Nature' },
        { id: 'food', icon: '🍎', name: 'Food & Drink' },
        { id: 'activities', icon: '⚽', name: 'Activities' },
        { id: 'travel', icon: '🚗', name: 'Travel & Places' },
        { id: 'objects', icon: '💡', name: 'Objects' },
        { id: 'symbols', icon: '❤️', name: 'Symbols' }
      ],
      emojis: {
        smileys: [
          { emoji: '😀', name: 'grinning face', code: 'grinning' },
          { emoji: '😃', name: 'grinning face with big eyes', code: 'smiley' },
          { emoji: '😄', name: 'grinning face with smiling eyes', code: 'smile' },
          { emoji: '😁', name: 'beaming face with smiling eyes', code: 'grin' },
          { emoji: '😆', name: 'grinning squinting face', code: 'laughing' },
          { emoji: '😅', name: 'grinning face with sweat', code: 'sweat_smile' },
          { emoji: '🤣', name: 'rolling on the floor laughing', code: 'rofl' },
          { emoji: '😂', name: 'face with tears of joy', code: 'joy' },
          { emoji: '🙂', name: 'slightly smiling face', code: 'slightly_smiling_face' },
          { emoji: '🙃', name: 'upside-down face', code: 'upside_down_face' },
          { emoji: '😉', name: 'winking face', code: 'wink' },
          { emoji: '😊', name: 'smiling face with smiling eyes', code: 'blush' },
          { emoji: '😇', name: 'smiling face with halo', code: 'innocent' },
          { emoji: '🥰', name: 'smiling face with hearts', code: 'smiling_face_with_hearts' },
          { emoji: '😍', name: 'smiling face with heart-eyes', code: 'heart_eyes' },
          { emoji: '🤩', name: 'star-struck', code: 'star_struck' },
          { emoji: '😘', name: 'face blowing a kiss', code: 'kissing_heart' },
          { emoji: '😗', name: 'kissing face', code: 'kissing' },
          { emoji: '😚', name: 'kissing face with closed eyes', code: 'kissing_closed_eyes' },
          { emoji: '😙', name: 'kissing face with smiling eyes', code: 'kissing_smiling_eyes' },
          { emoji: '😋', name: 'face savoring food', code: 'yum' },
          { emoji: '😛', name: 'face with tongue', code: 'stuck_out_tongue' },
          { emoji: '😜', name: 'winking face with tongue', code: 'stuck_out_tongue_winking_eye' },
          { emoji: '🤪', name: 'zany face', code: 'zany_face' },
          { emoji: '😝', name: 'squinting face with tongue', code: 'stuck_out_tongue_closed_eyes' },
          { emoji: '🤑', name: 'money-mouth face', code: 'money_mouth_face' },
          { emoji: '🤗', name: 'hugging face', code: 'hugs' },
          { emoji: '🤭', name: 'face with hand over mouth', code: 'hand_over_mouth' },
          { emoji: '🤫', name: 'shushing face', code: 'shushing_face' },
          { emoji: '🤔', name: 'thinking face', code: 'thinking' }
        ],
        people: [
          { emoji: '👋', name: 'waving hand', code: 'wave' },
          { emoji: '🤚', name: 'raised back of hand', code: 'raised_back_of_hand' },
          { emoji: '🖐️', name: 'hand with fingers splayed', code: 'raised_hand_with_fingers_splayed' },
          { emoji: '✋', name: 'raised hand', code: 'hand' },
          { emoji: '🖖', name: 'vulcan salute', code: 'vulcan_salute' },
          { emoji: '👌', name: 'OK hand', code: 'ok_hand' },
          { emoji: '🤏', name: 'pinching hand', code: 'pinching_hand' },
          { emoji: '✌️', name: 'victory hand', code: 'v' },
          { emoji: '🤞', name: 'crossed fingers', code: 'crossed_fingers' },
          { emoji: '🤟', name: 'love-you gesture', code: 'love_you_gesture' },
          { emoji: '🤘', name: 'sign of the horns', code: 'metal' },
          { emoji: '🤙', name: 'call me hand', code: 'call_me_hand' },
          { emoji: '👈', name: 'backhand index pointing left', code: 'point_left' },
          { emoji: '👉', name: 'backhand index pointing right', code: 'point_right' },
          { emoji: '👆', name: 'backhand index pointing up', code: 'point_up_2' },
          { emoji: '🖕', name: 'middle finger', code: 'fu' },
          { emoji: '👇', name: 'backhand index pointing down', code: 'point_down' },
          { emoji: '☝️', name: 'index pointing up', code: 'point_up' },
          { emoji: '👍', name: 'thumbs up', code: '+1' },
          { emoji: '👎', name: 'thumbs down', code: '-1' },
          { emoji: '✊', name: 'raised fist', code: 'fist_raised' },
          { emoji: '👊', name: 'oncoming fist', code: 'fist_oncoming' },
          { emoji: '🤛', name: 'left-facing fist', code: 'fist_left' },
          { emoji: '🤜', name: 'right-facing fist', code: 'fist_right' },
          { emoji: '👏', name: 'clapping hands', code: 'clap' },
          { emoji: '🙌', name: 'raising hands', code: 'raised_hands' },
          { emoji: '👐', name: 'open hands', code: 'open_hands' },
          { emoji: '🤲', name: 'palms up together', code: 'palms_up_together' },
          { emoji: '🤝', name: 'handshake', code: 'handshake' },
          { emoji: '🙏', name: 'folded hands', code: 'pray' }
        ],
        // Add more categories as needed
        animals: [],
        food: [],
        activities: [],
        travel: [],
        objects: [],
        symbols: []
      }
    }
  },
  computed: {
    filteredEmojis() {
      return this.emojis[this.activeCategory] || []
    }
  },
  methods: {
    setActiveCategory(categoryId) {
      this.activeCategory = categoryId
    },
    selectEmoji(emoji) {
      this.$emit('emoji-selected', emoji)
    }
  }
}
</script>

<style scoped>
.emoji-picker {
  width: 300px;
  height: 400px;
  background: var(--color-main-background);
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.emoji-categories {
  display: flex;
  border-bottom: 1px solid var(--color-border);
  background: var(--color-background-dark);
}

.category-btn {
  flex: 1;
  padding: 8px;
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 16px;
  transition: background-color 0.2s;
}

.category-btn:hover {
  background: var(--color-background-hover);
}

.category-btn.active {
  background: var(--color-primary-light);
}

.emoji-grid {
  flex: 1;
  padding: 8px;
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 4px;
  overflow-y: auto;
}

.emoji-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  border-radius: 4px;
  cursor: pointer;
  font-size: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s;
}

.emoji-btn:hover {
  background: var(--color-background-hover);
}
</style>
