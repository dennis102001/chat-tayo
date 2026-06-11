<template>
    <div :class="['flex flex-col h-[62px]', error && 'group/error']">

        <div class="relative ">
            <i 
                class=" absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" 
                :class="[
                    icon, 
                    'absolute left-0 text-gray-500 transition-colors',
                    error ? 'text-red-400' : 'group-focus-within:text-blue-500'
                ]"
            ></i>

            <input
                v-model="model"
                v-bind="$attrs"
                class="autofill-fix pl-12 block w-full rounded-md bg-[#16243f] px-3 py-2 
                text-base text-white sm:text-sm/6 placeholder:text-gray-500 outline-none 
                border-0 border-b-2 border-gray-400/50 focus:border-blue-500"
                :class="error && 'border-red-400 focus:border-red-400'"
            />
        </div>
        
        <div
            v-if="error"
            class="flex items-center gap-1 text-red-400 text-xs mt-1 animate-fade"
        >
            <i class="fa fa-exclamation-circle text-[10px]"></i>
            <span>{{ error }}</span>
        </div>
    </div>
    
</template>

<script setup>
const props = defineProps({
    icon: {
        type: String,
        required: true
    },
    error: {
        type: String,
    }
})

const model = defineModel({
    type: null,
    required: true
})

</script>

<style lang="scss" scoped>

</style>