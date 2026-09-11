<template>
  <Loading
    :show="loading"
    :title="loadingDetails.title"
    :subtitle="loadingDetails.subtitle"
  />

  <div class="min-h-screen flex items-center justify-center bg-[radial-gradient(circle_at_10%_20%,#0f192d_0%,#080c18_100%)] relative overflow-hidden">

    <!-- glow -->
    <div class="absolute w-[70vmax] h-[70vmax] bg-blue-500/10 rounded-full top-[-20%] right-[-20%] blur-3xl"></div>

    <!-- container -->
    <div class="relative z-10 w-full max-w-md px-6 flex flex-col items-center py-8">

      <!-- brand -->
      <div class="text-center mb-8">
        <div class="w-16 h-16 flex items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-400 shadow-lg mb-4 mx-auto">
          <i class="fas fa-comment-dots text-white text-2xl"></i>
        </div>

        <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-100 to-blue-300 bg-clip-text text-transparent">
          ChatTayo
        </h1>

        <p class="text-indigo-300 text-sm mt-1">
          where conversations flow freely
        </p>
      </div>

      <!-- card -->
      <div class="w-full bg-slate-900/80 backdrop-blur-xl p-8 rounded-3xl border border-blue-500/30 shadow-2xl text-center">

        <div class="w-16 h-16 mx-auto flex items-center justify-center rounded-full bg-blue-500/10 border border-blue-500/30">
          <i class="fas fa-envelope text-blue-400 text-2xl"></i>
        </div>

        <h2 class="mt-6 text-2xl font-bold text-white">
          Check Your Email
        </h2>

        <p class="mt-3 text-sm text-slate-400 leading-relaxed">
          Your account has been created successfully.
          We've sent a verification link to:
        </p>

        <p class="mt-3 text-blue-300 font-medium break-all">
          {{ email }}
        </p>

        <p class="mt-4 text-sm text-slate-400">
          Please check your inbox and click the verification link
          to activate your account.
        </p>

        <!-- resend -->
        <button
          @click="resendVerification"
          :disabled="cooldown > 0"
          class="mt-6 text-sm text-blue-400 hover:underline disabled:text-slate-500 disabled:no-underline disabled:cursor-not-allowed"
        >
          {{
            cooldown > 0
              ? `Resend available in ${cooldown}s`
              : "Didn't receive the email? Resend"
          }}
        </button>

        <div class="mt-6">
          <PrimaryButton
            @click="goToLogin"
            text="Go to Login"
            type="button"
            icon="fas fa-arrow-right-to-bracket"
          />
        </div>

        <p class="mt-5 text-xs text-slate-500">
          Don't see the email? Check your spam or junk folder.
        </p>

      </div>

    </div>

    <!-- bottom gradient line -->
    <div class="absolute bottom-0 w-full h-1 bg-gradient-to-r from-blue-600 via-purple-500 to-blue-600 animate-gradient"></div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import PrimaryButton from '@/components/PrimaryButton.vue'
import Loading from '@/components/Loading.vue'
import axiosClient from '@/axios'
import router from '@/router'
import { useToast } from '@/composables/useToast'

const { showToast } = useToast()

const email = ref(
  router.currentRoute.value.query.email ?? ''
)

const loading = ref(false)

const loadingDetails = ref({
  title: 'Sending verification email',
  subtitle: 'Please wait...'
})

const cooldown = ref(5)
let timer = null

onMounted(() => {
  startCooldown()
})

onUnmounted(() => {
  if (timer) {
    clearInterval(timer)
  }
})

function startCooldown() {
  cooldown.value = 5

  timer = setInterval(() => {
    cooldown.value--

    if (cooldown.value <= 0) {
      clearInterval(timer)
      timer = null
    }
  }, 1000)
}

async function resendVerification() {
  if (cooldown.value > 0) {
    return
  }

  if (!email.value) {
    showToast('Email address is missing', 'Error')
    return
  }

  loading.value = true

  try {
    await axiosClient.post('/api/email/resend-verification', {
      email: email.value
    })

    showToast('Verification email sent', 'Success')

    startCooldown()
  } 
  catch (error) {
    showToast( error.response?.data?.message ?? 'Unable to send verification email', 'Error' )
  } 
  finally {
    loading.value = false
  }
}

function goToLogin() {
  router.push({ name: 'Login' })
}
</script>

<style>
@keyframes gradientMove {
  0% { background-position: 0% }
  100% { background-position: 200% }
}

.animate-gradient {
  background-size: 200%;
  animation: gradientMove 6s linear infinite;
}
</style>