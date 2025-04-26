export const useGptSuggestion = async (prompt) => {
  try {
    const response = await fetch('/api/ai/generate-block', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        prompt,
        options: {
          format: 'html',
          context: 'cms_block'
        }
      })
    })

    if (!response.ok) {
      throw new Error('AI service failed')
    }

    const data = await response.json()
    return data.suggestions || []
  } catch (error) {
    console.error('AI Generation Error:', error)
    return []
  }
}

export const analyzeContentStructure = (content) => {
  return {
    elements: {
      headers: (content.match(/<h[1-6][^>]*>.*?<\/h[1-6]>/g) || []).length,
      images: (content.match(/<img[^>]+>/g) || []).length,
      links: (content.match(/<a[^>]+href=['"][^'"]+['"][^>]*>/g) || []).length
    },
    complexityScore: Math.min(
      10,
      Math.floor(
        content.length / 500 +
        (content.split(' ').length / 100)
      )
    )
  }
}