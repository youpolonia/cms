<template>
  <div class="page-builder-demo">
    <h1>Page Builder Demo</h1>
    <PageBuilder />
  </div>
</template>

<script>
import PageBuilder from '@/components/PageBuilder/PageBuilder.vue'

export default {
  components: { PageBuilder }
}
</script>

<style>
.page-builder-demo {
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
}
</style>