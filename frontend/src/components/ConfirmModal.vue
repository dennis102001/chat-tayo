<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur"
    >
        <div class="bg-slate-900 border border-blue-500/30 rounded-2xl p-6 w-full max-w-sm text-center shadow-xl">
        
        <h3 class="text-white text-lg font-semibold mb-2">
            {{ title }}
        </h3>

        <p class="text-slate-400 text-sm mb-6">
            {{ message }}
        </p>

        <div class="flex gap-3 justify-center">
            <button
                @click="$emit('confirm')"
                class="px-4 py-2 rounded-full bg-red-500 hover:bg-red-600 text-white text-sm"
            >
                Yes
            </button>

            <button
                @click="$emit('cancel')"
                class="px-4 py-2 rounded-full bg-white/10 hover:bg-white/20 text-white text-sm"
            >
                Cancel
            </button>
        </div>

        </div>
    </div>
</template>

<script setup>
defineProps({
  open: Boolean,
  title: String,
  message: String
})
</script>