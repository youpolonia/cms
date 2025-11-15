<template>
  <div class="theme-creator">
    <h2>Create New Theme</h2>
    
    <div class="form-group">
      <label for="themeName">Theme Name</label>
      <input 
        type="text" 
        id="themeName" 
        v-model="themeName" 
        @input="validateName"
        :class="{ 'is-invalid': nameError }"
      >
      <div v-if="nameError" class="invalid-feedback">
        {{ nameError }}
      </div>
      <small class="form-text text-muted">
        Only letters, numbers, and hyphens allowed (3-50 chars)
      </small>
    </div>

    <button 
      @click="createTheme" 
      :disabled="!isValid || isCreating"
      class="btn btn-primary"
    >
      <span v-if="isCreating">Creating...</span>
      <span v-else>Create Theme</span>
    </button>

    <div v-if="message" class="alert" :class="messageClass">
      {{ message }}
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      themeName: '',
      nameError: '',
      isCreating: false,
      message: '',
      messageClass: ''
    }
  },
  computed: {
    isValid() {
      return this.themeName && !this.nameError
    }
  },
  methods: {
    validateName() {
      const regex = /^[a-zA-Z0-9-]{3,50}$/
      if (!regex.test(this.themeName)) {
        this.nameError = 'Invalid theme name format'
      } else {
        this.nameError = ''
      }
    },
    async createTheme() {
      if (!this.isValid || this.isCreating) return

      this.isCreating = true
      this.message = ''
      
      try {
        const response = await fetch('/admin/themes/create.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            themeName: this.themeName
          })
        })

        const result = await response.json()
        
        if (result.success) {
          this.message = 'Theme created successfully!'
          this.messageClass = 'alert-success'
          this.themeName = ''
        } else {
          this.message = result.error || 'Failed to create theme'
          this.messageClass = 'alert-danger'
        }
      } catch (error) {
        this.message = 'Network error occurred'
        this.messageClass = 'alert-danger'
      } finally {
        this.isCreating = false
      }
    }
  }
}
</script>

<style scoped>
.theme-creator {
  max-width: 500px;
  margin: 0 auto;
  padding: 20px;
}
.form-group {
  margin-bottom: 20px;
}
.invalid-feedback {
  color: #dc3545;
}
.is-invalid {
  border-color: #dc3545;
}
.alert {
  margin-top: 20px;
  padding: 10px;
  border-radius: 4px;
}
.alert-success {
  background-color: #d4edda;
  color: #155724;
}
.alert-danger {
  background-color: #f8d7da;
  color: #721c24;
}
</style>