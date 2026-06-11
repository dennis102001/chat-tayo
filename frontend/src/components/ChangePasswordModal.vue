<template>
  <div
    v-if="open"
    @click.self="closeModal"
    class="fixed inset-0 bg-slate-900/80 backdrop-blur flex items-center justify-center z-50"
  >
    <div class="bg-slate-900/95 backdrop-blur-xl w-full max-w-md p-8 rounded-3xl border border-blue-500/30 shadow-2xl">

      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-blue-100 text-xl font-semibold flex items-center gap-2">
          <i class="fas fa-user-cog"></i> Account Settings
        </h2>
        <button @click="closeModal" class="text-slate-400 hover:text-red-400 text-lg">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Form -->
      <div class="space-y-4">
        <div v-if="hasPassword">
          <label class="text-blue-200 text-xs">Old Password</label>
          <input
                type="password"
                v-model="form.oldPassword"
                class="w-full mt-1 px-4 py-2 rounded-xl bg-white/5 border border-slate-400/30 text-white focus:border-blue-500 focus:bg-blue-500/10 outline-none"
            />
        </div>

        <div>
          <label class="text-blue-200 text-xs">New Password</label>
          <input
            type="password"
              v-model="form.newPassword"
              class="w-full mt-1 px-4 py-2 rounded-xl bg-white/5 border border-slate-400/30 text-white focus:border-blue-500 focus:bg-blue-500/10 outline-none"
            />
        </div>

        <div>
          <label class="text-blue-200 text-xs">Confirm Password</label>
          <input
            v-model="form.confirmation"
            type="password"
            class="w-full mt-1 px-4 py-2 rounded-xl bg-white/5 border border-slate-400/30 text-white focus:border-blue-500 focus:bg-blue-500/10 outline-none"
          />
        </div>

        <button
          @click="save"
          class="w-full mt-2 py-3 rounded-full bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold hover:translate-y-[-2px] transition"
        >
          <i class="fas fa-save"></i> Save Changes
        </button>
        
      </div>
    </div>
  </div>
  
</template>

<script setup>
import { ref, watch } from 'vue'

const emit = defineEmits(['close', 'save', 'hasError'])
const props = defineProps({
  open: Boolean,
  hasPassword: Boolean
})

const form = ref({
  oldPassword: '',
  newPassword: '',
  confirmation: '',
})

watch(
  () => props.open,
  (isOpen) => {
    if(isOpen){
      form.value.oldPassword = ''
      form.value.newPassword = ''
      form.value.confirmation = ''
    }
  },
  {immediate: true}
)

function save(){
  emit('save', form.value)
}

function closeModal(){
  emit('close')
}

</script>