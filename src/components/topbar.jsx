import React from 'react'
import { Save, Eye, Layout, ChevronRight, Undo, Redo } from 'lucide-react'

export default function Topbar({ onSave, onPreview, pageTitle, saving }) {
  return (
    <header style={{
      height: '56px',
      background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      padding: '0 20px',
      color: 'white',
      boxShadow: '0 2px 10px rgba(0,0,0,0.1)'
    }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
        <Layout size={24} />
        <h1 style={{ fontSize: '1.25rem', fontWeight: '600', margin: 0 }}>
          Theme Builder 4.0
        </h1>
        {pageTitle && (
          <>
            <ChevronRight size={16} style={{ opacity: 0.6 }} />
            <span style={{ opacity: 0.9 }}>{pageTitle}</span>
          </>
        )}
      </div>

      <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
        <button
          onClick={() => {}}
          style={{
            background: 'rgba(255,255,255,0.1)',
            border: 'none',
            borderRadius: '6px',
            padding: '8px',
            cursor: 'pointer',
            color: 'white',
            display: 'flex',
            alignItems: 'center'
          }}
          title="Undo"
        >
          <Undo size={18} />
        </button>
        <button
          onClick={() => {}}
          style={{
            background: 'rgba(255,255,255,0.1)',
            border: 'none',
            borderRadius: '6px',
            padding: '8px',
            cursor: 'pointer',
            color: 'white',
            display: 'flex',
            alignItems: 'center'
          }}
          title="Redo"
        >
          <Redo size={18} />
        </button>

        <div style={{ width: '1px', height: '24px', background: 'rgba(255,255,255,0.2)' }} />

        <button
          onClick={onPreview}
          style={{
            background: 'rgba(255,255,255,0.15)',
            border: '1px solid rgba(255,255,255,0.3)',
            borderRadius: '6px',
            padding: '8px 16px',
            cursor: 'pointer',
            color: 'white',
            display: 'flex',
            alignItems: 'center',
            gap: '6px',
            fontWeight: '500'
          }}
        >
          <Eye size={18} />
          Preview
        </button>

        <button
          onClick={onSave}
          disabled={saving}
          style={{
            background: 'white',
            border: 'none',
            borderRadius: '6px',
            padding: '8px 16px',
            cursor: saving ? 'wait' : 'pointer',
            color: '#667eea',
            display: 'flex',
            alignItems: 'center',
            gap: '6px',
            fontWeight: '600',
            opacity: saving ? 0.7 : 1
          }}
        >
          <Save size={18} />
          {saving ? 'Saving...' : 'Save'}
        </button>
      </div>
    </header>
  )
}
