<script setup>
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
import { computed } from 'vue'

const store = useStore()
const router = useRouter()

const isAuthenticated = computed(() => store.state.isAuthenticated)
const user = computed(() => store.state.user)

const handleLogout = async () => {
  await store.dispatch('logout')
  router.push('/login')
}
</script>

<template>
  <div id="app">
    <!-- Navigation -->
    <nav v-if="isAuthenticated" class="navbar">
      <div class="nav-brand">Stock Management System</div>
      <div class="nav-menu">
        <router-link to="/dashboard" class="nav-link">Dashboard</router-link>
        <router-link to="/stocks" class="nav-link">Stock List</router-link>
        <router-link to="/stocks/bulk" class="nav-link">Bulk Entry</router-link>
        <div class="nav-user">
          <span>Welcome, {{ user?.name }}</span>
          <button @click="handleLogout" class="logout-btn">Logout</button>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<style scoped>
#app {
  min-height: 100vh;
  font-family: Arial, sans-serif;
}

.navbar {
  background: #2c3e50;
  color: white;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.nav-brand {
  font-size: 1.5rem;
  font-weight: bold;
}

.nav-menu {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.nav-link {
  color: white;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.nav-link:hover {
  background-color: #34495e;
}

.nav-link.router-link-active {
  background-color: #3498db;
}

.nav-user {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logout-btn {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.logout-btn:hover {
  background: #c0392b;
}

.main-content {
  padding: 2rem;
}
</style>
