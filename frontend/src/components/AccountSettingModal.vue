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

      <!-- Avatar -->
      <div class="flex flex-col items-center mb-6">
        <div class="w-24 h-24 rounded-full overflow-hidden flex items-center justify-center text-3xl font-semibold text-white bg-gradient-to-br from-blue-500 to-cyan-400 cursor-pointer">
          <img v-if="preview" :src="preview" class="w-full h-full object-cover" />
          <span v-else>{{ initial }}</span>
        </div>

        <div class="flex flex-wrap gap-4">
          <button
            @click="fileInput.click()"
            class="mt-3 px-4 py-1.5 text-sm rounded-full border border-blue-400/40 bg-blue-500/20 text-blue-300 hover:bg-blue-500/40"
          >
            <i class="fas fa-camera mr-1"></i> Change
          </button>

          <button
            v-if="preview"
            @click="removeAvatar"
            class="mt-3 px-4 py-1.5 text-sm rounded-full border border-red-400/40 bg-red-500/20 text-red-300 hover:bg-red-500/40"
          >
            <i class="fas fa-trash mr-1"></i> Remove
          </button>
        </div>

        <input
          ref="fileInput"
          type="file"
          class="hidden"
          accept="image/*"
          @change="handleFile"
        />
      </div>

      <!-- Form -->
      <div class="space-y-4">
        <div>
          <label class="text-blue-200 text-xs">Display Name</label>
          <input
              v-model="form.name"
              class="w-full mt-1 px-4 py-2 rounded-xl bg-white/5 border border-slate-400/30 text-white focus:border-blue-500 focus:bg-blue-500/10 outline-none"
            />
        </div>

        <div>
          <label class="text-blue-200 text-xs">Email</label>
          <input
            v-model="form.email"
            type="email"
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
import { ref, watch, computed, onMounted } from 'vue'

const emit = defineEmits(['close', 'save'])
const props = defineProps({
  open: Boolean,
  user: Object
})

const form = ref({
  name: '',
  email: '',
  avatar: null,
  remove_avatar: false
})

const preview = ref(null)
const fileInput = ref(null)

const initial = computed(() => {
    return form.value.name ? form.value.name.charAt(0).toUpperCase() : '?'
})

watch(
  () => props.open, 
  (isOpen) => {
    if( isOpen && props.user ){
      form.value.name = props.user.name
      form.value.email = props.user.email
      form.value.avatar = null
      form.value.remove_avatar = false
      preview.value = props.user.avatar_url || null
    }
  },
  { immediate: true }
)

function handleFile(e) {
  const file = e.target.files[0]
  if (!file) return
  
  form.value.avatar = file
  preview.value = URL.createObjectURL(file)
  form.value.remove_avatar = false
}

function removeAvatar(){
  form.value.remove_avatar = true
  form.value.avatar = null
  preview.value = null
}

function save(){
  emit('save', form.value)
}

function closeModal(){
  emit('close')
}

</script>

<style scoped>

</style>