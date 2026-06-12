<template>
  <Loading
    :show="loading" 
    title="Loading"
    subtitle="Please wait"
  />
  
  <Toast
    :show="toast.show"
    :message="toast.message"
    :type="toast.type"
  />

  <AccountSettingModal
    :open="showAccountSettingModal"
    :user="authUser"
    @close="showAccountSettingModal = false"
    @save="updateUser"
  />

  <ChangePasswordModal
    :open="showChangePasswordModal"
    :hasPassword="authUser?.has_password"
    @close="showChangePasswordModal = false"
    @save="updatePassword"
  />

  <ConfirmModal
    :open="showConfirmModal"
    :title="confirmTitle"
    :message="confirmMessage"
    @confirm="onConfirm"
    @cancel="cancelAction"
  />

  <div class="h-screen flex bg-[radial-gradient(circle_at_10%_20%,#0f192d_0%,#080c18_100%)] text-gray-200 overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-full md:w-80 flex-col border-r border-white/10 backdrop-blur"
      :class="isSidebarOpen ? 'flex' : 'hidden md:flex'"
    >

      <!-- user header -->
      <div class="p-4 border-b border-white/10 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full overflow-hidden flex items-center justify-center text-lg font-semibold text-white bg-gradient-to-br from-blue-500 to-cyan-400 cursor-pointer">
            <img
              v-if="authUser?.avatar_url"
              :src="authUser?.avatar_url"
              class="w-full h-full object-cover"
            />
            <span v-else>{{ authUser?.name.charAt(0).toUpperCase() ?? '?' }}</span>
          </div>

          <div>
            <h2 class="text-sm font-semibold">{{ authUser?.name }}</h2>
            <span v-if="isOnline(authUser?.id)" class="text-xs text-green-400">
              ● Online
            </span>
            <span v-else class="text-xs text-gray-400">
              ● Offline
            </span>
          </div>
        </div>

        <div ref="profileDropdown" class="relative">
          <button
            @click="toggleProfileDropdown"
            class="flex items-center gap-2 px-3 py-2 rounded-md text-slate-400 hover:bg-blue-500/20 hover:text-blue-400 transition cursor-pointer"
          >
            <i class="fa-solid fa-bars"></i>
          </button>

          <!-- Dropdown -->
          <div
            v-if="isProfileDropdownOpen"
            class="absolute z-50 right-0 mt-2 w-48 rounded-xl border border-blue-500/30 bg-slate-900/90 backdrop-blur shadow-xl overflow-hidden"
          >
            <button
              @click="showAccountSettingModal = true"
              class="w-full flex items-center gap-3 px-4 py-3 text-sm text-slate-200 hover:bg-blue-500/20 transition"
            >
              <i class="fas fa-user-cog text-slate-400"></i>
              Account Settings
            </button>

            <div class="h-px bg-slate-600/30"></div>

            <button
              @click="showChangePasswordModal = true"
              class="w-full flex items-center gap-3 px-4 py-3 text-sm text-slate-200 hover:bg-blue-500/20 transition"
            >
              <i class="fas fa-lock text-slate-400"></i>
              Change Password
            </button>

            <div class="h-px bg-slate-600/30"></div>

            <button
              @click="askLogout"
              class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-400 hover:bg-red-500/20 transition"
            >
              <i class="fas fa-sign-out-alt"></i>
              Logout
            </button>
          </div>
        </div>
      </div>

      <!-- search -->
      <div class="p-3 border-b border-white/10 relative">
        <input
          v-model="searchBar"
          @focus="handleFocus"
          @blur="handleBlur"
          type="text"
          placeholder="Search..."
          class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-blue-500"
        />

        <i
          v-if="searchBar"
          @click="clearSearchBar"
          class="fa-solid fa-xmark absolute right-6 top-1/2 -translate-y-1/2 text-white/50 hover:text-white cursor-pointer"
        ></i>
      </div>

      <div v-if="showSearchResultArea" class="flex-1 flex overflow-y-auto">
        <div v-if="matchedUsers.length !== 0" class="flex-1">
          <div 
            v-for="user in matchedUsers" 
            @click="openConversation(user.id)"
            class="flex items-center gap-3 p-3 cursor-pointer "
          >
          <div class="w-10 h-10 rounded-full overflow-hidden flex items-center justify-center text-lg font-semibold text-white bg-gradient-to-br from-gray-700 to-gray-800 cursor-pointer">
            <img 
              v-if="user.avatar_url"
              :src="user.avatar_url" 
              class="w-full h-full object-cover"
            >
            <span v-else>
              {{ user.name?.charAt(0).toUpperCase() ?? '?' }}
            </span>
          </div>

            <div class="flex-1">
              <div class="flex justify-between text-sm">
                <h3 class="text-sm">{{ user.name }}</h3>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="flex-1 flex justify-center p-3">
          Search results will appear here
        </div>
      </div>
      
      <div v-else class="flex-1 flex overflow-y-auto">
        <div v-if="loadingConversationsList" class="flex-1 flex justify-center p-3">
          Loading...
        </div>
        
        <!-- chat list -->
        <div v-else class="flex-1">
          <div 
            v-for="conversation in conversations" 
            @click="openConversation(conversation.otherUser?.id)" 
            class="flex items-center gap-3 p-3 cursor-pointer "
            :class="[conversation.id == currentConversation?.conversation_id ? 'bg-blue-500/10 border-l-4 border-blue-500' : 'hover:bg-white/5 active:hover:bg-white/5 transition']"
          >
          
            <!-- avatar -->
            <div class="w-10 h-10 rounded-full overflow-hidden flex items-center justify-center text-lg font-semibold text-white bg-gradient-to-br from-gray-700 to-gray-800 cursor-pointer">
              <img
                v-if="conversation.otherUser?.avatar_url"
                :src="conversation.otherUser?.avatar_url"
                class="w-full h-full object-cover"
              />
              <span v-else>{{ conversation.otherUser?.name?.charAt(0).toUpperCase() ?? '?' }}</span>
            </div>

            <div class="flex-1">
              <div class="grid grid-cols-5 text-sm">
                <h3 class="text-sm truncate col-span-3">{{ conversation.otherUser.name }}</h3>
                <span 
                  v-if="conversation.last_message_date" 
                  class="text-xs text-gray-400 col-span-2 text-right"
                >
                  {{ new Date(conversation.last_message_date).toLocaleTimeString([], {
                      month: 'short',
                      day: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit',
                      hour12: true
                    }) 
                  }}
                </span>
              </div>

              <p 
                :class="conversation.unreadCount > 0 ? 'font-bold text-sm text-white': 'text-xs text-gray-400'"
                class=" line-clamp-1"
              >
                {{ conversation.last_message }}
              </p>
            </div>
          </div>

        </div>
      </div>
    </aside>

    <!-- Chat Area -->
    <main class="flex-1  flex-col p-2 lg:p-4"
      :class="isSidebarOpen ? 'hidden md:flex' : 'flex'"
    >

      <!-- when loading -->
      <div v-if="loadingConversation" class="flex-1 flex flex-col rounded-2xl overflow-hidden border border-white/10 bg-white/5 backdrop-blur items-center justify-center">
        <div class="flex flex-col items-center justify-center gap-2 text-slate-200 font-medium">
          <!-- dots -->
          <div class="flex gap-[3px]">
            <span class="dot" v-for="i in 3" :key="i"></span>
          </div>
          
          <span>Loading conversation ...</span>
        </div>
      </div>

      <!-- chat loaded -->
      <div v-else-if="currentConversation" class="flex-1 flex flex-col overflow-hidden ">
        
        <button
          type="button"
          @click="closeCurrentConversation()"
          class="md:hidden flex items-center gap-2 px-3 py-2 mb-2 w-fit pr-6 bg-white/10 border border-white/10 rounded-full text-sm text-white backdrop-blur hover:bg-white/20 transition active:scale-95"
        >
          <i class="fas fa-arrow-left text-blue-400"></i>
            Back
        </button>

        <div class="flex-1 flex flex-col rounded-2xl overflow-hidden border border-white/10 bg-white/5 backdrop-blur">
          
          <!-- header -->
          <div class="p-4 border-b border-white/10 flex justify-between items-center bg-white/5">
            <div class="flex items-center gap-3 w-full">
              <div class="w-10 h-10 rounded-full border border-white/20 overflow-hidden flex items-center justify-center text-lg font-semibold text-white bg-gradient-to-br from-blue-500 to-cyan-400 cursor-pointer">
                <img
                  v-if="currentConversation.otherUser?.avatar_url"
                  :src="currentConversation.otherUser?.avatar_url"
                  class="w-full h-full object-cover"
                />
                <span v-else>{{ currentConversation.otherUser?.name.charAt(0).toUpperCase() ?? '?' }}</span>
              </div>
              
              <div>
                <h1 class="font-semibold">{{ currentConversation.otherUser?.name }}</h1>
                <span v-if="isOnline(currentConversation.otherUser.id)" class="text-xs text-green-400">
                  ● Online
                </span>
                <span v-else class="text-xs text-gray-400">
                  ● Offline
                </span>
              </div>

              <div ref="currentConvoDropdown" class="relative ml-auto right-0">
                <button
                  @click="toggleCurrentConvoDropdown(currentConversation.conversation_id)"
                  class="flex items-center gap-2 px-3 py-2 rounded-md text-slate-400 hover:bg-blue-500/20 hover:text-blue-400 transition cursor-pointer"
                >
                  <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>

                <!-- Dropdown -->
                <div
                  v-if="isCurrentConvoDropdownOpen"
                  class="absolute z-50 right-0 mt-2 w-48 rounded-xl border border-blue-500/30 bg-slate-900/90 backdrop-blur shadow-xl overflow-hidden"
                >
                  <button
                    @click="askDelete('convo')"
                    class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-400 hover:bg-red-500/20 transition"
                  >
                    <i class="fas fa-trash"></i>
                    Delete Conversation
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- messages -->
          <div ref="messagesContainer"  @scroll="handleScroll" class="flex-1 overflow-y-auto p-4 space-y-3 hide-scrollbar">

            <div v-if="loadingMoreMessages" class="flex">
              <div class="bg-white/10 mx-auto text-sm px-4 py-1.5 rounded-full">
                Loading...
              </div>
            </div>

            <div v-for="message in currentConversation?.messages?.data">
              <!-- received -->
              <div v-if="message.user_id !== authUser?.id" class="flex items-end gap-2">
                <div class="w-7 h-7 rounded-full border border-white/20 overflow-hidden flex items-center justify-center text-sm font-semibold text-white bg-gradient-to-br from-blue-500 to-cyan-400 cursor-pointer">
                  <img
                    v-if="currentConversation.otherUser?.avatar_url"
                    :src="currentConversation.otherUser?.avatar_url"
                    class="w-full h-full object-cover"
                  />
                  <span v-else>{{ currentConversation.otherUser?.name.charAt(0).toUpperCase() ?? '?' }}</span>
                </div>
                <div class="bg-white/10 border border-white/10 px-3 py-2 rounded-2xl text-sm max-w-2/5 cursor-pointer">
                  {{ message.message }}
                </div>
              </div>

              <!-- sent -->
              <div v-else class="flex justify-end gap-4">
                <button
                  v-if="selectedMsgId === message.id"
                  @click="askDelete('message')"
                  class="my-auto px-2 py-1.5 text-xs rounded-full border border-red-400/40 bg-red-500/20 text-red-300 hover:bg-red-500/40"
                >
                  <i class="fas fa-trash"></i>
                </button>

                <div @click="openMsgAction(message.id)" class="bg-gradient-to-r from-blue-600 to-cyan-500 text-white px-3 py-2 rounded-2xl text-sm shadow-lg max-w-2/5 [overflow-wrap:anywhere] cursor-pointer">
                  {{ message.message }}
                </div>
                
              </div>
            </div>
          </div>

          <!-- message input and send button -->
          <form @submit.prevent="sendMessage" class="p-3 border-t border-white/10 flex gap-2 bg-white/5">

            <input
              ref="messageInput"
              v-model="messageFormData.message"
              type="text"
              placeholder="Type a message..."
              :disabled="isSendingMessage"
              class="flex-1 bg-white/5 border border-white/10 rounded-full px-4 py-2 text-sm outline-none focus:border-blue-500"
            />

            <button
              type="submit"
              :disabled="isSendingMessage"
              :class="[
                'px-5 rounded-full transition flex items-center justify-center gap-2',
                isSendingMessage
                  ? 'bg-gray-400 cursor-not-allowed'
                  : 'bg-gradient-to-r from-blue-600 to-blue-500 hover:-translate-y-1 text-white'
              ]"
            >
              <span
                v-if="isSendingMessage"
                class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"
              ></span>

              <span>{{ isSendingMessage ? 'Sending...' : 'Send' }}</span>
            </button>
          </form>
        </div>

      </div>

      <!-- no chat loaded -->
      <div v-else class="flex-1 flex flex-col overflow-hidden items-center justify-center">
        <div class="flex flex-col items-center justify-center gap-2 text-slate-200 font-medium">
          <div class="text-center mb-8">
            <div class="w-16 h-16 flex items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-400 shadow-lg mb-4 mx-auto">
              <i class="fas fa-comment-dots text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-100 to-blue-300 bg-clip-text text-transparent">
              ChatTayo
            </h1>
            <p class="text-indigo-300 text-sm mt-1">where conversations flow freely</p>
          </div>
        </div>
      </div>
    </main>

  </div>
