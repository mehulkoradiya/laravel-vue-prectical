<template>
  <div class="stock-list">
    <div class="header">
      <h1>Stock List</h1>
      <div class="search-container">
        <input
          v-model="searchTerm"
          @input="handleSearch"
          type="text"
          placeholder="Search by item code, name, or location..."
          class="search-input"
        />
      </div>
    </div>

    <!-- Loading Spinner -->
    <div v-if="loading" class="loading-overlay">
      <div class="spinner"></div>
      <p>Loading data...</p>
    </div>

    <!-- Table Container -->
    <div id="stock-table" class="table-container"></div>

    <!-- Empty Data Message -->
    <div v-if="!loading && isEmpty" class="empty-message">
      <p>No stock records found.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import Tabulator from "tabulator-tables"; // Default import for v4
import "tabulator-tables/dist/css/tabulator.min.css";
import axios from "axios";

const searchTerm = ref("");
const table = ref(null);
const loading = ref(false);
const isEmpty = ref(false);
let searchTimeout = null;


// Debounced Search
const handleSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);

  searchTimeout = setTimeout(() => {
    if (table.value) {
      table.value.setData();
    }
  }, 500);
};

// Fetch Data (Server-Side Pagination)
const fetchData = async (url, config, callback) => {
  console.log('🔍 fetchData called with:', { url, config }); // Debug log
  
  try {
    loading.value = true;
    isEmpty.value = false;

    const params = new URLSearchParams();

    // Handle different config formats for different Tabulator versions
    if (config.page) params.append("page", config.page);
    if (config.size) params.append("per_page", config.size);
    if (config.sorters && config.sorters.length > 0) {
      params.append("sort", config.sorters[0].field);
      params.append("order", config.sorters[0].dir);
    }
    if (searchTerm.value) params.append("search", searchTerm.value);

    const apiUrl = `/api/stocks?${params.toString()}`;
    console.log('🌐 Making API request to:', apiUrl); // Debug log

    const response = await axios.get(apiUrl);
    console.log('📦 API Response:', response.data); // Debug log

    if (response.data.success && response.data.data.length > 0) {
      callback(response.data.data, response.data.pagination.total);
      isEmpty.value = false;
    } else {
      callback([], 0);
      isEmpty.value = true;
    }
  } catch (error) {
    console.error("Error fetching data:", error);
    callback([], 0);
    isEmpty.value = true;
  } finally {
    loading.value = false;
  }
};

// 🗑️ Delete Stock
const deleteStock = async (id) => {
  if (!confirm("Are you sure you want to delete this stock entry?")) return;

  try {
    loading.value = true;
    const response = await axios.delete(`/api/stocks/${id}`);
    if (response.data.success) {
      table.value.setData();
    } else {
      alert("Failed to delete stock entry");
    }
  } catch (error) {
    console.error("Error deleting stock:", error);
    alert("Error deleting stock entry");
  } finally {
    loading.value = false;
  }
};

// Initialize Tabulator
onMounted(async () => {
  // Check authentication first
  const token = localStorage.getItem('auth_token');
  console.log('🔐 Auth token:', token ? 'Present' : 'Missing');
  
  if (!token) {
    console.error('No authentication token found');
    alert('Please login first');
    return;
  }

  const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000';

  table.value = new Tabulator("#stock-table", {
    height: "600px",
    layout: "fitColumns",
    pagination: "remote",
    paginationSize: 15,
    ajaxURL: `${API_BASE}/api/stocks`,
    ajaxParams: function() {
      const params = {};
      if (searchTerm.value) params.search = searchTerm.value;
      return params;
    },
    
    ajaxRequestFunc: async (url, config, params) => {
      const token = localStorage.getItem("auth_token");

      // Tabulator automatically sends pagination & sorting info in `params`
      const queryParams = {
        page: params.page || 1,
        per_page: params.size || 15,
        sort: params.sorters?.[0]?.field || "id",
        order: params.sorters?.[0]?.dir || "desc",
        search: searchTerm.value || ""
      };

      // Build final URL with query string
      const queryString = new URLSearchParams(queryParams).toString();
      const finalUrl = `${url}?${queryString}`;

      console.log("📡 Fetching:", finalUrl);

      const response = await fetch(finalUrl, {
        method: "GET",
        headers: {
          "Accept": "application/json",
          "Content-Type": "application/json",
          ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
      });

      const result = await response.json();

      // ✅ Tabulator expects an object like this
      return {
        data: result.data,
        last_page: result.pagination.last_page,
        total: result.pagination.total,
      };
    },
    
    placeholder: "⚠️ No stock data available",
    columns: [
      { title: "Stock No", field: "stock_no", width: 120 },
      { title: "Item Code", field: "item_code", width: 120 },
      { title: "Item Name", field: "item_name", width: 200 },
      { title: "Quantity", field: "quantity", width: 100, hozAlign: "center" },
      { title: "Location", field: "location", width: 150 },
      { title: "Store", field: "store.name", width: 150 },
      {
        title: "In-Stock Date",
        field: "in_stock_date",
        width: 120,
        formatter: function(cell) {
          const value = cell.getValue();
          return value ? new Date(value).toLocaleDateString("en-GB") : "";
        }
      },
      {
        title: "Status",
        field: "status",
        width: 100,
        formatter: function(cell) {
          const status = cell.getValue();
          const color = status === "in_stock" ? "#27ae60" : "#f39c12";
          return `<span style="color:${color};font-weight:bold;">${status
            .replace("_", " ")
            .toUpperCase()}</span>`;
        }
      },
      {
        title: "Actions",
        width: 120,
        hozAlign: "center",
        formatter: function(cell) {
          const row = cell.getRow();
          const id = row.getData().id;
          return `<button class="delete-btn" onclick="deleteStock(${id})">Delete</button>`;
        }
      }
    ]
  });

  // Add global delete function
  window.deleteStock = deleteStock;

  // Manually trigger data load
  console.log('Manually triggering data load...');
  table.value.setData();
});


// 🔹 Cleanup on Unmount
onUnmounted(() => {
  if (table.value) {
    table.value.destroy();
  }
  // Remove global function
  if (window.deleteStock) {
    delete window.deleteStock;
  }
});
</script>

<style scoped>
.stock-list {
  max-width: 1400px;
  margin: 0 auto;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.header h1 {
  color: #2c3e50;
  margin: 0;
}

.search-container {
  display: flex;
  gap: 1rem;
}

.search-input {
  padding: 0.75rem;
  border: 2px solid #e1e8ed;
  border-radius: 6px;
  font-size: 1rem;
  min-width: 300px;
  transition: border-color 0.3s;
}

.search-input:focus {
  outline: none;
  border-color: #3498db;
}

.table-container {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

:deep(.tabulator) {
  font-family: Arial, sans-serif;
}

:deep(.tabulator-header) {
  background: #f8f9fa;
  border-bottom: 2px solid #e9ecef;
}

:deep(.tabulator-row) {
  border-bottom: 1px solid #e9ecef;
}

:deep(.tabulator-row:hover) {
  background: #f8f9fa;
}

:deep(.delete-btn) {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.875rem;
  transition: background-color 0.3s;
}

:deep(.delete-btn:hover) {
  background: #c0392b;
}

@media (max-width: 768px) {
  .header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-input {
    min-width: auto;
    width: 100%;
  }
}
</style>
