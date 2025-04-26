import axios from 'axios'

const analyticsService = {
  async getVersionComparisonStats(version1Id, version2Id) {
    try {
      const response = await axios.get(`/api/analytics/compare/${version1Id}/${version2Id}`)
      return response.data.data
    } catch (error) {
      console.error('Error fetching comparison stats:', error)
      throw error
    }
  },

  async exportAnalytics(version1Id, version2Id, format = 'csv') {
    try {
      const response = await axios.post('/api/analytics/export', {
        version1_id: version1Id,
        version2_id: version2Id,
        export_format: format
      }, {
        responseType: 'blob'
      })
      return response.data
    } catch (error) {
      console.error('Error exporting analytics:', error)
      throw error
    }
  },

  async refreshCache(version1Id, version2Id) {
    try {
      await axios.post('/api/analytics/refresh-cache', {
        version1_id: version1Id,
        version2_id: version2Id
      })
      return true
    } catch (error) {
      console.error('Error refreshing cache:', error)
      throw error
    }
  }
}

export default analyticsService