</template>

<script setup>
import { onMounted, ref, nextTick, watch, computed } from 'vue'
import useUserStore from '@/stores/user';
import axiosClient from '@/axios';
import Loading from '@/components/Loading.vue';
import Toast from '@/components/Toast.vue';
import { useToast } from '@/composables/useToast';
import router from '@/router';
import AccountSettingModal from '@/components/AccountSettingModal.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import ChangePasswordModal from '@/components/ChangePasswordModal.vue';

const { toast, showToast } = useToast()

const userStore = useUserStore()
const authUser = computed(() => userStore.user )

const loading = ref(false)
const loadingConversation = ref(false)
const loadingMoreMessages = ref(false)
const loadingConversationsList = ref(false)
const isSendingMessage = ref(false)
const isSidebarOpen = ref(true)

const messagesContainer = ref(null)
const onlineUsers = ref([])
const conversations = ref([])
const currentConversation = ref()
const page = ref(1)
const lastPage = ref(null)
const messageInput = ref(null)
const messageFormData = ref({
  conversation_id: null,
  message: null
})

const showSearchResultArea = ref(false)
const searchBar = ref('')
let timeout = null
const matchedUsers = ref([])

const profileDropdown = ref()
const isProfileDropdownOpen = ref()
const toggleProfileDropdown = () => isProfileDropdownOpen.value = !isProfileDropdownOpen.value
const showAccountSettingModal = ref(false)
const showChangePasswordModal = ref(false)

