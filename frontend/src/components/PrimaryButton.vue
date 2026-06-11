<template>
    <button v-bind="$attrs" class="cursor-pointer w-full py-3 rounded-full bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold flex items-center justify-center gap-2 hover:-translate-y-1 transition">
        <i :class="icon"></i>
        {{ text }}
    </button>
</template>

<script setup>
const props = defineProps({
    text: {
        type: String,
        required: true
    },
    icon: {
        type: String,
    }
})
</script>

<style lang="scss" scoped>

</style>