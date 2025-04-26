export default class AnimationManager {
  constructor() {
    this.animations = {
      fade: {
        enter: 'fadeIn 0.5s ease-out',
        exit: 'fadeOut 0.5s ease-out'
      },
      slide: {
        enter: 'slideIn 0.5s ease-out',
        exit: 'slideOut 0.5s ease-out'  
      },
      bounce: {
        enter: 'bounceIn 0.75s ease-out',
        exit: 'bounceOut 0.75s ease-out'
      }
    };

    this.keyframes = `
      @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
      }
      @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
      }
      @keyframes slideIn {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
      }
      @keyframes slideOut {
        from { transform: translateY(0); opacity: 1; }
        to { transform: translateY(20px); opacity: 0; }
      }
      @keyframes bounceIn {
        0% { transform: scale(0.8); opacity: 0; }
        50% { transform: scale(1.05); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
      }
      @keyframes bounceOut {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 1; }
        100% { transform: scale(0.8); opacity: 0; }
      }
    `;

    this.injectStyles();
  }

  injectStyles() {
    const style = document.createElement('style');
    style.textContent = this.keyframes;
    document.head.appendChild(style);
  }

  getAnimation(animationName, type = 'enter') {
    return this.animations[animationName]?.[type] || '';
  }

  getAvailableAnimations() {
    return Object.keys(this.animations);
  }

  addCustomAnimation(name, enterKeyframes, exitKeyframes, css = '') {
    this.animations[name] = {
      enter: `${name}In 0.5s ease-out`,
      exit: `${name}Out 0.5s ease-out`
    };

    this.keyframes += `
      @keyframes ${name}In {
        ${enterKeyframes}
      }
      @keyframes ${name}Out {
        ${exitKeyframes}
      }
      ${css}
    `;

    this.injectStyles();
  }
}