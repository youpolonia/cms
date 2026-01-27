<template>
  <div class="plugin-blocks">
    <div class="block-selector">
      <h3>Plugin Blocks</h3>
      <div class="block-categories">
        <div v-for="category in categories" :key="category">
          <h4>{{ category }}</h4>
          <button
            v-for="block in blocksByCategory(category)"
            :key="block.type"
            @click="selectBlock(block)"
          >
            <i :class="block.icon"></i>
            {{ block.label }}
          </button>
        </div>
      </div>
    </div>

    <div class="config-form" v-if="activeBlock">
      <form-generator
        :schema="activeBlock.configSchema"
        :initial-data="blockConfig"
        @update="handleConfigUpdate"
      />
    </div>

    <div class="block-renderer">
      <component
        :is="activeBlock.component"
        v-if="activeBlock"
        :config="blockConfig"
        @save="saveBlock"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: 'PluginBlocks',
  data() {
    return {
      blocks: [],
      activeBlock: null,
      blockConfig: {},
      categories: []
    }
  },
  computed: {
    blocksByCategory() {
      return (category) => this.blocks.filter(b => b.category === category);
    }
  },
  methods: {
    registerBlock(blockDef) {
      this.blocks.push(blockDef);
      if (!this.categories.includes(blockDef.category)) {
        this.categories.push(blockDef.category);
      }
    },
    selectBlock(block) {
      this.activeBlock = block;
      this.blockConfig = block.defaultConfig || {};
    },
    handleConfigUpdate(config) {
      this.blockConfig = config;
      this.$emit('config-update', {
        type: this.activeBlock.type,
        config
      });
    },
    saveBlock() {
      this.$emit('save', {
        type: this.activeBlock.type,
        config: this.blockConfig
      });
    },
    async fetchBlockConfig(blockType) {
      const response = await fetch(`/api/blocks/config?type=${encodeURIComponent(blockType)}`);
      return response.json();
    }
  }
}
</script>

<style scoped>
.plugin-blocks {
  display: grid;
  grid-template-columns: 250px 1fr;
  gap: 20px;
}
.block-selector {
  border-right: 1px solid #eee;
  padding-right: 15px;
}
.config-form {
  margin-bottom: 20px;
}
</style>