const currentConvoDropdown = ref()
const isCurrentConvoDropdownOpen = ref(false)
const selectedConvoId = ref(null)
function toggleCurrentConvoDropdown (convoId){ 
  selectedConvoId.value = convoId
  isCurrentConvoDropdownOpen.value = !isCurrentConvoDropdownOpen.value 
}

const selectedMsgId = ref(null)
const showConfirmModal = ref(false)
const confirmAction = ref(null)
const confirmTitle = ref('')
const confirmMessage = ref('')

function openConfirm({ title, message, action }) {
  confirmTitle.value = title
  confirmMessage.value = message
  confirmAction.value = action
  showConfirmModal.value = true
}

function onConfirm() {
  confirmAction.value?.()
  showConfirmModal.value = false
  confirmTitle.value = ''
  confirmMessage.value = ''
  confirmAction.value = null
}

function askDelete(type = null) {
  if(!type) return

  if(type === 'message'){
    openConfirm({
      title: 'Delete Message?',
      message: 'Are you sure you want to delete this message?',
      action: confirmDeleteMessage
    })
  }
  
  if(type === 'convo'){
    openConfirm({
      title: 'Delete Conversation?',
      message: 'Are you sure you want to delete this conversation?',
      action: confirmDeleteConvo
    })
  }
  
}

