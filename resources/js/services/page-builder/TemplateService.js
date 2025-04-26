import axios from 'axios'

export class TemplateService {
  constructor() {
    this.baseUrl = '/api/templates'
  }

  async getTemplates(category = null) {
    const params = category ? { category } : {}
    const response = await axios.get(this.baseUrl, { params })
    return response.data
  }

  async saveTemplate(name, blocks, previewImage, categories = []) {
    const payload = {
      name,
      blocks: JSON.stringify(blocks),
      preview_image: previewImage,
      categories
    }
    const response = await axios.post(this.baseUrl, payload)
    return response.data
  }

  async applyTemplate(templateId, pageId) {
    const response = await axios.post(`${this.baseUrl}/${templateId}/apply`, { page_id: pageId })
    return response.data
  }

  async deleteTemplate(templateId) {
    const response = await axios.delete(`${this.baseUrl}/${templateId}`)
    return response.data
  }

  async getTemplateCategories() {
    const response = await axios.get(`${this.baseUrl}/categories`)
    return response.data
  }
}