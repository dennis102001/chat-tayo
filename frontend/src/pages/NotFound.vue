<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-950 px-6">
    <div class="text-center max-w-md">
      <h1 class="text-8xl font-extrabold bg-gradient-to-r from-blue-500 to-cyan-400 bg-clip-text text-transparent">
        404
      </h1>

      <h2 class="mt-4 text-2xl font-semibold text-white">
        Page Not Found
      </h2>

      <p class="mt-3 text-slate-400">
        The page you're looking for doesn't exist or has been moved.
      </p>

      <div class="mt-8 space-y-4">
        <PrimaryButton
          @click="goHome"
          text="Go Home"
          type="button"
        />
        
        <!-- divider -->
        <div class="flex items-center text-xs text-gray-400 gap-3">
          <div class="flex-1 h-px bg-gray-500/30"></div>
        </div>

        <div class="mt-4 text-center">
          <button 
            @click="goBack"
            class="text-sm text-slate-400 hover:text-blue-400"
          >
            ← Go Back
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import PrimaryButton from '@/components/PrimaryButton.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

function goHome() {
  router.push('/')
}

function goBack() {
  router.back()
}
</script>