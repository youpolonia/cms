<template>
  <div class="collaborators">
    <div v-for="user in collaborators" :key="user.id" class="collaborator">
      <div class="avatar" :style="{ backgroundColor: user.color }">
        {{ user.name.charAt(0).toUpperCase() }}
      </div>
      <span class="name">{{ user.name }}</span>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    collaborators: {
      type: Array,
      required: true
    }
  }
}
</script>

<style scoped>
.collaborators {
  position: fixed;
  bottom: 20px;
  right: 20px;
  display: flex;
  gap: 10px;
}

.collaborator {
  display: flex;
  align-items: center;
  gap: 5px;
  background: rgba(255,255,255,0.9);
  padding: 5px 10px;
  border-radius: 20px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
}

.name {
  font-size: 12px;
}
</style>