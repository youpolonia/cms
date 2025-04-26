<template>
  <div class="media-gallery">
    <div class="upload-area">
      <input type="file" ref="fileInput" @change="handleFileUpload" />
      <button @click="triggerUpload">Upload Media</button>
    </div>

    <div class="media-grid">
      <div v-for="media in mediaItems" :key="media.id" class="media-item">
        <img v-if="isImage(media)" :src="getMediaUrl(media)" />
        <div v-else class="file-icon">
          <span>{{ getFileIcon(media) }}</span>
        </div>
        <div class="media-info">
          <p>{{ media.filename }}</p>
          <button @click="deleteMedia(media)">Delete</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      mediaItems: []
    }
  },
  async created() {
    await this.fetchMedia();
  },
  methods: {
    async fetchMedia() {
      const response = await axios.get('/api/media');
      this.mediaItems = response.data.data;
    },
    triggerUpload() {
      this.$refs.fileInput.click();
    },
    async handleFileUpload(event) {
      const file = event.target.files[0];
      const formData = new FormData();
      formData.append('file', file);

      try {
        await axios.post('/api/media', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        await this.fetchMedia();
      } catch (error) {
        console.error('Upload failed:', error);
      }
    },
    async deleteMedia(media) {
      if (confirm('Are you sure you want to delete this media?')) {
        await axios.delete(`/api/media/${media.id}`);
        await this.fetchMedia();
      }
    },
    isImage(media) {
      return media.metadata?.mime_type?.startsWith('image/');
    },
    getMediaUrl(media) {
      return `/storage/${media.path}`;
    },
    getFileIcon(media) {
      return media.filename.split('.').pop().toUpperCase();
    }
  }
}
</script>

<style scoped>
.media-gallery {
  padding: 20px;
}
.media-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
  margin-top: 20px;
}
.media-item {
  border: 1px solid #ddd;
  padding: 10px;
  border-radius: 4px;
}
.media-item img {
  max-width: 100%;
  height: auto;
}
.file-icon {
  font-size: 3em;
  text-align: center;
  padding: 20px;
}
</style>