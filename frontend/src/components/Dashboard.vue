<template>
  <div class="dashboard">
    <h1>Dashboard</h1>
    <p class="welcome-text">Welcome to the Stock Management System</p>
    
    <div class="dashboard-grid">
      <div class="dashboard-card" @click="navigateTo('/stocks')">
        <div class="card-icon">📋</div>
        <h3>Stock List</h3>
        <p>View and manage all stock entries with search and pagination</p>
      </div>
      
      <div class="dashboard-card" @click="navigateTo('/stocks/bulk')">
        <div class="card-icon">📦</div>
        <h3>Bulk Stock Entry</h3>
        <p>Add multiple stock entries at once using the grid interface</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const navigateTo = (path) => {
  router.push(path)
}
</script>

<style scoped>
.dashboard {
  max-width: 1200px;
  margin: 0 auto;
}

.dashboard h1 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
  font-size: 2.5rem;
}

.welcome-text {
  color: #7f8c8d;
  font-size: 1.2rem;
  margin-bottom: 3rem;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.dashboard-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.dashboard-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  border-color: #3498db;
}

.card-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.dashboard-card h3 {
  color: #2c3e50;
  margin-bottom: 1rem;
  font-size: 1.5rem;
}

.dashboard-card p {
  color: #7f8c8d;
  line-height: 1.6;
}
</style>
