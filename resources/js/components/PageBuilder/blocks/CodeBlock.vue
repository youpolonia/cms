<template>
  <div class="code-block">
    <div class="code-header">
      <select v-model="data.language" @change="updateCode">
        <option value="javascript">JavaScript</option>
        <option value="html">HTML</option>
        <option value="css">CSS</option>
        <option value="php">PHP</option>
        <option value="python">Python</option>
      </select>
      <button @click="copyCode">Copy</button>
    </div>
    
    <pre><code ref="codeElement">{{ data.content }}</code></pre>
    <textarea 
      v-model="data.content" 
      @input="updateCode"
      class="code-editor"
    ></textarea>
  </div>
</template>

<script>
import hljs from 'highlight.js'
import 'highlight.js/styles/github.css'

export default {
  props: {
    data: {
      type: Object,
      default: () => ({
        language: 'javascript',
        content: '// Enter your code here'
      })
    }
  },
  mounted() {
    this.highlightCode()
  },
  methods: {
    highlightCode() {
      if (this.$refs.codeElement) {
        hljs.highlightElement(this.$refs.codeElement)
      }
    },
    updateCode() {
      this.$emit('update', this.data)
      this.$nextTick(() => {
        this.highlightCode()
      })
    },
    copyCode() {
      navigator.clipboard.writeText(this.data.content)
    }
  },
  watch: {
    'data.language'() {
      this.highlightCode()
    }
  }
}
</script>

<style>
.code-block {
  border: 1px solid #eee;
  padding: 15px;
  margin-bottom: 20px;
  position: relative;
}

.code-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

pre {
  margin: 0;
  padding: 10px;
  background: #f8f8f8;
  border-radius: 4px;
  overflow-x: auto;
}

.code-editor {
  width: 100%;
  min-height: 200px;
  font-family: monospace;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  margin-top: 10px;
  resize: vertical;
}
</style>