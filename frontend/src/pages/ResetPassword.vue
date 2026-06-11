<template>
    <Toast
        :show="toast.show"
        :message="toast.message"
        :type="toast.type"
    />

    <Loading
        :show="loading" 
        :title="loadingDetails.title"
        :subtitle="loadingDetails.subtitle"
    />

    <div class="min-h-screen flex items-center justify-center bg-[radial-gradient(circle_at_10%_20%,#0f192d_0%,#080c18_100%)] relative overflow-hidden p-4">
        
        <!-- glow -->
        <div class="absolute w-[70vmax] h-[70vmax] bg-blue-500/10 rounded-full top-[-20%] right-[-20%] blur-3xl"></div>

        <div class="w-full max-w-md rounded-3xl border border-blue-500/20 bg-slate-900 p-8 shadow-xl relative z-10  flex flex-col items-center">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-white">
                    🔒 Reset Password
                </h1>

                <p class="mt-2 text-sm text-slate-400">
                    Create a new password for your account
                </p>
            </div>

            <div class="mt-6 space-y-4">
                <input
                    v-model="form.email"
                    type="email"
                    placeholder="Email"
                    readonly
                    class="autofill-fix w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-white outline-none focus:border-blue-500"
                >

                <input
                    v-model="form.password"
                    type="password"
                    placeholder="New password"
                    class="autofill-fix w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-white outline-none focus:border-blue-500"
                >

                <input
                    v-model="form.password_confirmation"
                    type="password"
                    placeholder="Confirm password"
                    class="autofill-fix w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-white outline-none focus:border-blue-500"
                >

                <PrimaryButton
                    @click="resetPassword"
                    :text="loading ? 'Resetting...' : 'Reset Password'"
                    :disabled="loading"
                    type="button"
                />

                <!-- divider -->
                <div class="flex items-center text-xs text-gray-400 gap-3">
                    <div class="flex-1 h-px bg-gray-500/30"></div>
                </div>

                <div class="mt-4 text-center">
                    <router-link 
                        :to="{ name: 'Login' }" 
                        class="text-sm text-slate-400 hover:text-blue-400"
                    >
                        ← Back to Login
                    </router-link>
                </div>
            </div>
        </div>

        <!-- bottom gradient line -->
        <div class="absolute bottom-0 w-full h-1 bg-gradient-to-r from-blue-600 via-purple-500 to-blue-600 animate-gradient"></div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import router from '@/router';
import Toast from '@/components/Toast.vue'
import { useToast } from '@/composables/useToast'
import axiosClient from '@/axios';
import PrimaryButton from '@/components/PrimaryButton.vue';
import Loading from '@/components/Loading.vue';

const { toast, showToast } = useToast()
const route = useRoute()

const form = ref({
    token: route.query.token || '',
    email: route.query.email || '',
    password: '',
    password_confirmation: ''
})

const loading = ref(false)
const loadingDetails = ref({
  title: 'Loading',
  subtitle: 'Please wait...'
})

async function resetPassword() {
    if(!form.value.token || !form.value.email){
        showToast('Invalid reset link', 'Error')
        return
    }

    if (!form.value.password) {
        showToast('Enter a password', 'Error')
        return
    }

    if (form.value.password.length < 8) {
        showToast('Password must be at least 8 characters', 'Error')
        return
    }

    if (
        form.value.password !==
        form.value.password_confirmation
    ) {
        showToast('Passwords do not match', 'Error')
        return
    }

    try {
        showLoading('Resetting Password', 'Please wait while we reset your password' )

        await axiosClient.post('/api/reset-password', form.value)

        showToast('Password reset successfully', 'Success')

        setTimeout(() => {
            router.push({ name: 'Login' })
        }, 2000)
    } 
    catch (error) {
        showToast('Reset failed', 'Error')
    } 
    finally {
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

</script>

<style scoped>

</style>