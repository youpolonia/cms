/**
 * Page Builder Configuration Validator
 * Ensures component configurations meet requirements before saving/publishing
 */
export class ConfigValidator {
  static VALIDATION_PROFILES = {
    DRAFT: 'draft',
    PUBLISH: 'publish',
    IMPORT: 'import'
  }

  #validators = {
    componentStructure: this.validateComponentStructure.bind(this),
    contentConstraints: this.validateContentConstraints.bind(this),
    styleRules: this.validateStyleRules.bind(this),
    assetReferences: this.validateAssetReferences.bind(this),
    securityRules: this.validateSecurityRules.bind(this)
  }

  constructor(pageData) {
    this.pageData = pageData
    this.errors = new Map()
  }

  /**
   * Run validation against specified profile
   * @param {string} profile - Validation profile (draft/publish/import)
   * @returns {boolean} Whether config is valid
   */
  async validate(profile = ConfigValidator.VALIDATION_PROFILES.DRAFT) {
    this.errors.clear()
    const isStrict = profile === ConfigValidator.VALIDATION_PROFILES.PUBLISH

    // Run all validator functions in parallel
    await Promise.all(Object.entries(this.#validators).map(
      async ([name, validator]) => {
        try {
          await validator()
        } catch (error) {
          this.errors.set(name, error.message)
          if (isStrict) throw error
        }
      }
    ))

    return this.errors.size === 0
  }

  validateComponentStructure() {
    // Required root properties
    const requiredRoot = ['version', 'title', 'components']
    missingProperties(requiredRoot, this.pageData)

    // Validate each component's structure
    this.pageData.components.forEach(comp => {
      if (!comp.type) throw new Error('Component missing type')
      if (!comp.id) throw new Error('Component missing id')

      // Type-specific validations 
      switch(comp.type) {
        case 'HeroBanner':
          requiredProperties(['heading', 'cta'], comp.config)
          break
        case 'Gallery':
          if (!comp.config.items || comp.config.items.length === 0) {
            throw new Error('Gallery requires at least one item')
          }
          break
        // ... other component types
      }
    })

    function missingProperties(required, target) {
      const missing = required.filter(prop => !(prop in target))
      if (missing.length > 0) {
        throw new Error(`Missing required properties: ${missing.join(', ')}`)
      }
    }
  }

  async validateContentConstraints() {
    // Check content length limits
    const contentValidations = this.pageData.components.map(comp => {
      if (comp.type === 'TextBlock' && comp.config.text) {
        const text = stripTags(comp.config.text)
        if (text.length > 5000) {
          throw new Error(`TextBlock content exceeds 5000 character limit`)
        }
      }
    })
    await Promise.all(contentValidations)

    function stripTags(html) {
      const doc = new DOMParser().parseFromString(html, 'text/html')
      return doc.body.textContent || ''
    }
  }

  validateStyleRules() {
    // Validate responsive breakpoints
    const breakpoints = this.pageData.styles?.breakpoints
    if (breakpoints) {
      validateBreakpointOrder(Object.keys(breakpoints))
    }

    function validateBreakpointOrder(keys) {
      const ordered = [...keys].sort((a, b) => parseInt(a) - parseInt(b))
      if (keys.join() !== ordered.join()) {
        throw new Error('Breakpoints must be ordered from small to large')
      }
    }
  }

  validateAssetReferences() {
    // Check all referenced assets exist and are valid
    const assetMap = new Map(this.pageData.assets?.map(a => [a.id, a]))
    
    this.pageData.components.forEach(comp => {
      if (comp.type === 'Image' && comp.config.assetId) {
        if (!assetMap.has(comp.config.assetId)) {
          throw new Error(`Referenced asset ${comp.config.assetId} not found`)
        }
      }
    })
  }

  validateSecurityRules() {
    // Sanitize any potentially dangerous inputs
    this.pageData.components.forEach(comp => {
      if (comp.config?.html) {
        const suspicious = [
          { pattern: /on(load|error|click)\s*=/i, reason: 'Event handler' },
          { pattern: /javascript:/i, reason: 'JS protocol' }
        ]
        
        suspicious.forEach(({ pattern, reason }) => {
          if (pattern.test(comp.config.html)) {
            throw new Error(`Potential security issue: ${reason}`)
          }
        })
      }
    })
  }

  getValidationErrors() {
    return Object.fromEntries(this.errors)
  }
}