function askLogout() {
  openConfirm({
    title: 'Confirm logout',
    message: 'Are you sure you want to logout?',
    action: logout
  })
}

async function confirmDeleteMessage() {
  if(loading.value) return

  loading.value = true

  try {
    const response =  await axiosClient.delete(`/api/delete-message/${selectedMsgId.value}`)
    const msg = response.data.deleted_message

    if(currentConversation.value?.conversation_id === msg.conversation_id){
      const msgIndex = currentConversation.value.messages.data.findIndex((m) => {
        return m.id === msg.id
      })
      
      if(msgIndex !== -1){
        currentConversation.value.messages.data.splice(msgIndex, 1)
      }
    }

    const convoIndex = conversations.value.findIndex((c) => {
      return c.id === msg.conversation_id
    })

    if(convoIndex !== -1){
      const convo = conversations.value[convoIndex]
      convo.last_message = msg?.new_last_message?.message
      convo.last_message_date = msg?.new_last_message?.created_at
    }

    showToast('Successfully deleted', 'Success')
  } 
  catch (error) {
    showToast('An error occured', 'Error')
  }
  loading.value = false
  showConfirmModal.value = false
}

function cancelAction() {
  showConfirmModal.value = false
  confirmTitle.value = ''
  confirmMessage.value = ''
  confirmAction.value = null
}

