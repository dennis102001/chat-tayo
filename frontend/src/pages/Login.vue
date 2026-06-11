<template>
  <Loading
    :show="loading" 
    :title="loadingDetails.title"
    :subtitle="loadingDetails.subtitle"
  />

  <Toast
    :show="toast.show"
    :message="toast.message"
    :type="toast.type"
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
        <p class="text-indigo-300 text-sm mt-1">where conversations flow freely</p>
      </div>

      <form @submit.prevent="login" class="w-full space-y-4">
        <TextInput
          v-model="formData.email"
          type="email"
          placeholder="Email address"
          icon="fas fa-envelope"
        />

        <TextInput
          v-model="formData.password"
          type="password"
          placeholder="Password"
          icon="fas fa-lock"
        />
        <!-- options -->
        <div class="flex justify-between text-sm text-indigo-200">
          <label for="remember" class="flex items-center gap-2 cursor-pointer">
            <input id="remember" type="checkbox" v-model="formData.remember" class="accent-blue-500">
            Keep me signed in
          </label>

          <button @click="showForgotPasswordModal = true" type="button" class="text-blue-400 hover:underline">
            Forgot?
          </button>
        </div>

        <!-- button -->
        <PrimaryButton
          text="Log in"
          type="submit"
          icon="fas fa-arrow-right-to-bracket" 
        />

        <!-- divider -->
        <div class="flex items-center text-xs text-gray-400 gap-3">
          <div class="flex-1 h-px bg-gray-500/30"></div>
          or continue with
          <div class="flex-1 h-px bg-gray-500/30"></div>
        </div>

        <!-- socials -->
        <div class="flex justify-center gap-6">
          <button @click="loginWithGoogle" type="button" class="social"><i class="fab fa-google"></i></button>
          
        </div>

        <!-- signup -->
        <p class="text-center text-indigo-300 text-sm mt-6">
          Don't have an account?
          <RouterLink to="Register" class="text-blue-400 font-semibold ml-1">
            Create
          </RouterLink>
        </p>

      </form>
    </div>

    <!-- bottom gradient line -->
    <div class="absolute bottom-0 w-full h-1 bg-gradient-to-r from-blue-600 via-purple-500 to-blue-600 animate-gradient"></div>

    <div
      v-if="showForgotPasswordModal"
      class="fixed inset-0 bg-slate-900/80 backdrop-blur flex items-center justify-center z-50"
    >

      <div v-if="!emailSent" class="bg-slate-900/95 backdrop-blur-xl w-full max-w-md p-8 rounded-3xl border border-blue-500/30 shadow-2xl">
        
        <h3 class="text-center text-xl font-bold text-white">
          🔑 Forgot Password?
        </h3>

        <p class="mt-2 text-center text-sm text-slate-400">
          Enter your email address and we'll send you a reset link.
        </p>

        <input
          v-model="forgotPasswordFormData.email"
          type="email"
          placeholder="Email address"
          class="autofill-fix mt-6 w-full rounded-xl border border-slate-600 bg-slate-800 px-4 py-3 text-white outline-none focus:border-blue-500"
        />

        <div class="mt-6 flex gap-3">
          <button
            @click="sendResetLink"
            class="flex-1 rounded-full bg-red-500 py-3 text-white hover:bg-red-600"
          >
            Send Reset Link
          </button>

          <button
            @click="closeForgotPasswordModal"
            class="flex-1 rounded-full bg-slate-700 py-3 text-white hover:bg-slate-600"
          >
            Cancel
          </button>
        </div>

      </div>

      <div v-else class="bg-slate-900/95 backdrop-blur-xl w-full max-w-sm p-8 rounded-3xl border border-blue-500/30 shadow-2xl">
        <h3 class="text-center text-xl font-bold text-white">✓ Password reset email sent</h3>

        <p class="mt-2 text-center text-sm text-slate-400">
          If an account exists for this email,
          a reset link has been sent.
        </p>

        <div class="mt-6">
          <PrimaryButton
            @click="closeForgotPasswordModal"
            text="Ok"
            type="button"
          />
        </div>
        
      </div>

    </div>
  </div>
</template>

<script setup>
import TextInput from '@/components/TextInput.vue'
import { ref, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'
import Loading from '@/components/Loading.vue'
import Toast from '@/components/Toast.vue'
import axiosClient from '@/axios'
import router from '@/router'
import PrimaryButton from '@/components/PrimaryButton.vue'
import { initEcho } from '@/echo'

const { toast, showToast } = useToast()

const loading = ref(false)
const loadingDetails = ref({
  title: 'Loading',
  subtitle: 'Please wait...'
})
const showForgotPasswordModal = ref(false)
const emailSent = ref(false)

const formData = ref({
  email: null,
  password: null,
  remember: null
})

const forgotPasswordFormData = ref({
  email: ''
})

async function login() {
  showLoading('Logging in', 'Please wait while we are checking your credentials' )

  try {
    const response = await axiosClient.post('/api/login', formData.value)
    
    localStorage.setItem('token', response.data.token)
    initEcho()

    showToast('Successful login', 'Success')

    setTimeout(() => {
      router.push({ name: 'MainChat' })
    }, 2000)

  } 
  catch (error) {
    showToast('Invalid credentials', 'Error')
  }  
  finally {
    closeLoading()
  }
  
}

function loginWithGoogle() {
  showLoading('Connecting your Google account', 'Please wait...' )

  window.location.href = `${import.meta.env.VITE_API_BASE_URL}/api/auth/google/redirect`

}

function closeForgotPasswordModal() {
  showForgotPasswordModal.value = false
  forgotPasswordFormData.value.email = ''
  emailSent.value = false
}

async function sendResetLink() {
  const userEmail = forgotPasswordFormData.value.email.trim()

  if (!userEmail) {
    showToast('Please enter your email', 'Error')
    return
  }

  if (!userEmail.includes('@')) {
    showToast('Please enter a valid email', 'Error')
    return
  }

  showLoading('Sending reset link', 'Please wait while we are sending a reset link to your email account' )

  try {
    await axiosClient.post('/api/forgot-password', forgotPasswordFormData.value)
    emailSent.value = true
    showToast(`Reset link sent to ${userEmail}`, 'Success')
  } 
  catch (error) {
    showToast('An error occured', 'Error')
  }
  finally{
    closeLoading()
  }
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
onMounted(() => {
  
})
</script>

<style>


/* gradient animation */
@keyframes gradientMove {
  0% { background-position: 0% }
  100% { background-position: 200% }
}
.animate-gradient {
  background-size: 200%;
  animation: gradientMove 6s linear infinite;
}
</style>