import axios from 'axios'
import type { AxiosInstance } from 'axios'
import type { App } from 'vue'

export const api: AxiosInstance = axios.create({
  baseURL: '/',
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

export default {
  install(app: App) {
    app.config.globalProperties.$axios = api
  }
}