async function loadConversations() {
  loadingConversationsList.value = true

  try {
    const response = await axiosClient.get('/api/conversations-list')

    conversations.value = []
    Array.from(response.data.conversations_list).forEach(conversation => {
      const otherUser = conversation.users.find(u => u.id !== authUser.value.id)

      conversations.value.push({
        id: conversation.id, 
        otherUser: otherUser,
        unreadCount: conversation.unread_count,
        last_message: conversation.last_message?.message,
        last_message_date: conversation.last_message?.created_at,
      })
    })

  } 
  catch (error) {
    showToast('Failed to load conversations', 'Error')
  }
  finally{
    loadingConversationsList.value = false
  }
}

function openConversation(otherUserId){
  if(otherUserId === currentConversation.value?.otherUser.id) return
  
  lastPage.value = null
  page.value = 1
  loadingConversation.value = true
  loadMessages(otherUserId)
}

async function loadMessages(otherUserId){
  isSidebarOpen.value = false

  try {
    const response = await axiosClient.get('/api/open-conversation', {
      params: {
        "other_user_id": otherUserId,
        "page": page.value
      }
    })
    
    if(currentConversation.value && (currentConversation.value.conversation_id === response.data.conversation_id)){
      const oldHeight = messagesContainer.value.scrollHeight

      currentConversation.value.messages.data = [ ...response.data.messages.data.reverse(), ...currentConversation.value.messages.data  ]
      
      maintainScrollPosition(oldHeight)
    }
    else{
      currentConversation.value = response.data
      currentConversation.value.messages.data = response.data.messages.data.reverse()
    }

    page.value++
    lastPage.value = response.data.messages.last_page
    
    // updating form data conversation id - for sending message
    messageFormData.value.conversation_id = currentConversation.value.conversation_id   

    // update unread count to zero
    const i = conversations.value.findIndex((c) => {
      return c.id === currentConversation.value.conversation_id
    })

    if(i !== -1){
      const convo = conversations.value[i]
      convo.unreadCount = null
    }

    nextTick(() => {
      messageInput.value?.focus()
    })
  } 
  catch (error) {    
    showToast('Failed to open conversation', 'Error')
    isSidebarOpen.value = true
  }
  finally{
    if(loadingConversation.value){
      scrollToBottom()
    }

    loadingConversation.value = false
    loadingMoreMessages.value = false
  }
}

function handleScroll(e) {
  if (e.target.scrollTop === 0) {
    if (loadingConversation.value || (lastPage.value && page.value > lastPage.value)) return

    loadingMoreMessages.value = true
    loadMessages(currentConversation.value.otherUser.id)
  }
}

function closeCurrentConversation(){
  currentConversation.value = null
  messageFormData.value.conversation_id = null
  isSidebarOpen.value = true
}

