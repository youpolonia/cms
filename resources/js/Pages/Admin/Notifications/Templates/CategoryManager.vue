<template>
  <div class="category-manager">
    <div class="manager-header">
      <h1>Template Categories</h1>
      <button class="btn-add" @click="showAddModal = true">
        + Add Category
      </button>
    </div>

    <div class="categories-list">
      <div class="category-item" v-for="category in categories" :key="category.id">
        <div class="category-info">
          <span class="category-name">{{ category.name }}</span>
          <span class="template-count">{{ category.template_count }} templates</span>
        </div>
        <div class="category-actions">
          <button class="btn-edit" @click="editCategory(category)">
            Edit
          </button>
          <button 
            class="btn-delete" 
            @click="confirmDelete(category)"
            v-if="category.template_count === 0"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Category Modal -->
    <modal v-model="showAddModal" @close="showAddModal = false">
      <template #header>
        <h2>{{ editingCategory ? 'Edit' : 'Add' }} Category</h2>
      </template>
      <template #body>
        <div class="form-group">
          <label>Category Name *</label>
          <input 
            v-model="categoryForm.name" 
            type="text" 
            required
            placeholder="Enter category name"
          >
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea 
            v-model="categoryForm.description" 
            placeholder="Enter category description"
          />
        </div>
      </template>
      <template #footer>
        <button class="btn-save" @click="saveCategory">
          Save
        </button>
        <button class="btn-cancel" @click="showAddModal = false">
          Cancel
        </button>
      </template>
    </modal>

    <!-- Delete Confirmation Modal -->
    <modal v-model="showDeleteModal" @close="showDeleteModal = false">
      <template #header>
        <h2>Confirm Deletion</h2>
      </template>
      <template #body>
        <p>Are you sure you want to delete this category?</p>
      </template>
      <template #footer>
        <button class="btn-confirm" @click="deleteCategory">
          Delete
        </button>
        <button class="btn-cancel" @click="showDeleteModal = false">
          Cancel
        </button>
      </template>
    </modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';

const categories = ref([]);
const showAddModal = ref(false);
const showDeleteModal = ref(false);
const editingCategory = ref(null);
const categoryToDelete = ref(null);

const categoryForm = ref({
  name: '',
  description: ''
});

const loadCategories = async () => {
  try {
    const response = await axios.get('/api/notification-template-categories');
    categories.value = response.data;
  } catch (error) {
    console.error('Error loading categories:', error);
  }
};

const editCategory = (category) => {
  editingCategory.value = category;
  categoryForm.value = {
    name: category.name,
    description: category.description
  };
  showAddModal.value = true;
};

const saveCategory = async () => {
  try {
    if (editingCategory.value) {
      await axios.put(
        `/api/notification-template-categories/${editingCategory.value.id}`,
        categoryForm.value
      );
    } else {
      await axios.post(
        '/api/notification-template-categories',
        categoryForm.value
      );
    }
    showAddModal.value = false;
    loadCategories();
    resetForm();
  } catch (error) {
    console.error('Error saving category:', error);
  }
};

const confirmDelete = (category) => {
  categoryToDelete.value = category;
  showDeleteModal.value = true;
};

const deleteCategory = async () => {
  try {
    await axios.delete(
      `/api/notification-template-categories/${categoryToDelete.value.id}`
    );
    showDeleteModal.value = false;
    loadCategories();
  } catch (error) {
    console.error('Error deleting category:', error);
  }
};

const resetForm = () => {
  categoryForm.value = {
    name: '',
    description: ''
  };
  editingCategory.value = null;
};

onMounted(() => {
  loadCategories();
});
</script>

<style scoped>
.category-manager {
  padding: 20px;
}

.manager-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.btn-add {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.categories-list {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.category-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #eee;
}

.category-item:last-child {
  border-bottom: none;
}

.category-info {
  display: flex;
  flex-direction: column;
}

.category-name {
  font-weight: 500;
  margin-bottom: 5px;
}

.template-count {
  font-size: 14px;
  color: #666;
}

.category-actions {
  display: flex;
  gap: 10px;
}

.btn-edit {
  background: #f5f5f5;
  color: #333;
  border: none;
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.btn-delete {
  background: #fef2f2;
  color: #ef4444;
  border: none;
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
}

.form-group input[type="text"],
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.form-group textarea {
  min-height: 100px;
}

.btn-save {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.btn-confirm {
  background: #ef4444;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.btn-cancel {
  background: #f5f5f5;
  color: #333;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  margin-left: 10px;
}
</style>