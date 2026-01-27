import React, { useState, useEffect } from 'react'
import { Settings } from 'lucide-react'
import Topbar from './topbar'
import Sidebar from './sidebar'
import Canvas from './canvas'

// Get CMS config from window
const config = window.CMS_CONFIG || {}

// Properties Panel Component
function PropertiesPanel({ selectedBlock, blocks }) {
  const block = blocks.find(b => b.id === selectedBlock)

  if (!block) {
    return (
      <aside style={{
        width: '280px',
        background: '#fafafa',
        borderLeft: '1px solid #eee',
        padding: '16px',
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        color: '#888'
      }}>
        <Settings size={32} style={{ marginBottom: '12px', opacity: 0.5 }} />
        <p style={{ margin: 0, textAlign: 'center', fontSize: '0.875rem' }}>
          Select a block to edit its properties
        </p>
      </aside>
    )
  }

  return (
    <aside style={{
      width: '280px',
      background: '#fafafa',
      borderLeft: '1px solid #eee',
      padding: '0'
    }}>
      <div style={{
        padding: '16px',
        borderBottom: '1px solid #eee',
        fontWeight: '600',
        textTransform: 'capitalize'
      }}>
        {block.type} Settings
      </div>
      <div style={{ padding: '16px' }}>
        <label style={{ display: 'block', marginBottom: '8px', fontSize: '0.875rem', color: '#666' }}>
          Block ID
        </label>
        <input
          type="text"
          value={block.id}
          readOnly
          style={{
            width: '100%',
            padding: '8px 12px',
            border: '1px solid #ddd',
            borderRadius: '6px',
            fontSize: '0.875rem',
            background: '#f5f5f5',
            boxSizing: 'border-box'
          }}
        />

        <label style={{ display: 'block', marginTop: '16px', marginBottom: '8px', fontSize: '0.875rem', color: '#666' }}>
          Type
        </label>
        <input
          type="text"
          value={block.type}
          readOnly
          style={{
            width: '100%',
            padding: '8px 12px',
            border: '1px solid #ddd',
            borderRadius: '6px',
            fontSize: '0.875rem',
            background: '#f5f5f5',
            boxSizing: 'border-box'
          }}
        />
      </div>
    </aside>
  )
}

// Main App Component
export default function App() {
  const [blocks, setBlocks] = useState([])
  const [selectedBlock, setSelectedBlock] = useState(null)
  const [saving, setSaving] = useState(false)
  const [pageData, setPageData] = useState(null)

  // Load page data if pageId provided
  useEffect(() => {
    if (config.pageId) {
      fetch(`${config.apiBase}/api/page/${config.pageId}`, {
        headers: {
          'X-CSRF-Token': config.csrfToken
        }
      })
        .then(res => res.json())
        .then(data => {
          setPageData(data)
          if (data.blocks) {
            setBlocks(data.blocks)
          }
        })
        .catch(err => console.error('Failed to load page:', err))
    }
  }, [])

  const handleDragStart = (e, block) => {
    e.dataTransfer.setData('blockType', block.id)
  }

  const handleDrop = (blockType) => {
    const newBlock = {
      id: `block-${Date.now()}`,
      type: blockType,
      content: {}
    }
    setBlocks([...blocks, newBlock])
  }

  const handleAddSection = () => {
    // Add a container block when "Add Section" is clicked
    const newBlock = {
      id: `block-${Date.now()}`,
      type: 'container',
      content: {}
    }
    setBlocks([...blocks, newBlock])
  }

  const handleSave = async () => {
    setSaving(true)
    try {
      const response = await fetch(`${config.apiBase}/api/page/save`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': config.csrfToken
        },
        body: JSON.stringify({
          pageId: config.pageId,
          blocks: blocks
        })
      })
      const result = await response.json()
      if (!result.success) {
        throw new Error(result.error || 'Save failed')
      }
    } catch (err) {
      console.error('Save failed:', err)
      alert('Failed to save: ' + err.message)
    } finally {
      setSaving(false)
    }
  }

  const handlePreview = () => {
    // Open preview in new tab
    if (config.pageId) {
      window.open(`/preview/${config.pageId}`, '_blank')
    } else {
      alert('Save the page first to preview')
    }
  }

  return (
    <div style={{
      height: '100vh',
      display: 'flex',
      flexDirection: 'column',
      fontFamily: "'Inter', system-ui, sans-serif"
    }}>
      <Topbar
        onSave={handleSave}
        onPreview={handlePreview}
        pageTitle={pageData?.title || (config.pageId ? 'Loading...' : 'New Page')}
        saving={saving}
      />
      <div style={{ display: 'flex', flex: 1, overflow: 'hidden' }}>
        <Sidebar onDragStart={handleDragStart} />
        <Canvas
          blocks={blocks}
          onDrop={handleDrop}
          onSelectBlock={setSelectedBlock}
          selectedBlock={selectedBlock}
          onAddSection={handleAddSection}
        />
        <PropertiesPanel
          selectedBlock={selectedBlock}
          blocks={blocks}
        />
      </div>
    </div>
  )
}