async function sendMessage(){
  if (!messageFormData.value.message) return; 
  
  isSendingMessage.value = true
  
  try {
    const response = await axiosClient.post('/api/send-message', messageFormData.value)
    const msg = response.data.message_sent

    const index = Array.from(conversations.value).findIndex((c) => {
        return c.id == msg.conversation_id
      } 
    )

    if (index === -1){
      // new conversation
      conversations.value.unshift({
        id: msg.conversation_id, 
        otherUser: msg.receiver,
        last_message: msg.message,
        last_message_date: msg.created_at
      })
    }
    else{
      // existing conversation
      const convo = conversations.value[index]
      convo.last_message = msg?.message
      convo.last_message_date = msg?.created_at

      conversations.value.splice(index, 1)
      conversations.value.unshift(convo)
    }

    currentConversation.value.messages.data.push({
      id: msg.id,
      user_id: msg.user_id,
      message: msg.message,
      is_read: msg.is_read,
      created_at: msg.created_at
    })
    messageFormData.value.message = null

    scrollToBottom()
  } 
  catch (error) {
    showToast('An error occured', 'Error')
  }  
  finally{
    isSendingMessage.value = false

    nextTick(() => {
      messageInput.value?.focus()
    })
  }
}

function scrollToBottom() {
  nextTick(() => {
    const el = messagesContainer.value
    if (el) {
      el.scrollTop = el.scrollHeight
    }
  })
}

function maintainScrollPosition(oldHeight) {
  nextTick(() => {
    const msgsContainer = messagesContainer.value
    const newHeight = msgsContainer.scrollHeight

    if (msgsContainer) {
      msgsContainer.scrollTop = newHeight - oldHeight
    }
  })
}

function handleFocus() {
  showSearchResultArea.value = true
}

function handleBlur() {
  if (!searchBar.value) {
    showSearchResultArea.value = false
    matchedUsers.value = []
  }
}

function clearSearchBar(){
  searchBar.value = ''
  showSearchResultArea.value = false
  matchedUsers.value = []
}

async function fetchResults(text){
  try {
    const response = await axiosClient.get('/api/search', {
      params: { "name": text }
    })

    matchedUsers.value = response.data.matched_users
  } 
  catch (error) {
    showToast('An error occured', 'Error')
  }
}

async function logout(){
  try {
    await axiosClient.post('/api/logout')
    
    userStore.logoutUser()
    router.push({ name: 'Login' })
  } 
  catch (error) {
    showToast('Failed to logout', 'Error')
  }
}

function isOnline(userId) {
  return onlineUsers.value.some(u => u.id === userId)
}

async function updateUser(data){
  if(loading.value) return

  loading.value = true

  const formData = new FormData();
  formData.append('name', data.name)
  formData.append('email', data.email)

  // Only append avatar if it's a File object
  if (data.avatar instanceof File) {
    formData.append('avatar', data.avatar)
  }

  // Only append remove_avatar if true
  if (data.remove_avatar) {
    formData.append('remove_avatar', '1')
  }

  try {
    const response = await axiosClient.post('/api/update-account', formData)

    showToast('Successfully updated', 'Success')
    authUser.value.name = response.data.name
    authUser.value.email = response.data.email
    authUser.value.avatar_url = response.data.avatar_url
    
  } 
  catch (error) {
    showToast('An error occured', 'Error')
  }
  finally{
    loading.value = false
  }
}

async function updatePassword(data) {

  if(loading.value) return

  loading.value = true
  
  if (authUser.value.has_password && !data.oldPassword) {
    showToast('Old password required', 'Error')
    loading.value = false
    return
  }

  if (!data.newPassword) {
    showToast('New password required', 'Error')
    loading.value = false
    return
  }
  
  if (data.newPassword.length < 8) {
    showToast('Password must be at least 8 characters', 'Error')
    loading.value = false
    return
  }
  
  if (data.newPassword !== data.confirmation) {
    showToast('Passwords do not match', 'Error')
    loading.value = false
    return
  }
  
  const formData = {
    current_password: data.oldPassword,
    new_password: data.newPassword,
    new_password_confirmation: data.confirmation
  };

  try {
    const response = await axiosClient.post('/api/change-password', formData)

    if(response.status === 200) {
      showToast('Password updated successfully', 'Success')
      showChangePasswordModal.value = false  

      await userStore.fetchUser()
    }
  } 
  catch (error) {
    const message = error?.response?.data?.message || 'An error occurred'
    showToast(message, 'Error')
  }
  finally{
    loading.value = false
  }
}

