<template>
  <div class="form-block">
    <div class="form-header">
      <h3>{{ data.title || 'Contact Form' }}</h3>
      <select v-model="newFieldType" @change="addField">
        <option value="">Add Field</option>
        <option value="text">Text</option>
        <option value="email">Email</option>
        <option value="textarea">Textarea</option>
        <option value="select">Dropdown</option>
        <option value="checkbox">Checkbox</option>
      </select>
    </div>

    <div class="form-fields">
      <div 
        v-for="(field, index) in data.fields" 
        :key="index"
        class="form-field"
      >
        <div class="field-header">
          <span>{{ fieldTypes[field.type] || field.type }}</span>
          <button @click="removeField(index)">×</button>
        </div>
        
        <div class="field-config">
          <label>
            Label:
            <input 
              type="text" 
              v-model="field.label" 
              @input="updateField(index, field)"
            >
          </label>
          
          <label>
            Required:
            <input 
              type="checkbox" 
              v-model="field.required"
              @change="updateField(index, field)"
            >
          </label>

          <div v-if="field.type === 'select'" class="select-options">
            <div 
              v-for="(option, optIndex) in field.options" 
              :key="optIndex"
              class="option"
            >
              <input 
                type="text" 
                v-model="option.value"
                @input="updateField(index, field)"
              >
              <button @click="removeOption(index, optIndex)">×</button>
            </div>
            <button @click="addOption(index)">Add Option</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    data: {
      type: Object,
      default: () => ({
        title: '',
        fields: []
      })
    }
  },
  data() {
    return {
      newFieldType: '',
      fieldTypes: {
        text: 'Text Field',
        email: 'Email Field',
        textarea: 'Text Area',
        select: 'Dropdown',
        checkbox: 'Checkbox'
      }
    }
  },
  methods: {
    addField() {
      if (!this.newFieldType) return
      
      const newField = {
        type: this.newFieldType,
        label: '',
        required: false
      }

      if (this.newFieldType === 'select') {
        newField.options = [{ value: '' }]
      }

      this.$emit('update', {
        ...this.data,
        fields: [...this.data.fields, newField]
      })
      
      this.newFieldType = ''
    },
    removeField(index) {
      this.$emit('update', {
        ...this.data,
        fields: this.data.fields.filter((_, i) => i !== index)
      })
    },
    updateField(index, field) {
      const fields = [...this.data.fields]
      fields[index] = field
      this.$emit('update', {
        ...this.data,
        fields
      })
    },
    addOption(fieldIndex) {
      const fields = [...this.data.fields]
      fields[fieldIndex].options.push({ value: '' })
      this.$emit('update', {
        ...this.data,
        fields
      })
    },
    removeOption(fieldIndex, optionIndex) {
      const fields = [...this.data.fields]
      fields[fieldIndex].options = fields[fieldIndex].options.filter(
        (_, i) => i !== optionIndex
      )
      this.$emit('update', {
        ...this.data,
        fields
      })
    }
  }
}
</script>

<style>
.form-block {
  border: 1px solid #eee;
  padding: 15px;
  margin-bottom: 20px;
}

.form-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.form-fields {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-field {
  border: 1px solid #ddd;
  padding: 10px;
  border-radius: 4px;
}

.field-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

.field-header button {
  background: red;
  color: white;
  border: none;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  cursor: pointer;
}

.field-config {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.select-options {
  margin-top: 10px;
  border-top: 1px solid #eee;
  padding-top: 10px;
}

.option {
  display: flex;
  gap: 5px;
  margin-bottom: 5px;
}

.option button {
  background: red;
  color: white;
  border: none;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  cursor: pointer;
}
</style>