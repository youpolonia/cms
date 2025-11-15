<template>
  <div class="schedule-list">
    <div class="list-header">
      <h2>Schedules</h2>
      <button @click="showEditor">Create New Schedule</button>
    </div>

    <div class="filter-controls">
      <div class="filter-group">
        <label>Filter by:</label>
        <select v-model="filter.status">
          <option value="all">All Schedules</option>
          <option value="active">Active</option>
          <option value="upcoming">Upcoming</option>
          <option value="completed">Completed</option>
        </select>
      </div>

      <div class="filter-group">
        <label>Sort by:</label>
        <select v-model="sort.field">
          <option value="startDate">Start Date</option>
          <option value="title">Title</option>
        </select>
        <select v-model="sort.order">
          <option value="asc">Ascending</option>
          <option value="desc">Descending</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="loading">Loading schedules...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else-if="filteredSchedules.length === 0" class="empty">
      No schedules found
    </div>
    <ul v-else class="schedule-items">
      <li v-for="schedule in filteredSchedules" :key="schedule.id" class="schedule-item">
        <div class="schedule-info">
          <h3>{{ schedule.title }}</h3>
          <p>{{ schedule.description }}</p>
          <div class="schedule-dates">
            <span>{{ formatDate(schedule.startDate) }}</span>
            <span>to</span>
            <span>{{ formatDate(schedule.endDate) }}</span>
          </div>
          <div class="schedule-status" :class="getStatusClass(schedule)">
            {{ getStatusText(schedule) }}
          </div>
        </div>
        <div class="schedule-actions">
          <button @click="editSchedule(schedule)">Edit</button>
          <button @click="confirmDelete(schedule)">Delete</button>
        </div>
      </li>
    </ul>

    <div v-if="showPagination" class="pagination">
      <button 
        @click="prevPage" 
        :disabled="currentPage === 1"
      >
        Previous
      </button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button 
        @click="nextPage" 
        :disabled="currentPage === totalPages"
      >
        Next
      </button>
    </div>

    <ScheduleEditor
      v-if="showEditorModal"
      :schedule="selectedSchedule"
      @saved="handleScheduleSaved"
      @cancel="hideEditor"
    />

    <div v-if="showDeleteConfirm" class="modal-overlay">
      <div class="modal">
        <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete "{{ scheduleToDelete.title }}"?</p>
        <div class="modal-actions">
          <button @click="cancelDelete">Cancel</button>
          <button @click="deleteSchedule" class="danger">Delete</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ScheduleEditor from './ScheduleEditor.vue'

