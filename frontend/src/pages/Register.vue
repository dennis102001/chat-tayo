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
          <i class="fas fa-user-plus text-white text-2xl"></i>
        </div>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-100 to-blue-300 bg-clip-text text-transparent">
          Create Account
        </h1>
        <p class="text-indigo-300 text-sm mt-1">join ChatTayo today</p>
      </div>

      <form @submit.prevent="register" class="w-full space-y-4">

        <TextInput
          v-model="formData.name"
          type="text"
          placeholder="Full name"
          icon="fas fa-user"
          :error="formErrors?.name?.[0] ?? ''"
        />
        
        <TextInput
          v-model="formData.email"
          type="email"
          placeholder="Email address"
          icon="fas fa-envelope"
          :error="formErrors?.email?.[0] ?? ''"
        />

        <TextInput
          v-model="formData.password"
          type="password"
          placeholder="Password"
          icon="fas fa-lock"
          :error="formErrors?.password?.[0] ?? ''"
        />

        <TextInput
          v-model="formData.password_confirmation"
          type="password"
          placeholder="Confirm password"
          icon="fas fa-lock"
        />

        <PrimaryButton
          text="Create Account"
          type="submit"
          icon="fas fa-user-plus" 
        />

        <!-- divider -->
        <div class="divider">
          <div class="line"></div>
            or sign up with
          <div class="line"></div>
        </div>

        <!-- socials -->
        <div class="flex justify-center gap-6">
          <button @click="loginWithGoogle" type="button" class="social"><i class="fab fa-google"></i></button>
        </div>

        <!-- login link -->
        <p class="text-center text-indigo-300 text-sm mt-6">
          Already have an account?
          <RouterLink to="Login"  class="text-blue-400 font-semibold ml-1">
            Login
          </RouterLink>
        </p>

      </form>
    </div>

    <!-- bottom gradient -->
    <div class="absolute bottom-0 w-full h-1 bg-gradient-to-r from-blue-600 via-purple-500 to-blue-600 animate-gradient"></div>

    <!-- resend modal -->
    <div
      v-if="showResendVerificationModal"
      class="fixed inset-0 bg-slate-900/80 backdrop-blur flex items-center justify-center z-50 px-6"
    >
      <div class="bg-slate-900/95 backdrop-blur-xl w-full max-w-md p-8 rounded-3xl border border-blue-500/30 shadow-2xl">

        <template v-if="!emailResendVerificationSent">

          <h3 class="text-center text-xl font-bold text-white">
            📧 Verify Your Email
          </h3>

          <p class="mt-2 text-center text-sm text-slate-400">
            Please verify your email before logging in.
          </p>

          <p class="mt-4 text-center text-blue-300 font-medium break-all">
            {{ formData.email }}
          </p>

          <p class="mt-4 text-center text-sm text-slate-400">
            Didn't receive the verification email?
            Click Resend to receive a new link.
          </p>

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
            A new verification link has been sent to:
          </p>

          <p class="mt-3 text-center text-blue-300 font-medium break-all">
            {{ formData.email }}
          </p>

          <p class="mt-3 text-center text-sm text-slate-400">
            Please check your inbox and click the link to verify your email.
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
import axiosClient from '../axios.js'
import TextInput from '@/components/TextInput.vue'
import Loading from '@/components/Loading.vue'
import { ref } from 'vue'
import router from '@/router/index.js'
import { useToast } from '@/composables/useToast.js'
import PrimaryButton from '@/components/PrimaryButton.vue'

const { showToast } = useToast()
const loading = ref(false)
const loadingDetails = ref({
  title: 'Loading',
  subtitle: 'Please wait...'
})

const cooldown = ref(0)
let timer = null

const showResendVerificationModal = ref(false)
const emailResendVerificationSent = ref(false)

const formData = ref({
  name: null,
  email: null,
  password: null,
  password_confirmation: null 
})

const formErrors = ref({
  name: '',
  email: '',
  password: '',
})

async function register(){
  showLoading('Registering your account', 'Please wait while we are registering your account' )
  
  try {
    await axiosClient.post('/api/register', formData.value)

    showToast('Account created successfully', 'Success')
    
    router.push({ 
      name: 'CheckEmail',
      query: {
        email: formData.value.email
      }
    })

    clearFormData();
  } 
  catch (error) {
    if(error.response?.status === 403){
      showResendVerificationModal.value = true
      return
    }
    
    formErrors.value = error.response?.data?.errors || {}
    showToast((error.response?.data?.message ?? 'Something went wrong'), 'Error')
  }
  finally{
    closeLoading()
  }
}

async function resendVerification() {
  if (cooldown.value > 0) {
    return
  }

  const userEmail = formData.value.email.trim()

  if (!userEmail) {
    showToast('Please enter your email', 'Error')
    return
  }

  if (!userEmail.includes('@')) {
    showToast('Please enter a valid email', 'Error')
    return
  }

  showLoading('Sending verification email', 'Please wait while we send a new verification link...')

  try {
    await axiosClient.post('/api/email/resend-verification', {
      email: userEmail
    })

    emailResendVerificationSent.value = true

    showToast('Verification email sent', 'Success')

    startCooldown()
  } 
  catch (error) {
    showToast(error.response?.data?.message ?? 'Unable to send verification email', 'Error')
  } 
  finally {
    closeLoading()
  }
}

function closeResendModal() {
  showResendVerificationModal.value = false
  emailResendVerificationSent.value = false
}

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

function showLoading(title, subtitle){
  loadingDetails.value.title = title
  loadingDetails.value.subtitle = subtitle
  loading.value = true
}

function closeLoading(){
  loading.value = false
  loadingDetails.value.title = 'Loading' 
  loadingDetails.value.subtitle = 'Please wait...'
}

function clearFormData(){
  formData.value.name = null
  formData.value.email = null
  formData.value.password = null
  formData.value.password_confirmation = null

  formErrors.value.name = null
  formErrors.value.email = null
  formErrors.value.password = null
}

function loginWithGoogle() {
  window.location.href = `${import.meta.env.VITE_API_BASE_URL}/api/auth/google/redirect`
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