<template>
  <div class="plugin-manager">
    <h2>Plugin Marketplace</h2>
    
    <div class="plugin-filters">
      <input v-model="searchQuery" placeholder="Search plugins...">
      <select v-model="filterType">
        <option value="all">All</option>
        <option value="free">Free</option>
        <option value="premium">Premium</option>
        <option value="installed">Installed</option>
      </select>
    </div>

    <div class="plugin-list">
      <div v-for="plugin in filteredPlugins" :key="plugin.id" class="plugin-card">
        <div class="plugin-header">
          <h3>{{ plugin.name }}</h3>
          <span class="plugin-version">{{ plugin.version }}</span>
          <span class="plugin-type" :class="plugin.monetization.type">
            {{ plugin.monetization.type }}
          </span>
        </div>
        
        <p class="plugin-description">{{ plugin.description }}</p>
        
        <div class="plugin-actions">
          <button v-if="!isInstalled(plugin.id)" 
                  @click="installPlugin(plugin)">
            Install
          </button>
          <button v-else class="installed">
            Installed
          </button>
          
          <div v-if="plugin.monetization.type === 'premium'" class="license-input">
            <input v-model="licenseKeys[plugin.id]" 
                   placeholder="Enter license key"
                   v-if="!isLicensed(plugin.id)">
            <span v-else class="license-valid">✓ Valid license</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'

export default {
  setup() {
    const plugins = ref([])
    const installedPlugins = ref([])
    const searchQuery = ref('')
    const filterType = ref('all')
    const licenseKeys = ref({})
    const loading = ref(false)
    const error = ref(null)

    const fetchPlugins = async () => {
      try {
        loading.value = true
        const response = await fetch('/api/plugins')
        const data = await response.json()
        plugins.value = data.remote || []
        installedPlugins.value = data.installed || []
      } catch (err) {
        error.value = err.message
      } finally {
        loading.value = false
      }
    }

    const installPlugin = async (plugin) => {
      try {
        loading.value = true
        const response = await fetch('/api/plugins/install', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            plugin_id: plugin.id,
            license_key: licenseKeys.value[plugin.id]
          })
        })
        
        const result = await response.json()
        if (result.status === 'success') {
          installedPlugins.value.push(plugin.id)
        } else {
          throw new Error(result.message)
        }
      } catch (err) {
        error.value = err.message
      } finally {
        loading.value = false
      }
    }

    const isInstalled = (pluginId) => {
      return installedPlugins.value.includes(pluginId)
    }

    const isLicensed = (pluginId) => {
      return installedPlugins.value[pluginId]?.valid || false
    }

    const filteredPlugins = computed(() => {
      return plugins.value.filter(plugin => {
        const matchesSearch = plugin.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                             plugin.description.toLowerCase().includes(searchQuery.value.toLowerCase())
        
        const matchesFilter = filterType.value === 'all' ||
                             (filterType.value === 'free' && plugin.monetization.type === 'free') ||
                             (filterType.value === 'premium' && plugin.monetization.type === 'premium') ||
                             (filterType.value === 'installed' && isInstalled(plugin.id))
        
        return matchesSearch && matchesFilter
      })
    })

    onMounted(fetchPlugins)

    return {
      plugins,
      installedPlugins,
      searchQuery,
      filterType,
      licenseKeys,
      loading,
      error,
      filteredPlugins,
      installPlugin,
      isInstalled,
      isLicensed
    }
  }
}
</script>

<style scoped>
.plugin-manager {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.plugin-filters {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
}

.plugin-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.plugin-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 15px;
  background: white;
}

.plugin-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.plugin-type {
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 0.8em;
}

.plugin-type.free {
  background: #e6f7ff;
  color: #1890ff;
}

.plugin-type.premium {
  background: #fff7e6;
  color: #fa8c16;
}

.plugin-actions {
  margin-top: 15px;
  display: flex;
  gap: 10px;
  align-items: center;
}

.license-input {
  flex-grow: 1;
}

.license-valid {
  color: #52c41a;
  font-size: 0.9em;
}
</style>