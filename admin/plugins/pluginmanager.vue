<template>
  <div class="plugin-manager">
    <div class="header">
      <h1>Plugin Marketplace</h1>
      <div class="controls">
        <input 
          v-model="searchQuery" 
          placeholder="Search plugins..."
          @input="filterPlugins"
        >
        <select v-model="filterCategory" @change="filterPlugins">
          <option value="all">All Categories</option>
          <option value="free">Free</option>
          <option value="premium">Premium</option>
          <option value="token">Token</option>
        </select>
      </div>
    </div>

    <div class="plugin-list">
      <div 
        v-for="plugin in filteredPlugins" 
        :key="plugin.id"
        class="plugin-card"
        :class="{
          'incompatible': !isCompatible(plugin),
          'installed': isInstalled(plugin.id)
        }"
      >
        <div class="plugin-header">
          <h3>{{ plugin.name }}</h3>
          <span class="version">{{ plugin.version }}</span>
          <span class="price" v-if="plugin.price > 0">
            ${{ plugin.price.toFixed(2) }}
          </span>
          <span class="free-badge" v-else>FREE</span>
        </div>

        <div class="plugin-body">
          <p>{{ plugin.description }}</p>
          <div class="compatibility" v-if="!isCompatible(plugin)">
            <span class="warning">⚠️ Incompatible with your system</span>
          </div>
        </div>

        <div class="plugin-actions">
          <button 
            v-if="!isInstalled(plugin.id)"
            @click="installPlugin(plugin)"
          >
            Install
          </button>
          <button 
            v-else
            class="installed-btn"
            disabled
          >
            Installed
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      plugins: [],
      searchQuery: '',
      filterCategory: 'all',
      filteredPlugins: [],
      installedPlugins: [],
      isLoading: false,
      error: null,
      activePlugin: null,
      showDetails: false,
      installingPlugin: null
    }
  },
  async created() {
    await this.fetchRemotePlugins();
    await this.fetchInstalledPlugins();
    this.filterPlugins();
  },
  methods: {
    async fetchRemotePlugins() {
      this.isLoading = true;
      this.error = null;
      try {
        const response = await fetch('/api/plugins/registry');
        if (!response.ok) throw new Error('Failed to fetch plugins');
        this.plugins = await response.json();
      } catch (error) {
        this.error = error.message;
      } finally {
        this.isLoading = false;
      }
    },
    async fetchInstalledPlugins() {
      try {
        const response = await fetch('/api/plugins/installed');
        this.installedPlugins = await response.json();
      } catch (error) {
        console.error('Failed to fetch installed plugins:', error);
      }
    },
    isCompatible(plugin) {
      // Check PHP and CMS version compatibility
      return true; // Simplified for initial implementation
    },
    isInstalled(pluginId) {
      return this.installedPlugins.includes(pluginId);
    },
    filterPlugins() {
      this.filteredPlugins = this.plugins.filter(plugin => {
        const matchesSearch = plugin.name.toLowerCase().includes(
          this.searchQuery.toLowerCase()
        ) || plugin.description.toLowerCase().includes(
          this.searchQuery.toLowerCase()
        );
        
        const matchesCategory = this.filterCategory === 'all' || 
          plugin.license_type === this.filterCategory;
        
        return matchesSearch && matchesCategory;
      });
    },
    async installPlugin(plugin) {
      this.installingPlugin = plugin.id;
      this.error = null;
      
      try {
        let licenseKey = null;
        if (plugin.license_type !== 'free') {
          licenseKey = window.prompt(
            `Enter license key for ${plugin.name}`,
            ''
          );
          if (!licenseKey) return;
        }

        const response = await fetch('/api/plugins/install', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            pluginId: plugin.id,
            licenseKey
          })
        });

        if (!response.ok) {
          const error = await response.json();
          throw new Error(error.message || 'Installation failed');
        }

        await this.fetchInstalledPlugins();
        this.filterPlugins();
      } catch (error) {
        this.error = error.message;
      } finally {
        this.installingPlugin = null;
      }
    },
    showPluginDetails(plugin) {
      this.activePlugin = plugin;
      this.showDetails = true;
    }
  }
}
</script>

<style scoped>
.notification {
  padding: 10px;
  margin-bottom: 20px;
  border-radius: 4px;
}
.error {
  background: #ffebee;
  color: #c62828;
}
.loading {
  text-align: center;
  padding: 20px;
}
.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal-content {
  background: white;
  padding: 20px;
  border-radius: 5px;
  max-width: 600px;
  width: 90%;
}
.plugin-manager {
  padding: 20px;
}
.header {
  margin-bottom: 20px;
}
.controls {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}
.plugin-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}
.plugin-card {
  border: 1px solid #ddd;
  border-radius: 5px;
  padding: 15px;
  background: white;
}
.plugin-card.incompatible {
  opacity: 0.7;
  border-left: 3px solid orange;
}
.plugin-card.installed {
  border-left: 3px solid green;
}
.plugin-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}
.price {
  margin-left: auto;
  font-weight: bold;
}
.free-badge {
  margin-left: auto;
  background: #4CAF50;
  color: white;
  padding: 2px 8px;
  border-radius: 10px;
  font-size: 0.8em;
}
.warning {
  color: orange;
  font-size: 0.9em;
}
.plugin-actions {
  margin-top: 15px;
}
button {
  padding: 5px 15px;
  background: #4CAF50;
  color: white;
  border: none;
  border-radius: 3px;
  cursor: pointer;
}
button.installed-btn {
  background: #ccc;
  cursor: default;
}
</style>