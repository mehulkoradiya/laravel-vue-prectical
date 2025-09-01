<template>
  <div class="bulk-stock-entry">
    <div class="header">
      <h1>Bulk Stock Entry</h1>
      <div class="actions">
        <button @click="addNewRow" class="btn btn-primary">Add New Record</button>
        <button @click="saveAll" :disabled="loading" class="btn btn-success">
          {{ loading ? 'Saving...' : 'Save All Records' }}
        </button>
      </div>
    </div>
    
    <div class="grid-container">
      <ag-grid-vue
        ref="agGrid"
        class="ag-theme-alpine"
        :columnDefs="columnDefs"
        :rowData="rowData"
        :frameworkComponents="{ DatePicker }"
        :defaultColDef="defaultColDef"
        :gridOptions="gridOptions"
        @grid-ready="onGridReady"
      />
    </div>
    
    <div v-if="message" :class="['message', messageType]">
      {{ message }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue'
import { AllCommunityModule, ModuleRegistry } from 'ag-grid-community';
ModuleRegistry.registerModules([AllCommunityModule]);
import { AgGridVue } from 'ag-grid-vue3'

import axios from 'axios'
import Flatpickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'



const agGrid = ref(null)
const rowData = ref([])
const stores = ref([])
const loading = ref(false)
const message = ref('')
const messageType = ref('')
const gridReady = ref(false)
const nextRowId = ref(1)

const defaultColDef = {
  editable: true,
  sortable: true,
  filter: true,
  resizable: true,
  minWidth: 100,
  flex: 1
}

const DatePicker = {
  template: `
    <flat-pickr
      v-model="date"
      :config="config"
      @on-close="onDateChange"
      class="ag-input-field-input"
    />
  `,
  props: ["value"],
  components: { Flatpickr },
  data() {
    return {
      date: this.value,
      config: {
        dateFormat: "Y-m-d",
        maxDate: new Date(),
      },
    }
  },
  methods: {
    onDateChange(selectedDates, dateStr) {
      this.$emit("update:value", dateStr)
    },
  },
}

const columnDefs = computed(() => [
  { headerName: "Stock No", field: "stock_no", editable: false, width: 120 },
  { headerName: "Item Code", field: "item_code", editable: true },
  { headerName: "Item Name", field: "item_name", editable: true },
  { headerName: "Quantity", field: "quantity", editable: true, type: "numericColumn" },
  { headerName: "Location", field: "location", editable: true },
  {
    headerName: "Store Name",
    field: "store_id",
    editable: true,
    cellEditor: "agSelectCellEditor",
    cellEditorParams: () => {
      return {
        values: stores.value.map(store => store.name) // ✅ Pass names, not IDs
      };
    },
    valueGetter: (params) => {
      const store = stores.value.find(s => s.id === params.data.store_id);
      return store ? store.name : '';
    },
    valueSetter: (params) => {
      const selectedStore = stores.value.find(s => s.name === params.newValue);
      if (selectedStore) {
        params.data.store_id = selectedStore.id; // ✅ Save ID internally
        return true;
      }
      return false;
    }
  },
  {
    headerName: "In-Stock Date",
    field: "in_stock_date",
    editable: true,
    cellEditor: "DatePicker", // ✅ Custom date picker
    valueFormatter: params => {
      if (params.value) {
        return new Date(params.value).toLocaleDateString()
      }
      return ''
    },
  },
  {
    headerName: "Actions",
    width: 120,
    editable: false,
    cellRenderer: params => {
      return `<button class="delete-row-btn" onclick="deleteRow(${params.rowIndex})">Delete</button>`
    },
  },
])


const gridOptions = {
  rowSelection: {
    mode: 'multiRow',            // ✅ New syntax
    enableClickSelection: false, // ✅ replaces suppressRowClickSelection
  },
  pagination: true,
  paginationPageSize: 20,
  paginationPageSizeSelector: [10, 20, 50, 100],
  defaultColDef: {
    sortable: true,
    filter: true,
    resizable: true,
    flex: 1,
  },
  columnDefs: [
    { headerName: "ID", field: "id", width: 80 },
    { headerName: "Name", field: "name" },
    { headerName: "Category", field: "category" },
    { headerName: "Price", field: "price" },
    {
      headerName: "Actions",
      field: "actions",
      cellRenderer: (params) => {
        return `<button class="btn btn-sm btn-primary">Edit</button>
                <button class="btn btn-sm btn-danger">Delete</button>`;
      },
    },
  ],
  onGridReady: async (params) => {
    const token = localStorage.getItem("auth_token");

    try {
      const response = await axios.get("/api/stocks", {
        headers: { Authorization: `Bearer ${token}` },
      });

      params.api.setRowData(response.data.data); // ✅ Set API data to grid
    } catch (error) {
      console.error("Error fetching data:", error);
    }
  },
};


const onGridReady = (params) => {
  gridReady.value = true
  params.api.sizeColumnsToFit()
  
  // Add global delete function
  window.deleteRow = (rowIndex) => {
    if (confirm('Are you sure you want to delete this row?')) {
      deleteRowByIndex(rowIndex)
    }
  }
  
  // Add initial row after grid is ready
  nextTick(() => {
    addNewRow()
  })
}

const addNewRow = () => {
  if (!gridReady.value || !agGrid.value || !agGrid.value.api) {
    return
  }
  
  const newRow = {
    id: nextRowId.value++,
    stock_no: '',
    item_code: '',
    item_name: '',
    quantity: 1,
    location: '',
    store_id: stores.value.length > 0 ? stores.value[0].id : null,
    in_stock_date: new Date().toISOString().split('T')[0]
  }
  
  // Use AG Grid's API to add the row
  agGrid.value.api.applyTransaction({ add: [newRow] })
}

const deleteRowByIndex = (rowIndex) => {
  if (!agGrid.value || !agGrid.value.api) {
    return
  }
  
  const rowNode = agGrid.value.api.getDisplayedRowAtIndex(rowIndex)
  if (rowNode) {
    agGrid.value.api.applyTransaction({ remove: [rowNode.data] })
  }
}

const saveAll = async () => {
  if (!agGrid.value || !agGrid.value.api) {
    showMessage('Grid not ready', 'error')
    return
  }
  
  // Get all row data from the grid
  const allRows = []
  agGrid.value.api.forEachNode(node => {
    if (node.data) {
      allRows.push(node.data)
    }
  })
  
  if (allRows.length === 0) {
    showMessage('No data to save', 'error')
    return
  }
  
  // Validate data
  const validRows = allRows.filter(row => 
    row.item_code && row.item_name && row.quantity && row.location && row.store_id && row.in_stock_date
  )
  
  if (validRows.length !== allRows.length) {
    showMessage('Please fill in all required fields', 'error')
    return
  }
  
  loading.value = true
  
  try {
    const response = await axios.post('/api/stocks/bulk', {
      stocks: validRows.map(row => ({
        item_code: row.item_code,
        item_name: row.item_name,
        quantity: row.quantity,
        location: row.location,
        store_id: row.store_id,
        in_stock_date: row.in_stock_date
      }))
    })
    
    if (response.data.success) {
      showMessage(response.data.message, 'success')

      if (agGrid.value && agGrid.value.api) {
        agGrid.value.api.applyTransaction({ remove: agGrid.value.api.getRenderedNodes().map(node => node.data) })
      }

      nextRowId.value = 1
    } else {
      showMessage('Failed to save stocks', 'error')
    }
  } catch (error) {
    console.error('Error saving stocks:', error)
    showMessage('Error saving stocks: ' + (error.response?.data?.message || error.message), 'error')
  } finally {
    loading.value = false
  }
}

const showMessage = (msg, type) => {
  message.value = msg
  messageType.value = type
  setTimeout(() => {
    message.value = ''
    messageType.value = ''
  }, 5000)
}

const fetchStores = async () => {
  try {
    const response = await axios.get('/api/stores')
    if (response.data.success) {
      stores.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching stores:', error)
  }
}

onMounted(async () => {
  await fetchStores();
  await nextTick();
  addNewRow();
})
</script>

<style scoped>
.bulk-stock-entry {
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

.actions {
  display: flex;
  gap: 1rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s;
  font-weight: 500;
}

.btn-primary {
  background: #3498db;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #2980b9;
}

.btn-success {
  background: #27ae60;
  color: white;
}

.btn-success:hover:not(:disabled) {
  background: #229954;
}

.btn:disabled {
  background: #bdc3c7;
  cursor: not-allowed;
}

.grid-container {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  margin-bottom: 2rem;
}

.ag-theme-alpine {
  height: 600px;
  width: 100%;
}

.message {
  padding: 1rem;
  border-radius: 6px;
  margin-top: 1rem;
  font-weight: 500;
}

.message.success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.message.error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

:deep(.delete-row-btn) {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.875rem;
  transition: background-color 0.3s;
}

:deep(.delete-row-btn:hover) {
  background: #c0392b;
}

@media (max-width: 768px) {
  .header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .actions {
    justify-content: center;
  }
  
  .ag-theme-alpine {
    height: 400px;
  }
}
</style>
