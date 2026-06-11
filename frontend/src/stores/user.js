import { defineStore } from 'pinia';
import axiosClient from '../axios';
import router from '@/router';

const useUserStore = defineStore('user', {
  state: () => ({
    user: null,
  }),
  actions: {
    fetchUser () {
      return axiosClient.get('/api/user')
        .then(({data}) => {
          this.user = data.user;
        })
    },
    logoutUser () {
      localStorage.removeItem('token')

      if (window.Echo) {
        window.Echo.disconnect()
        window.Echo = null
      }

      this.user = null
      router.push({name: 'Login'})
    }
  }
})

export default useUserStore;