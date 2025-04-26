export const blockTemplates = {
  text: {
    width: 200,
    height: 100,
    text: 'New Text Block',
    fontSize: 16,
    color: '#000000',
    backgroundColor: 'transparent',
    textAlign: 'left'
  },
  image: {
    width: 300,
    height: 200,
    src: '',
    altText: '',
    objectFit: 'cover'
  },
  button: {
    width: 120,
    height: 40,
    text: 'Click Me',
    fontSize: 14,
    color: '#ffffff',
    backgroundColor: '#007bff',
    borderRadius: 4,
    href: '#'
  },
  columns: {
    width: 400,
    height: 300,
    columns: 2,
    gap: 20,
    columnBackgrounds: ['#ffffff', '#ffffff']
  }
}

export const blockComponents = {
  text: () => import('./blocks/TextBlock.vue'),
  image: () => import('./blocks/ImageBlock.vue'), 
  button: () => import('./blocks/ButtonBlock.vue'),
  columns: () => import('./blocks/ColumnsBlock.vue')
}

export const presets = {
  heroSection: {
    name: 'Hero Section',
    blocks: [
      {
        type: 'columns',
        x: 100,
        y: 100,
        columns: 1,
        width: 800,
        height: 400,
        columnBackgrounds: ['#f8f9fa']
      },
      {
        type: 'text',
        x: 150,
        y: 150,
        text: 'Welcome to Our Site',
        fontSize: 36,
        width: 500
      },
      {
        type: 'button',
        x: 150,
        y: 220,
        text: 'Learn More'
      }
    ]
  },
  // More presets can be added here
}