<template>
  <div>
    <h1>Version History</h1>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Date</th>
          <th>Author</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="version in versions" :key="version.id">
          <td>{{ version.id }}</td>
          <td>{{ version.date }}</td>
          <td>{{ version.author }}</td>
          <td>
            <button @click="compareVersion(version.id)">Compare</button>
            <button @click="restoreVersion(version.id)">Restore</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
  setup() {
    const versions = ref([]);

    const fetchVersions = async () => {
      try {
        const response = await axios.get('/api/versions');
        versions.value = response.data;
      } catch (error) {
        console.error('Error fetching versions:', error);
      }
    };

    onMounted(fetchVersions);

    const compareVersion = (id) => {
      // Placeholder logic
      alert('Compare version ' + id);
    };

    const restoreVersion = async (id) => {
      try {
        const response = await axios.post(`/api/versions/restore/${id}`);
        if (response.data.status === 'success') {
          alert('Version restored successfully');
          fetchVersions(); // Refresh the list
        } else {
          alert('Restore failed: ' + (response.data.error || 'Unknown error'));
        }
      } catch (error) {
        console.error('Restore failed:', error);
        alert('Restore failed: ' + error.message);
      }
    };

    return {
      versions,
      compareVersion,
      restoreVersion,
    };
  },
};
</script>

<style scoped>
table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 8px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

th {
  background-color: #f4f4f4;
}

tr:hover {
  background-color: #f1f1f1;
}
</style>