function handleClickOutside(e){
  if(!profileDropdown.value?.contains(e.target)){
    isProfileDropdownOpen.value = false
  }

  if(!currentConvoDropdown.value?.contains(e.target)){
    isCurrentConvoDropdownOpen.value = false
  }
}

function openMsgAction(id){
  if(selectedMsgId.value === id){
    selectedMsgId.value = null
  }
  else{
    selectedMsgId.value = id
  }
}

async function confirmDeleteConvo(){
  if (loading.value === true) return

  loading.value = true

  try {
    const response = await axiosClient.delete(`/api/delete-conversation/${selectedConvoId.value}`)

    showToast('Deleted successfully', 'Success')
    
    await loadConversations()
    closeCurrentConversation()
  } 
  catch (error) {
    showToast('An error occurred', 'Error')
  }
  finally{
    loading.value = false
  }
}

async function markAsRead(convoId) {
  try {
    const i = conversations.value.findIndex((c) => {
      return c.id === convoId
    })
    const convo = conversations.value[i]
    convo.unreadCount = null
    
    await axiosClient.post('/api/mark-as-read', {
      conversation_id: convoId
    })
  } catch (error) {
    showToast('An error occured', 'Error')
  } 
}

watch(searchBar, (text) => {
  clearTimeout(timeout)
  
  timeout = setTimeout(() => {
    if (text.trim()){
      fetchResults(text)
    }
  }, 500)
})

onMounted(() => {
  loadConversations()  

  window.Echo.private(`user.${authUser.value.id}`)
    .listen('MessageSent', (e) => {
      const msg = e.message

      const index = conversations.value.findIndex((c) => {
        return c.id === msg.conversation_id
      })
      
      if(index == -1){
        // new conversation - first message received
        conversations.value.unshift({
          id: msg.conversation_id, 
          otherUser: msg.sender,
          unreadCount: 1,
          last_message: msg.message,
          last_message_date: msg.created_at
        })
      }
      else{
        // existing conversation
        const convo = conversations.value[index]
        convo.unreadCount = (convo.unreadCount + 1) 
        convo.last_message = msg?.message
        convo.last_message_date = msg?.created_at

        conversations.value.splice(index, 1)
        conversations.value.unshift(convo)
      }

      if(currentConversation.value?.conversation_id === msg.conversation_id){
        currentConversation.value.messages.data.push({
          id: msg.id,
          user_id: msg.user_id,
          message: msg.message,
          is_read: msg.is_read,
          created_at: msg.created_at
        })

        scrollToBottom()
        markAsRead(msg.conversation_id)
      }
    })
    .listen('MessageDeleted', (e) => {
      const msg = e.message

      if(currentConversation.value?.conversation_id === msg.conversation_id){
        const msgIndex = currentConversation.value.messages.data.findIndex((m) => {
          return m.id === msg.id
        })
        
        if(msgIndex !== -1){
          currentConversation.value.messages.data.splice(msgIndex, 1)
        }
      }

      const convoIndex = conversations.value.findIndex((c) => {
        return c.id === msg.conversation_id
      })

      if(convoIndex !== -1){
        const convo = conversations.value[convoIndex]
        convo.last_message = msg?.new_last_message?.message
        convo.last_message_date = msg?.new_last_message?.created_at
      }
    })

  window.Echo.join('online')
    .here(users => {
      onlineUsers.value = users
    })
    .joining(user => {
      onlineUsers.value.push(user)
    })
    .leaving(user => {
      onlineUsers.value = onlineUsers.value.filter(u => u.id !== user.id)
    })  
  
    document.addEventListener('click', handleClickOutside)
})

</script>

<style scoped>
/* dots */
@keyframes dotFade {
  0%, 100% { opacity: 0.3; transform: translateY(0); }
  50% { opacity: 1; transform: translateY(-3px); background: #60a5fa; }
}

.dot {
  width: 10px;
  height: 10px;
  background: #94a3b8;
  border-radius: 9999px;
  animation: dotFade 1.2s infinite;
}

.dot:nth-child(2) { animation-delay: 0.2s; }
.dot:nth-child(3) { animation-delay: 0.4s; }
</style>