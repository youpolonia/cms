export default class GlobalStylesManager {
  constructor() {
    this.styles = {
      colors: {
        primary: '#4CAF50',
        secondary: '#2196F3',
        danger: '#F44336',
        text: '#333333',
        background: '#FFFFFF'
      },
      spacing: {
        small: '8px',
        medium: '16px', 
        large: '24px'
      },
      typography: {
        fontFamily: 'Arial, sans-serif',
        baseSize: '16px',
        headingSizes: {
          h1: '2.5rem',
          h2: '2rem',
          h3: '1.75rem'
        }
      }
    };
  }

  getStyle(path) {
    return path.split('.').reduce((obj, key) => {
      return obj && obj[key] !== undefined ? obj[key] : null;
    }, this.styles);
  }

  updateStyle(path, value) {
    const parts = path.split('.');
    const lastKey = parts.pop();
    let current = this.styles;

    for (const key of parts) {
      if (!current[key]) {
        current[key] = {};
      }
      current = current[key];
    }

    current[lastKey] = value;
    this.notifyChanges();
  }

  registerListener(callback) {
    this.listeners.push(callback);
  }

  notifyChanges() {
    this.listeners.forEach(callback => callback(this.styles));
  }

  exportStyles() {
    return JSON.parse(JSON.stringify(this.styles));
  }

  importStyles(styles) {
    this.styles = JSON.parse(JSON.stringify(styles));
    this.notifyChanges();
  }
}