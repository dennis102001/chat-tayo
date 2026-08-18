<script setup>
import Toast from './components/Toast.vue';
import { useToast } from './composables/useToast';

const { toast } = useToast()
</script>

<template>
  <Toast
    :show="toast.show"
    :message="toast.message"
    :type="toast.type"
  />

  <RouterView/>
</template>

<style scoped></style>
