<template>
  <Transition name="toast">
    <div
      v-if="show"
      class="fixed top-6 right-1/2 translate-x-1/2 z-[2000]
             flex items-center gap-3 px-5 py-3 rounded-full
             backdrop-blur-md text-sm font-medium shadow-lg"
      :class="typeClass"
    >
      <!-- icon -->
      <i :class="icon"></i>

      <!-- message -->
      <span>{{ message }}</span>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  show: Boolean,
  message: String,
  type: {
    type: String,
    default: 'Info' // success | error | info
  }
})

const icon = computed(() => {
  switch (props.type) {
    case 'Success': return 'fas fa-circle-check'
    case 'Error': return 'fas fa-circle-xmark'
    default: return 'fas fa-circle-info'
  }
})

const typeClass = computed(() => {
  switch (props.type) {
    case 'Success':
      return 'bg-green-500/20 text-green-300 border border-green-400/30'
    case 'Error':
      return 'bg-red-500/20 text-red-300 border border-red-400/30'
    default:
      return 'bg-blue-500/20 text-blue-300 border border-blue-400/30'
  }
})
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.25s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translate(50%, 0px) scale(0.95);
}
</style>