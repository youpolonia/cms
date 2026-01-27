import React, { useEffect, useState, useRef } from 'react';
import { createApp, h } from 'vue';
import { useVersionComparison } from './version-comparison';

const VersionComparisonReact = ({ versions }) => {
  const [vueApp, setVueApp] = useState(null);
  const [container, setContainer] = useState(null);
  const [activeIndex, setActiveIndex] = useState(-1);
  const containerRef = useRef(null);

  const handleKeyDown = (e) => {
    if (!containerRef.current) return;
    
    const items = containerRef.current.querySelectorAll('.version-item');
    if (!items.length) return;

    switch(e.key) {
      case 'ArrowDown':
        e.preventDefault();
        setActiveIndex(prev => Math.min(prev + 1, items.length - 1));
        break;
      case 'ArrowUp':
        e.preventDefault();
        setActiveIndex(prev => Math.max(prev - 1, 0));
        break;
      case 'Enter':
      case ' ':
        if (activeIndex >= 0) {
          e.preventDefault();
          items[activeIndex].click();
        }
        break;
    }
  };

  useEffect(() => {
    if (!container) return;

    // Create Vue app instance
    const app = createApp({
      setup() {
        const { comparisonResult, diffNavigation } = useVersionComparison();

        return {
          comparisonResult,
          diffNavigation
        };
      },
      render() {
        return h('div', {
          class: 'version-comparison-container',
          'aria-live': 'polite'
        }, [
          h('div', {
            class: 'version-list',
            role: 'listbox',
            'aria-label': 'Content versions'
          },
            versions.map((version, index) =>
              h('div', {
                class: `version-item ${version.isCurrent ? 'current' : ''} ${index === activeIndex ? 'active' : ''}`,
                'aria-selected': version.isCurrent || index === activeIndex,
                role: 'option',
                tabIndex: index === activeIndex ? 0 : -1,
                'aria-posinset': index + 1,
                'aria-setsize': versions.length,
                onClick: () => setActiveIndex(index)
              }, [
                h('span', { class: 'version-date' }, version.date),
                h('span', { class: 'version-author' }, version.author)
              ])
            )
          ),
          h('button', {
            class: 'compare-button',
            onClick: () => console.log('Compare clicked'),
            'aria-label': 'Compare selected versions',
            'aria-disabled': activeIndex === -1
          }, 'Compare')
        ]);
      }
    });

    // Mount Vue app inside React component
    app.mount(container);
    setVueApp(app);

    return () => {
      if (vueApp) {
        vueApp.unmount();
      }
    };
  }, [container, versions]);

  return (
    <div 
      ref={setContainer}
      role="region"
      aria-label="Version comparison"
    />
  );
};

export default VersionComparisonReact;