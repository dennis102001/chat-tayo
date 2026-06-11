import axios from 'axios';
import useUserStore from './stores/user';

const axiosClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL
});

axiosClient.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

axiosClient.interceptors.response.use(
  (response) => {
    return response;
  }, 
  (error) => {
    if (error.response && error.response.status === 401) {
      const userStore = useUserStore()
      userStore.logoutUser()
    }

    throw error
  }
)

export default axiosClient;