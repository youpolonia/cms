<template>
  <div class="approval-detail">
    <h2>Approval Request {{ requestId }}</h2>
    <div v-if="loading">Loading...</div>
    <div v-else-if="error">{{ error }}</div>
    <div v-else>
      <div class="request-info">
        <p>Content ID: {{ approval.content_id }}</p>
        <p>Requester: {{ approval.requester_id }}</p>
        <p>Current Stage: {{ approval.current_stage + 1 }} of {{ approval.stages.length }}</p>
        <p>Status: {{ approval.status }}</p>
      </div>

      <div class="stage-actions" v-if="approval.status === 'pending'">
        <button @click="approveStage">Approve</button>
        <button @click="rejectStage">Reject</button>
      </div>

      <div class="history">
        <h3>Approval History</h3>
        <ul>
          <li v-for="(stage, index) in approval.stages" :key="index">
            Stage {{ index + 1 }}: {{ stage.approved_by || 'Pending' }}
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['requestId'],
  data() {
    return {
      loading: true,
      error: null,
      approval: null
    }
  },
  async created() {
    try {
      const response = await fetch(`/api/workflows/${this.requestId}`);
      this.approval = await response.json();
    } catch (err) {
      this.error = 'Failed to load approval details';
    } finally {
      this.loading = false;
    }
  },
  methods: {
    async approveStage() {
      try {
        await fetch(`/api/workflows/${this.requestId}/approve`, {
          method: 'POST'
        });
        this.$router.push('/workflows');
      } catch (err) {
        this.error = 'Failed to approve';
      }
    },
    async rejectStage() {
      try {
        await fetch(`/api/workflows/${this.requestId}/reject`, {
          method: 'POST'
        });
        this.$router.push('/workflows');
      } catch (err) {
        this.error = 'Failed to reject';
      }
    }
  }
}
</script>

<style scoped>
.approval-detail {
  padding: 1rem;
}
.request-info {
  margin-bottom: 1rem;
}
.stage-actions button {
  margin-right: 0.5rem;
}
.history {
  margin-top: 1rem;
}
</style>