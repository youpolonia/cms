<template>
  <div class="approval-list">
    <h2>Pending Approvals</h2>
    <div v-if="loading">Loading...</div>
    <div v-else-if="approvals.length === 0">No pending approvals</div>
    <ul v-else>
      <li v-for="approval in approvals" :key="approval.request_id">
        <router-link :to="`/workflows/${approval.request_id}`">
          {{ approval.content_id }} - Stage {{ approval.current_stage + 1 }}
        </router-link>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  data() {
    return {
      loading: true,
      approvals: []
    }
  },
  async created() {
    const response = await fetch('/api/workflows/pending');
    this.approvals = await response.json();
    this.loading = false;
  }
}
</script>

<style scoped>
.approval-list {
  padding: 1rem;
}
ul {
  list-style: none;
  padding: 0;
}
li {
  padding: 0.5rem;
  border-bottom: 1px solid #eee;
}
</style>