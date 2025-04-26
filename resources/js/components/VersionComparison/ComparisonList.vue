<template>
  <div class="comparison-list">
    <div class="list-controls">
      <div class="search-filter">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search comparisons..."
          class="search-input"
        />
        <button class="search-btn">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
      
      <div class="sort-filter">
        <select v-model="sortBy" class="sort-select">
          <option value="date">Date (Newest)</option>
          <option value="date_asc">Date (Oldest)</option>
          <option value="similarity">Similarity (High)</option>
          <option value="similarity_asc">Similarity (Low)</option>
          <option value="changes">Changes (High)</option>
          <option value="changes_asc">Changes (Low)</option>
        </select>
      </div>
    </div>

    <div class="list-container">
      <div v-if="loading" class="loading-overlay">
        <div class="spinner"></div>
        <span>Loading comparisons...</span>
      </div>

      <div v-else>
        <div v-if="filteredComparisons.length === 0" class="empty-state">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3>No comparisons found</h3>
          <p v-if="searchQuery">Try adjusting your search query</p>
          <p v-else>No version comparisons available</p>
        </div>

        <ul v-else class="comparison-items">
          <li 
            v-for="comparison in filteredComparisons"
            :key="comparison.id"
            class="comparison-item"
            @click="$emit('view', comparison.id)"
          >
            <div class="item-header">
              <div class="version-info">
                <span class="from-version">{{ comparison.fromVersion }}</span>
                <span class="arrow">→</span>
                <span class="to-version">{{ comparison.toVersion }}</span>
              </div>
              <div class="date-info">
                {{ formatDate(comparison.date) }}
              </div>
            </div>

            <div class="item-stats">
              <div class="stat">
                <span class="stat-label">Similarity:</span>
                <span class="stat-value">{{ comparison.changes.similarity }}%</span>
              </div>
              <div class="stat">
                <span class="stat-label">Changes:</span>
                <span class="stat-value">
                  <span class="additions">+{{ comparison.changes.additions }}</span>
                  <span class="deletions">-{{ comparison.changes.deletions }}</span>
                  <span class="modifications">~{{ comparison.changes.modifications }}</span>
                </span>
              </div>
              <div class="stat">
                <span class="stat-label">Author:</span>
                <span class="stat-value">{{ comparison.author }}</span>
              </div>
            </div>

            <div class="item-preview">
              <div class="preview-header">
                <span class="preview-title">Preview</span>
              </div>
              <div class="preview-content">
                <div 
                  v-for="(line, i) in comparison.previewLines"
                  :key="i"
                  :class="['preview-line', `preview-line-${line.type}`]"
                  v-html="highlightSyntax(line.content, comparison.language)"
                />
              </div>
            </div>
          </li>
        </ul>

        <div v-if="hasMore" class="load-more">
          <button 
            @click="$emit('load-more')"
            :disabled="loadingMore"
            class="load-more-btn"
          >
            <span v-if="!loadingMore">Load More</span>
            <span v-else>Loading...</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Prism from 'prismjs'
import 'prismjs/themes/prism.css'
import 'prismjs/components/prism-javascript'
import 'prismjs/components/prism-php'
import 'prismjs/components/prism-markup'
import 'prismjs/components/prism-css'
import 'prismjs/components/prism-json'

