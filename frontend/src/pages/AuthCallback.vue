<template>
    <Loading
        :show="true" 
        title="Logging you in"
        subtitle="Please wait"
    />

    <div class="min-h-screen flex bg-[radial-gradient(circle_at_10%_20%,#0f192d_0%,#080c18_100%)]">

    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { initEcho } from '@/echo'
import router from '@/router'
import Loading from '@/components/Loading.vue'

onMounted(() => {

const token = new URLSearchParams(window.location.search).get('token')

if (token) {
    localStorage.setItem('token', token)
    initEcho()
    router.push({ name: 'MainChat' })
} 
else {
    router.push({ name: 'Login' })
}
    
})
</script>

<style  scoped>

</style>