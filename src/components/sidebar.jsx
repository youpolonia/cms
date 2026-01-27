import React, { useState } from 'react'
import {
  Type, Image, MousePointerClick, Minus, MessageSquare, 
  Award, Megaphone, Layout, Columns, Menu, X
} from 'lucide-react'

// Block types available in sidebar
const BLOCK_TYPES = [
  { id: 'text', icon: Type, label: 'Text', category: 'Content' },
  { id: 'image', icon: Image, label: 'Image', category: 'Content' },
  { id: 'button', icon: MousePointerClick, label: 'Button', category: 'Content' },
  { id: 'divider', icon: Minus, label: 'Divider', category: 'Content' },
  { id: 'blurb', icon: MessageSquare, label: 'Blurb', category: 'Content' },
  { id: 'hero', icon: Award, label: 'Hero', category: 'Content' },
  { id: 'cta', icon: Megaphone, label: 'CTA', category: 'Content' },
  { id: 'section', icon: Layout, label: 'Section', category: 'Layout' },
  { id: 'columns', icon: Columns, label: 'Row', category: 'Layout' },
]

export default function Sidebar({ onDragStart }) {
  const [collapsed, setCollapsed] = useState(false)

  return (
    <aside style={{
      width: collapsed ? '48px' : '260px',
      background: '#1e1e2e',
      borderRight: '1px solid #2d2d3d',
      display: 'flex',
      flexDirection: 'column',
      transition: 'width 0.2s ease'
    }}>
      <div style={{
        padding: '16px',
        borderBottom: '1px solid #2d2d3d',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between'
      }}>
        {!collapsed && (
          <span style={{ color: 'white', fontWeight: '600' }}>Blocks</span>
        )}
        <button
          onClick={() => setCollapsed(!collapsed)}
          style={{
            background: 'transparent',
            border: 'none',
            padding: '4px',
            cursor: 'pointer',
            color: '#888'
          }}
        >
          {collapsed ? <Menu size={20} /> : <X size={20} />}
        </button>
      </div>

      <div style={{
        padding: collapsed ? '8px' : '12px',
        flex: 1,
        overflowY: 'auto'
      }}>
        {BLOCK_TYPES.map(block => (
          <div
            key={block.id}
            draggable
            onDragStart={(e) => onDragStart(e, block)}
            style={{
              display: 'flex',
              alignItems: 'center',
              gap: '12px',
              padding: collapsed ? '10px' : '10px 12px',
              marginBottom: '4px',
              background: '#2d2d3d',
              borderRadius: '8px',
              cursor: 'grab',
              color: '#ddd',
              justifyContent: collapsed ? 'center' : 'flex-start',
              transition: 'all 0.15s ease'
            }}
            onMouseOver={(e) => {
              e.currentTarget.style.background = '#3d3d4d'
              e.currentTarget.style.transform = 'translateX(4px)'
            }}
            onMouseOut={(e) => {
              e.currentTarget.style.background = '#2d2d3d'
              e.currentTarget.style.transform = 'translateX(0)'
            }}
          >
            <block.icon size={18} style={{ flexShrink: 0, color: '#667eea' }} />
            {!collapsed && <span style={{ fontSize: '0.875rem' }}>{block.label}</span>}
          </div>
        ))}
      </div>
    </aside>
  )
}
