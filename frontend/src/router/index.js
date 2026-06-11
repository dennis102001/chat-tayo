import Login from '@/pages/Login.vue'
import MainChat from '@/pages/MainChat.vue'
import Register from '@/pages/Register.vue'
import AuthCallback from '@/pages/AuthCallback.vue'
import { createRouter, createWebHistory } from 'vue-router'
import useUserStore from '@/stores/user'
import ResetPassword from '@/pages/ResetPassword.vue'
import NotFound from '@/pages/NotFound.vue'

const routes = [
  {
    path: '/',
    name: 'MainChat',
    component: MainChat,
    meta: { requiresAuth : true}
  },
  {
    path: '/login',
    name: 'Login', 
    component: Login
  },
  {
    path: '/auth/callback',
    name: 'AuthCallback', 
    component: AuthCallback
  },
  {
    path: '/register',
    name: 'Register', 
    component: Register
  },
  {
    path: '/reset-password',
    name: 'ResetPassword', 
    component: ResetPassword
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFound
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach(async (to, from) => {
  const userStore = useUserStore()

  if(to.meta.requiresAuth){

    if(!userStore.user){
      try {
        await userStore.fetchUser()
      } catch {
        return { name: 'Login' }
      }
    }
    
    if(to.meta.requiresAdmin && userStore.user.role !== 'admin'){
        return next({ name: 'NotFound' });
    }
  }
  
  return true;
})

export default router
