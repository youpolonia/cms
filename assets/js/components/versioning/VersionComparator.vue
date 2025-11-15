<template>
  <div class="version-comparator">
    <VersionSelector 
      :versions="versions" 
      @select-left="selectLeftVersion"
      @select-right="selectRightVersion"
    />
    <DiffRenderer 
      :left-content="leftContent"
      :right-content="rightContent"
      :diffs="diffs"
      @navigate="navigateToChange"
    />
  </div>
</template>

<script>
export default {
  name: 'VersionComparator',
  components: {
    VersionSelector: () => import('./VersionSelector.vue'),
    DiffRenderer: () => import('./DiffRenderer.vue')
  },
  props: {
    versions: {
      type: Array,
      required: true
    },
    initialLeft: {
      type: String,
      default: null
    },
    initialRight: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      leftVersion: this.initialLeft,
      rightVersion: this.initialRight,
      leftContent: '',
      rightContent: '',
      diffs: []
    }
  },
  methods: {
    selectLeftVersion(versionId) {
      this.leftVersion = versionId
      this.fetchContent()
    },
    selectRightVersion(versionId) {
      this.rightVersion = versionId
      this.fetchContent()
    },
    async fetchContent() {
      if (!this.leftVersion || !this.rightVersion) return
      
      // Fetch content for both versions
      const [leftRes, rightRes] = await Promise.all([
        fetch(`/api/versions/${this.leftVersion}/content`),
        fetch(`/api/versions/${this.rightVersion}/content`)
      ])
      
      this.leftContent = await leftRes.text()
      this.rightContent = await rightRes.text()
      
      // Get diffs from diff engine
      const diffRes = await fetch('/api/diff', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          left: this.leftContent,
          right: this.rightContent
        })
      })
      this.diffs = await diffRes.json()
    },
    navigateToChange(index) {
      // Scroll to the specific diff
      const diffElement = document.getElementById(`diff-${index}`)
      if (diffElement) {
        diffElement.scrollIntoView({ behavior: 'smooth' })
      }
    }
  }
}
</script>

<style scoped>
.version-comparator {
  display: flex;
  flex-direction: column;
  height: 100%;
  gap: 1rem;
}
</style>