export default {
  components: {
    ScheduleEditor
  },
  data() {
    return {
      schedules: [],
      loading: true,
      error: null,
      filter: {
        status: 'all'
      },
      sort: {
        field: 'startDate',
        order: 'asc'
      },
      currentPage: 1,
      itemsPerPage: 10,
      showEditorModal: false,
      selectedSchedule: null,
      showDeleteConfirm: false,
      scheduleToDelete: null
    }
  },
  computed: {
    filteredSchedules() {
      let filtered = [...this.schedules]

      // Apply status filter
      if (this.filter.status !== 'all') {
        const now = new Date()
        filtered = filtered.filter(schedule => {
          const start = new Date(schedule.startDate)
          const end = new Date(schedule.endDate)

          switch(this.filter.status) {
            case 'active':
              return start <= now && end >= now
            case 'upcoming':
              return start > now
            case 'completed':
              return end < now
            default:
              return true
          }
        })
      }

      // Apply sorting
      filtered.sort((a, b) => {
        const fieldA = a[this.sort.field]
        const fieldB = b[this.sort.field]

        if (fieldA < fieldB) {
          return this.sort.order === 'asc' ? -1 : 1
        }
        if (fieldA > fieldB) {
          return this.sort.order === 'asc' ? 1 : -1
        }
        return 0
      })

      // Apply pagination
      const start = (this.currentPage - 1) * this.itemsPerPage
      const end = start + this.itemsPerPage
      return filtered.slice(start, end)
    },
    totalPages() {
      return Math.ceil(this.schedules.length / this.itemsPerPage)
    },
    showPagination() {
      return this.schedules.length > this.itemsPerPage
    }
  },
  created() {
    this.fetchSchedules()
  },
  methods: {
    async fetchSchedules() {
      this.loading = true
      this.error = null
      
      try {
        const response = await fetch('/api/scheduling')
        if (!response.ok) throw new Error('Failed to fetch schedules')
        this.schedules = await response.json()
      } catch (err) {
        this.error = err.message
      } finally {
        this.loading = false
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleString()
    },
    getStatusClass(schedule) {
      const now = new Date()
      const start = new Date(schedule.startDate)
      const end = new Date(schedule.endDate)

      if (start > now) return 'upcoming'
      if (end < now) return 'completed'
      return 'active'
    },
    getStatusText(schedule) {
      const now = new Date()
      const start = new Date(schedule.startDate)
      const end = new Date(schedule.endDate)

      if (start > now) return 'Upcoming'
      if (end < now) return 'Completed'
      return 'Active'
    },
    showEditor() {
      this.selectedSchedule = null
      this.showEditorModal = true
    },
    editSchedule(schedule) {
      this.selectedSchedule = schedule
      this.showEditorModal = true
    },
    hideEditor() {
      this.showEditorModal = false
    },
    handleScheduleSaved(updatedSchedule) {
      if (this.selectedSchedule) {
        // Update existing schedule
        const index = this.schedules.findIndex(s => s.id === updatedSchedule.id)
        if (index !== -1) {
          this.schedules.splice(index, 1, updatedSchedule)
        }
      } else {
        // Add new schedule
        this.schedules.unshift(updatedSchedule)
      }
      this.hideEditor()
    },
    confirmDelete(schedule) {
      this.scheduleToDelete = schedule
      this.showDeleteConfirm = true
    },
    cancelDelete() {
      this.scheduleToDelete = null
      this.showDeleteConfirm = false
    },
    async deleteSchedule() {
      try {
        const response = await fetch(`/api/scheduling/${this.scheduleToDelete.id}`, {
          method: 'DELETE'
        })
        
        if (response.ok) {
          this.schedules = this.schedules.filter(s => s.id !== this.scheduleToDelete.id)
        } else {
          throw new Error('Failed to delete schedule')
        }
      } catch (err) {
        console.error('Error deleting schedule:', err)
        // Show error to user
      } finally {
        this.cancelDelete()
      }
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++
      }
    },
    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--
      }
    }
  }
}
</script>

<style scoped>
.schedule-list {
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.filter-controls {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 10px;
}

.schedule-items {
  list-style: none;
  padding: 0;
}

.schedule-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  margin-bottom: 10px;
  background: #fff;
  border-radius: 4px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.schedule-info {
  flex: 1;
}

.schedule-info h3 {
  margin: 0 0 5px 0;
}

.schedule-info p {
  margin: 0 0 10px 0;
  color: #666;
}

.schedule-dates {
  display: flex;
  gap: 10px;
  font-size: 0.9em;
  color: #555;
}

.schedule-status {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 0.8em;
  font-weight: bold;
}

.schedule-status.active {
  background: #e8f5e9;
  color: #2e7d32;
}

.schedule-status.upcoming {
  background: #e3f2fd;
  color: #1565c0;
}

.schedule-status.completed {
  background: #f5f5f5;
  color: #616161;
}

.schedule-actions {
  display: flex;
  gap: 10px;
}

button {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  background: #f5f5f5;
}

button:hover {
  background: #e0e0e0;
}

button.danger {
  background: #ffebee;
  color: #c62828;
}

button.danger:hover {
  background: #ffcdd2;
}

.loading, .error, .empty {
  padding: 20px;
  text-align: center;
  background: #fff;
  border-radius: 4px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.error {
  color: #c62828;
  background: #ffebee;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  margin-top: 20px;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  max-width: 500px;
  width: 90%;
}

.modal h3 {
  margin-top: 0;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}
</style>