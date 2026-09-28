import { defineStore } from 'pinia';
import axiosClient from '../axios';
import router from '@/router';

const useUserStore = defineStore('user', {
  state: () => ({
    user: null,
    isAutoLoggingOut: false
  }),
  actions: {
    fetchUser () {
      return axiosClient.get('/api/user')
        .then(({data}) => {
          this.user = data.user;
        })
    },
    async logoutUser (auto = false) {
      this.isAutoLoggingOut = auto

      localStorage.removeItem('token')

      if (window.Echo) {
        window.Echo.disconnect()
        window.Echo = null
      }

      this.user = null
      await router.replace({name: 'Login'})
      this.isAutoLoggingOut = false
    }
  }
})

export default useUserStore;