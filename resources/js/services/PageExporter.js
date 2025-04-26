export class PageExporter {
  static EXPORT_FORMATS = {
    HTML: 'html',
    JSON: 'json',
    CMS_FORMAT: 'cms'
  }

  constructor(pageData) {
    this.pageData = pageData
  }

  /**
   * Exports page in the requested format
   * @param {string} format - Export format (html/json/cms)
   * @returns {Promise<string>} - The exported page
   */
  async export(format = EXPORT_FORMATS.HTML) {
    switch(format) {
      case PageExporter.EXPORT_FORMATS.HTML:
        return this.generateHTML(this.pageData)
      case PageExporter.EXPORT_FORMATS.JSON:
        return this.generateJSON(this.pageData)
      case PageExporter.EXPORT_FORMATS.CMS_FORMAT:
        return this.generateCMSFormat(this.pageData)
      default:
        throw new Error(`Unsupported export format: ${format}`)
    }
  }

  // HTML exporter implemention  
  async generateHTML() {
    const metaTags = `
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>${this.pageData.title || 'Generated Page'}</title>
    `

    const stylesheets = this.generateCSSLinks()
    const scripts = this.generateScripts()
    const content = this.processComponentsToHTML()

    return `
      <!DOCTYPE html>
      <html lang="en">
        <head>
          ${metaTags}
          ${stylesheets}
        </head>
        <body>
          ${content}
          ${scripts}
        </body>
      </html>
    `
  }

  generateJSON() {
    // Redact internal/developer-only fields
    const { revisions, draftState, ...publishableState } = {
      ...this.pageData,
      publishedAt: new Date().toISOString(),
      exportVersion: this.pageData.version
    }
    
    return JSON.stringify(publishableState, null, 2) 
  }

  async generateCMSFormat() {
    // Convert to format consumable by headless CMS REST API
    const packages = this.processDependencies()
    const componentData = this.transformForAPI()

    return {
      configVersion: '2.4', // CMS config version
      exportedAt: new Date().toISOString(),
      title: this.pageData.title,
      npm_dependencies: packages,
      content: componentData
    }
  }

  // Helper methods
  generateCSSLinks() {
    return [...new Set(this.pageData.stylesheets)]
      .map(link => `<link href="${link}" rel="stylesheet">`)
      .join('\n')
  }

  generateScripts() {
    return [...new Set(this.pageData.scripts)]
      .map(script => `<script src="${script}"></script>`)
      .join('\n')
  }

  processComponentsToHTML() {
    let htmlFrontend = ''
    
    this.pageData.rows?.forEach(row => {
      let columnsHTML = ''
      
      row.columns?.forEach(col => {
        columnsHTML += `
          <div class="col" ${this.generateColumnAttributes(col)}>
            ${col.content || this.processComponents(col.name)}
          </div>
        `
      })

      htmlFrontend += `<div class="row">${columnsHTML}</div>`
    })

    return htmlFrontend
  }

  processComponents(componentType) {
    const componentHandlers = {
      HeroBanner: () => this.generateHeroHTML(),
      TextBlock: (data) => `{/* Text component */}`,
      Gallery: () => this.generateGalleryHTML()
    }

    return componentHandlers[componentType]?.() || ''
  }

  patternWarning(details) {
    console.warn('Unsupported pattern detected:', details)
    return `<div class="error-export">Export error:\n${JSON.stringify(details)}</div>`
  }
}

// Utilities only used by PageExporter
function convertComponentLifecycle(eventName) {
  switch(eventName) {
    case 'firstRender': return 1
    case 'variableChange': return 2
    case 'destroy': return 7
    default: return 0
  }
}

function logicTooltip(type) {
  const tooltips = {
    loop: 'Circuit protection may apply for async loops',
    transform: 'Special transforms for API payloads',
    computed: "Read-only derived types don't serialize"
  }
  
  return tooltips[type] || ''
}