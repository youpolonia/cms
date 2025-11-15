<template>
  <div class="schedule-editor">
    <h2>{{ editing ? 'Edit Schedule' : 'Create New Schedule' }}</h2>
    
    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label for="title">Title</label>
        <input 
          type="text" 
          id="title" 
          v-model="form.title" 
          required
          @blur="validateField('title')"
        >
        <span class="error" v-if="errors.title">{{ errors.title }}</span>
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea 
          id="description" 
          v-model="form.description"
        ></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="startDate">Start Date</label>
          <input 
            type="datetime-local" 
            id="startDate" 
            v-model="form.startDate"
            required
            @change="validateDates"
          >
        </div>

        <div class="form-group">
          <label for="endDate">End Date</label>
          <input 
            type="datetime-local" 
            id="endDate" 
            v-model="form.endDate"
            required
            @change="validateDates"
          >
        </div>
      </div>

      <div class="form-group">
        <label>
          <input type="checkbox" v-model="form.isRecurring">
          Recurring Schedule
        </label>
      </div>

      <div v-if="form.isRecurring" class="recurring-options">
        <div class="form-group">
          <label>Recurrence Pattern</label>
          <select v-model="form.recurrencePattern">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
          </select>
        </div>

        <div class="form-group">
          <label>End After</label>
          <input 
            type="number" 
            v-model="form.recurrenceCount"
            min="1"
          > occurrences
        </div>
      </div>

      <div class="form-actions">
        <button type="button" @click="cancel">Cancel</button>
        <button type="submit">{{ editing ? 'Update' : 'Create' }}</button>
      </div>

      <div v-if="conflicts.length" class="conflicts">
        <h3>Potential Conflicts</h3>
        <ul>
          <li v-for="conflict in conflicts" :key="conflict.id">
            {{ conflict.title }} ({{ formatDate(conflict.startDate) }} - {{ formatDate(conflict.endDate) }})
          </li>
        </ul>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  props: {
    schedule: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      editing: false,
      form: {
        title: '',
        description: '',
        startDate: '',
        endDate: '',
        isRecurring: false,
        recurrencePattern: 'daily',
        recurrenceCount: 1
      },
      errors: {},
      conflicts: []
    }
  },
  created() {
    if (this.schedule) {
      this.editing = true
      this.form = { ...this.schedule }
    }
  },
  methods: {
    validateField(field) {
      // Basic validation logic
      if (field === 'title' && !this.form.title.trim()) {
        this.errors.title = 'Title is required'
      } else {
        delete this.errors[field]
      }
    },
    validateDates() {
      if (new Date(this.form.startDate) > new Date(this.form.endDate)) {
        this.errors.dates = 'End date must be after start date'
      } else {
        delete this.errors.dates
        this.checkForConflicts()
      }
    },
    async checkForConflicts() {
      try {
        const response = await fetch('/api/scheduling/check-conflicts', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            startDate: this.form.startDate,
            endDate: this.form.endDate,
            excludeId: this.editing ? this.schedule.id : null
          })
        })
        this.conflicts = await response.json()
      } catch (error) {
        console.error('Error checking for conflicts:', error)
      }
    },
    async handleSubmit() {
      // Validate all fields
      Object.keys(this.form).forEach(field => this.validateField(field))
      
      if (Object.keys(this.errors).length) return
      
      try {
        const url = this.editing 
          ? `/api/scheduling/${this.schedule.id}`
          : '/api/scheduling'
          
        const method = this.editing ? 'PUT' : 'POST'
        
        const response = await fetch(url, {
          method,
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.form)
        })
        
        if (response.ok) {
          this.$emit('saved', await response.json())
        } else {
          throw new Error('Failed to save schedule')
        }
      } catch (error) {
        console.error('Error saving schedule:', error)
        // Show error to user
      }
    },
    cancel() {
      this.$emit('cancel')
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleString()
    }
  }
}
</script>

<style scoped>
.schedule-editor {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
  margin-bottom: 15px;
}

.form-row {
  display: flex;
  gap: 20px;
}

.form-row .form-group {
  flex: 1;
}

label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

input[type="text"],
input[type="datetime-local"],
input[type="number"],
textarea,
select {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.error {
  color: #e74c3c;
  font-size: 0.9em;
  margin-top: 5px;
  display: block;
}

.form-actions {
  margin-top: 20px;
  text-align: right;
}

button {
  padding: 8px 16px;
  margin-left: 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

button[type="submit"] {
  background: #3498db;
  color: white;
}

.conflicts {
  margin-top: 20px;
  padding: 15px;
  background: #fff8e1;
  border-radius: 4px;
}

.conflicts h3 {
  margin-top: 0;
  color: #e67e22;
}

.recurring-options {
  margin-top: 15px;
  padding: 15px;
  background: #f5f5f5;
  border-radius: 4px;
}
</style>