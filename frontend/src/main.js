import { createApp } from 'vue'
import { createStore } from 'vuex'
import { createRouter, createWebHistory } from 'vue-router'
import axios from 'axios'
import './style.css'
import App from './App.vue'

// Axios configuration
axios.defaults.baseURL = import.meta.env.VITE_API_URL || "http://localhost:8000";

axios.interceptors.request.use((config) => {
  const token = localStorage.getItem("auth_token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Vuex store
const store = createStore({
  state: {
    user: null,
    token: localStorage.getItem('auth_token') || null,
    isAuthenticated: !!localStorage.getItem('auth_token')
  },
  mutations: {
    setAuth(state, { user, token }) {
      state.user = user
      state.token = token
      state.isAuthenticated = true
      localStorage.setItem('auth_token', token)
    },
    clearAuth(state) {
      state.user = null
      state.token = null
      state.isAuthenticated = false
      localStorage.removeItem('auth_token')
    }
  },
  actions: {
    async login({ commit }, credentials) {
      try {
        const response = await axios.post('/api/login', credentials)
        if (response.data.success) {
          commit('setAuth', {
            user: response.data.user,
            token: response.data.token
          })
          return { success: true }
        }
      } catch (error) {
        return { success: false, message: error.response?.data?.message || 'Login failed' }
      }
    },
    async logout({ commit }) {
      try {
        await axios.post('/api/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        commit('clearAuth')
      }
    }
  }
})

// Vue Router
const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: () => import('./components/Login.vue') },
  { 
    path: '/dashboard', 
    component: () => import('./components/Dashboard.vue'),
    meta: { requiresAuth: true }
  },
  { 
    path: '/stocks', 
    component: () => import('./components/StockList.vue'),
    meta: { requiresAuth: true }
  },
  { 
    path: '/stocks/bulk', 
    component: () => import('./components/BulkStockEntry.vue'),
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guard
router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth && !store.state.isAuthenticated) {
    next('/login')
  } else if (to.path === '/login' && store.state.isAuthenticated) {
    next('/dashboard')
  } else {
    next()
  }
})

// Create and mount app
const app = createApp(App)
app.use(store)
app.use(router)
app.mount('#app')
