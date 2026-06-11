<template>
  <Loading 
    :show="loading" 
    title="Registering your account"
    subtitle="Please wait while we are registering your account"
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

  </div>
</template>

<script setup>
import axiosClient from '../axios.js'
import TextInput from '@/components/TextInput.vue'
import Loading from '@/components/Loading.vue'
import { ref } from 'vue'
import Toast from '@/components/Toast.vue'
import router from '@/router/index.js'
import { useToast } from '@/composables/useToast.js'
import PrimaryButton from '@/components/PrimaryButton.vue'

const { toast, showToast } = useToast()
const loading = ref(false)

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
  loading.value = true
  
  try {
    await axiosClient.post('/api/register', formData.value)

    clearFormData();

    showToast('Successful registration', 'Success')
    setTimeout(() => {
      router.push({ name: 'Login'})
    }, 2000)
    
  } 
  catch (error) {
    formErrors.value = error.response?.data?.errors || {}
    showToast('Something went wrong', 'Error')
  }
  finally{
    loading.value = false
  }
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