<template>
  <div class="version-admin">
    <div class="version-timeline">
      <h2>Version History</h2>
      <table>
        <thead>
          <tr>
            <th>Version</th>
            <th>Date</th>
            <th>Author</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="version in versions" :key="version.id">
            <td>{{ version.title }}</td>
            <td>{{ formatDate(version.created_at) }}</td>
            <td>{{ version.author_name }}</td>
            <td>
              <button @click="compareWithCurrent(version.id)">Compare</button>
              <button @click="restoreVersion(version.id)">Restore</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="comparison" class="diff-viewer">
      <h2>Comparison Results</h2>
      <div class="view-options">
        <button @click="viewMode = 'side-by-side'" :class="{active: viewMode === 'side-by-side'}">Side by Side</button>
        <button @click="viewMode = 'unified'" :class="{active: viewMode === 'unified'}">Unified</button>
        <button @click="viewMode = 'visual'" :class="{active: viewMode === 'visual'}">Visual</button>
      </div>

      <div v-if="viewMode === 'side-by-side'" class="side-by-side">
        <div class="old-version">
          <h3>Old Version</h3>
          <div v-html="comparison.side_by_side.old"></div>
        </div>
        <div class="new-version">
          <h3>New Version</h3>
          <div v-html="comparison.side_by_side.new"></div>
        </div>
      </div>

      <div v-if="viewMode === 'unified'" class="unified">
        <pre>{{ comparison.unified }}</pre>
      </div>

      <div v-if="viewMode === 'visual'" class="visual" v-html="comparison.visual"></div>

      <div class="actions">
        <button @click="saveComparison()">Save Comparison</button>
        <button @click="comparison = null">Close</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    contentId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      versions: [],
      comparison: null,
      viewMode: 'side-by-side',
      notes: ''
    }
  },
  mounted() {
    this.fetchVersionHistory();
  },
  methods: {
    async fetchVersionHistory() {
      try {
        const response = await fetch(`/api/content-versions/${this.contentId}/history`);
        this.versions = await response.json();
      } catch (error) {
        console.error('Failed to fetch version history:', error);
      }
    },
    async compareWithCurrent(versionId) {
      try {
        const response = await fetch(`/api/content-versions/${this.contentId}/compare/${versionId}`);
        this.comparison = await response.json();
      } catch (error) {
        console.error('Failed to compare versions:', error);
      }
    },
    async restoreVersion(versionId) {
      if (confirm('Are you sure you want to restore this version?')) {
        try {
          const response = await fetch(`/api/content-versions/${this.contentId}/restore`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ version_id: versionId })
          });
          
          if (response.ok) {
            alert('Version restored successfully');
            this.fetchVersionHistory();
          }
        } catch (error) {
          console.error('Failed to restore version:', error);
        }
      }
    },
    async saveComparison() {
      try {
        const response = await fetch('/api/version-comparisons', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            version1_id: this.comparison.version1_id,
            version2_id: this.comparison.version2_id,
            diff_data: this.comparison.diff_data,
            notes: this.notes
          })
        });

        if (response.ok) {
          alert('Comparison saved successfully');
          this.comparison = null;
        }
      } catch (error) {
        console.error('Failed to save comparison:', error);
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleString();
    }
  }
}
</script>

<style scoped>
.version-admin {
  margin: 20px;
}

.version-timeline table {
  width: 100%;
  border-collapse: collapse;
}

.version-timeline th, .version-timeline td {
  padding: 8px;
  border: 1px solid #ddd;
}

.diff-viewer {
  margin-top: 20px;
  border: 1px solid #eee;
  padding: 15px;
}

.side-by-side {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.view-options button {
  margin-right: 10px;
  padding: 5px 10px;
}

.view-options button.active {
  background-color: #007bff;
  color: white;
}

.actions {
  margin-top: 20px;
}

.actions button {
  margin-right: 10px;
  padding: 8px 15px;
}
</style>