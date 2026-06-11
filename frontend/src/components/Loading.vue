<template>
  <div
    v-if="show"
    class="fixed inset-0 z-[1000] flex items-center justify-center flex-col gap-6
           bg-[#080c18]/85 backdrop-blur-lg transition-all duration-300"
  >
    <div
      class="text-center rounded-[48px] px-10 py-7
             bg-[#0f192d]/60 backdrop-blur-xl
             border border-blue-500/30
             shadow-[0_25px_40px_-15px_rgba(0,0,0,0.4)]"
    >
      <!-- bubbles -->
      <div class="flex justify-center gap-3 mb-4">
        <div
          v-for="i in 4"
          :key="i"
          class="w-[18px] h-[18px] rounded-full animate-bubble"
          :class="bubbleColors[i]"
        />
      </div>

      <!-- text -->
      <div class="flex items-center justify-center gap-2 text-slate-200 font-medium">
        <span>{{ title }}</span>

        <!-- dots -->
        <!-- <div class="flex gap-[3px]">
          <span class="dot" v-for="i in 3" :key="i"></span>
        </div> -->
      </div>

      <p class="text-xs text-[#8da3cf] mt-3">
        {{ subtitle }}
      </p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Loading'
  },
  subtitle: {
    type: String,
    default: 'Please wait'
  }
})

const bubbleColors = {
  1: 'bg-blue-400',
  2: 'bg-blue-500',
  3: 'bg-cyan-400',
  4: 'bg-violet-500'
}

</script>

<style scoped>
@keyframes bubblePulse {
  0%, 100% {
    transform: scale(0.8);
    opacity: 0.5;
  }
  50% {
    transform: scale(1.3);
    opacity: 1;
    box-shadow: 0 0 12px rgba(59,130,246,0.6);
  }
}

.animate-bubble {
  animation: bubblePulse 1.4s ease-in-out infinite;
}

.animate-bubble:nth-child(2) { animation-delay: 0.2s; }
.animate-bubble:nth-child(3) { animation-delay: 0.4s; }
.animate-bubble:nth-child(4) { animation-delay: 0.6s; }

/* dots */
@keyframes dotFade {
  0%, 100% { opacity: 0.3; transform: translateY(0); }
  50% { opacity: 1; transform: translateY(-3px); background: #60a5fa; }
}

.dot {
  width: 5px;
  height: 5px;
  background: #94a3b8;
  border-radius: 9999px;
  animation: dotFade 1.2s infinite;
}

.dot:nth-child(2) { animation-delay: 0.2s; }
.dot:nth-child(3) { animation-delay: 0.4s; }
</style>