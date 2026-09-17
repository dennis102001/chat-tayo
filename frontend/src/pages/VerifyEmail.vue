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

        <!-- success -->
        <template v-if="status === 'success'">

            <div class="w-16 h-16 mx-auto flex items-center justify-center rounded-full bg-green-500/10 border border-green-500/30">
                <i class="fas fa-check text-green-400 text-2xl"></i>
            </div>

            <h2 class="mt-6 text-2xl font-bold text-white">
                Email Verified!
            </h2>

            <p class="mt-3 text-sm text-slate-400 leading-relaxed">
                Your email address has been successfully verified.
                You can now log in to your ChatTayo account.
            </p>

            <div class="mt-6">
                <PrimaryButton
                    @click="goToLogin"
                    text="Go to Login"
                    type="button"
                    icon="fas fa-arrow-right-to-bracket"
                />
            </div>

        </template>

        <!-- error -->
        <template v-else-if="status === 'error'">

            <div class="w-16 h-16 mx-auto flex items-center justify-center rounded-full bg-red-500/10 border border-red-500/30">
                <i class="fas fa-link-slash text-red-400 text-2xl"></i>
            </div>

            <h2 class="mt-6 text-2xl font-bold text-white">
                Verification Link Expired
            </h2>

            <p class="mt-3 text-sm text-slate-400 leading-relaxed">
                This verification link is invalid or has expired.
                Request a new verification email to verify your account.
            </p>

            <div class="mt-6">
                <PrimaryButton
                    @click="showResendModal = true"
                    text="Resend Verification Email"
                    type="button"
                    icon="fas fa-paper-plane"
                />
            </div>

            <button
                @click="goToLogin"
                class="mt-4 text-sm text-blue-400 hover:underline"
            >
                Back to Login
            </button>

        </template>

      </div>

    </div>

    <!-- bottom gradient line -->
    <div class="absolute bottom-0 w-full h-1 bg-gradient-to-r from-blue-600 via-purple-500 to-blue-600 animate-gradient"></div>

    <!-- CONTINUE HERE -->
    <!-- resend modal -->
    <div
      v-if="showResendModal"
      class="fixed inset-0 bg-slate-900/80 backdrop-blur flex items-center justify-center z-50 px-6"
    >

      <div class="bg-slate-900/95 backdrop-blur-xl w-full max-w-md p-8 rounded-3xl border border-blue-500/30 shadow-2xl">

        <template v-if="!emailSent">

          <h3 class="text-center text-xl font-bold text-white">
            📧 Resend Verification Email
          </h3>

          <p class="mt-2 text-center text-sm text-slate-400">
            Enter the email address associated with your account.
          </p>

          <input
            v-model="email"
            type="email"
            placeholder="Email address"
            class="autofill-fix mt-6 w-full rounded-xl border border-slate-600 bg-slate-800 px-4 py-3 text-white outline-none focus:border-blue-500"
          />

          <div class="mt-6 flex gap-3">

            <button
              @click="resendVerification"
              :disabled="cooldown > 0"
              class="flex-1 rounded-full bg-blue-500 py-3 text-white hover:bg-blue-600"
            >
              {{
                cooldown > 0 
                ? `Resend in ${cooldown}s` 
                : `Resend`
              }}
            </button>

            <button
              @click="closeResendModal"
              class="flex-1 rounded-full bg-slate-700 py-3 text-white hover:bg-slate-600"
            >
              Cancel
            </button>

          </div>

        </template>

        <template v-else>

          <h3 class="text-center text-xl font-bold text-white">
            ✓ Verification Email Sent
          </h3>

          <p class="mt-2 text-center text-sm text-slate-400">
            A new verification link has been sent to your email address.
          </p>

          <div class="mt-6">
            <PrimaryButton
              @click="closeResendModal"
              text="Ok"
              type="button"
            />
          </div>

        </template>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Loading from '@/components/Loading.vue'
import PrimaryButton from '@/components/PrimaryButton.vue'
import axiosClient from '@/axios'
import router from '@/router'
import { useToast } from '@/composables/useToast'

const { showToast } = useToast()

const loading = ref(false)
const loadingDetails = ref({
  title: 'Verifying your email',
  subtitle: 'Please wait while we verify your email address...'
})

const status = ref('loading')

const cooldown = ref(0)
let timer = null

const email = ref('')
const showResendModal = ref(false)
const emailSent = ref(false)

function startCooldown() {
  cooldown.value = 30

  timer = setInterval(() => {
    cooldown.value--

    if (cooldown.value <= 0) {
      clearInterval(timer)
      timer = null
    }
  }, 1000)
}

onMounted(async () => {
  const params = new URLSearchParams(window.location.search)

  const token = params.get('token')
  const userEmail = params.get('email')

  email.value = userEmail ?? ''

  if (!token || !userEmail) {
    status.value = 'error'
    return
  }

  loading.value = true

  try {
    await axiosClient.post('/api/email/verify', {
      token,
      email: userEmail
    })

    status.value = 'success'
  } 
  catch (error) {
    status.value = 'error'
  } 
  finally {
    loading.value = false
  }
})

async function resendVerification() {
  if (cooldown.value > 0) {
    return
  }

  const userEmail = email.value.trim()

  if (!userEmail) {
    showToast('Please enter your email', 'Error')
    return
  }

  if (!userEmail.includes('@')) {
    showToast('Please enter a valid email', 'Error')
    return
  }

  loadingDetails.value.title = 'Sending verification email'
  loadingDetails.value.subtitle = 'Please wait while we send a new verification link...'
  loading.value = true

  try {
    await axiosClient.post('/api/email/resend-verification', {
      email: userEmail
    })

    emailSent.value = true

    showToast('Verification email sent', 'Success')

    startCooldown()
  } 
  catch (error) {
    showToast(
        error.response?.data?.message ?? 'Unable to send verification email',
        'Error'
    )
  } 
  finally {
    loading.value = false
  }
}

function closeResendModal() {
  showResendModal.value = false
  emailSent.value = false
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