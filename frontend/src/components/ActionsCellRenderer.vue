<template>
  <button @click="onDeleteClick" class="delete-row-btn">Delete</button>
</template>

<script setup>
// Define props that AG Grid will pass to this component
const props = defineProps({
  params: Object,
});

// The handler for the button click
const onDeleteClick = () => {
  // Call the onDelete function passed from the parent grid component
  props.params.onDelete(props.params.data);
};
</script>

<style scoped>
/* You can move the .delete-row-btn styles here or keep them global */
.delete-row-btn {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.875rem;
  transition: background-color 0.3s;
}

.delete-row-btn:hover {
  background: #c0392b;
}
</style>