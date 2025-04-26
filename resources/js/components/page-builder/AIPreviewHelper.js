export class AIPreviewGenerator {
  constructor(pageId) {
    this.apiEndpoint = '/api/page-preview/generate'
    this.pageId = pageId
    this.previewCache = {}

    // Binds preview settings
    this.previewLimits = {
      maxPreviewTime: 30000,
      ratioReduction: {
        mobile: 0.5,
        tablet: 0.8,
        desktop: 1.0 
      }
    }
  }

  /**
   * Generate AI-enhanced page preview
   * @param {String} mode - 'live', 'draft' or 'alternate'
   * @returns {Promise<Object>} Preview data with generated alternate metadata
   */
  async generatePreview(mode = 'live') {
    const previewKey = `${this.pageId}:${mode}`
    
    if (this.previewCache[previewKey]) {
      return this.previewCache[previewKey]
    }

    // Main API call 
    const response = await fetch(this.apiEndpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        pageId: this.pageId,
        mode 
      })
    })

    if (!response.ok) {
      throw new Error(`Preview generation failed: ${response.statusText}`)
    }

    const previewData = await response.json()
    
    // Transform for viewports
    const finalPreview = this._processForViewports(previewData)
    this.previewCache[previewKey] = finalPreview

    return finalPreview
  }

  /**
   * Generate design variations programmatically 
   */
  async generateVariations(themeGuidelines) {
    const variations = []

    for (const viewport of ['mobile', 'tablet', 'desktop']) {
      const result = await fetch(`/api/ai/variation/generate`, {
        method: 'POST',
        body: JSON.stringify({
          pageId: this.pageId,
          viewport,
          theme: themeGuidelines[viewport]
        })
      })
      variations.push(await result.json())
    }

    return variations
  }

  /**
   * Suggested components connector
   */
  async getSuggestedComponents(locale) {
    const { components } = await (await fetch(
      `/api/ai/fittings/list?locale=${locale}&pageId=${this.pageId}`
    )).json()

    return components.filter(comp => 
      comp.isFrameworkCompatible && 
      !comp.deprecated
    )
  }

  _processForViewports(rawData) {
    const result = Object.keys(this.previewLimits.ratioReduction).reduce((acc, viewport) => {
      acc[viewport] = this._scaleViewportData(rawData, viewport)
      return acc
    }, {})

    result.type = rawData.type
    result.timestamp = new Date().toISOString()

    return result
  }

  _scaleViewportData(data, viewport) {
    return {
      ...data,
      topElements: data.topElements.slice(
        0, 
        Math.floor(this.previewLimits.ratioReduction[viewport] * 25)
      ),
      keepPointerSegments: data.atomicCssTypes.map(type => 
        type.split('-').pop()
      ).filter(Boolean)   
    }
  }
}