export default {
  props: {
    comparisons: {
      type: Array,
      required: true
    },
    loading: {
      type: Boolean,
      default: false
    },
    loadingMore: {
      type: Boolean,
      default: false
    },
    hasMore: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      searchQuery: '',
      sortBy: 'date'
    }
  },
  computed: {
    filteredComparisons() {
      let filtered = this.comparisons

      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(c => 
          c.fromVersion.toLowerCase().includes(query) ||
          c.toVersion.toLowerCase().includes(query) ||
          c.author.toLowerCase().includes(query) ||
          c.language.toLowerCase().includes(query)
        )
      }

      // Apply sorting
      switch (this.sortBy) {
        case 'date':
          return filtered.sort((a, b) => new Date(b.date) - new Date(a.date))
        case 'date_asc':
          return filtered.sort((a, b) => new Date(a.date) - new Date(b.date))
        case 'similarity':
          return filtered.sort((a, b) => b.changes.similarity - a.changes.similarity)
        case 'similarity_asc':
          return filtered.sort((a, b) => a.changes.similarity - b.changes.similarity)
        case 'changes':
          return filtered.sort((a, b) => 
            (b.changes.additions + b.changes.deletions + b.changes.modifications) - 
            (a.changes.additions + a.changes.deletions + a.changes.modifications)
          )
        case 'changes_asc':
          return filtered.sort((a, b) => 
            (a.changes.additions + a.changes.deletions + a.changes.modifications) - 
            (b.changes.additions + b.changes.deletions + b.changes.modifications)
          )
        default:
          return filtered
      }
    }
  },
  methods: {
    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    },
    highlightSyntax(content, type) {
      if (type === 'none' || !type) {
        return content
      }

      try {
        const highlighted = Prism.highlight(
          content,
          Prism.languages[type] || Prism.languages.plaintext,
          type
        )
        return highlighted
      } catch (e) {
        console.warn('Syntax highlighting failed:', e)
        return content
      }
    }
  }
}
</script>

<style scoped>
.comparison-list {
  @apply bg-white rounded-lg shadow;
}

.list-controls {
  @apply flex flex-col md:flex-row justify-between items-start md:items-center p-4 border-b border-gray-200;
}

.search-filter {
  @apply flex items-center mb-4 md:mb-0;
}

.search-input {
  @apply px-4 py-2 border border-gray-300 rounded-l text-sm w-full md:w-64;
}

.search-btn {
  @apply px-3 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r hover:bg-gray-200;
}

.sort-filter {
  @apply w-full md:w-auto;
}

.sort-select {
  @apply px-3 py-2 border border-gray-300 rounded text-sm;
}

.list-container {
  @apply p-4 relative min-h-[300px];
}

.loading-overlay {
  @apply absolute inset-0 flex flex-col items-center justify-center gap-2 bg-white bg-opacity-80 z-10;
}

.spinner {
  @apply animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full;
}

.empty-state {
  @apply flex flex-col items-center justify-center py-12 text-center;
}

.empty-state svg {
  @apply mb-4;
}

.empty-state h3 {
  @apply text-lg font-medium text-gray-900 mb-1;
}

.empty-state p {
  @apply text-sm text-gray-500;
}

.comparison-items {
  @apply space-y-4;
}

.comparison-item {
  @apply p-4 border border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 hover:shadow-sm;
}

.item-header {
  @apply flex flex-col md:flex-row justify-between items-start md:items-center mb-3;
}

.version-info {
  @apply flex items-center gap-2 text-gray-800 font-medium mb-2 md:mb-0;
}

.from-version, .to-version {
  @apply font-semibold;
}

.arrow {
  @apply text-gray-400;
}

.date-info {
  @apply text-sm text-gray-500;
}

.item-stats {
  @apply grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4;
}

.stat {
  @apply flex items-center gap-2;
}

.stat-label {
  @apply text-sm text-gray-500;
}

.stat-value {
  @apply text-sm font-medium;
}

.additions {
  @apply text-green-600;
}

.deletions {
  @apply text-red-600;
}

.modifications {
  @apply text-yellow-600;
}

.item-preview {
  @apply border-t border-gray-200 pt-3;
}

.preview-header {
  @apply mb-2;
}

.preview-title {
  @apply text-xs text-gray-500;
}

.preview-content {
  @apply bg-gray-50 p-2 rounded font-mono text-sm overflow-hidden max-h-[120px];
}

.preview-line {
  @apply whitespace-pre-wrap break-all;
}

.preview-line-none {
  @apply text-gray-800;
}

.preview-line-add {
  @apply text-green-600 bg-green-50;
}

.preview-line-del {
  @apply text-red-600 bg-red-50;
}

.preview-line-mod {
  @apply text-yellow-600 bg-yellow-50;
}

.load-more {
  @apply mt-6 text-center;
}

.load-more-btn {
  @apply px-4 py-2 bg-gray-100 rounded hover:bg-gray-200 disabled:bg-gray-100 disabled:text-gray-400;
}
</style>
