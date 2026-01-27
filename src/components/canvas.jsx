import React, { useState } from 'react'
import { Layout, Image, MessageSquare } from 'lucide-react'

export default function Canvas({ blocks, onDrop, onSelectBlock, selectedBlock, onAddSection }) {
  const [dragOver, setDragOver] = useState(false)

  const handleDragOver = (e) => {
    e.preventDefault()
    setDragOver(true)
  }

  const handleDragLeave = () => {
    setDragOver(false)
  }

  const handleDrop = (e) => {
    e.preventDefault()
    setDragOver(false)
    const blockType = e.dataTransfer.getData('blockType')
    if (blockType) {
      onDrop(blockType)
    }
  }

  return (
    <main
      style={{
        flex: 1,
        background: '#f0f2f5',
        padding: '24px',
        overflowY: 'auto',
        display: 'flex',
        justifyContent: 'center'
      }}
      onDragOver={handleDragOver}
      onDragLeave={handleDragLeave}
      onDrop={handleDrop}
    >
      <div style={{
        width: '100%',
        maxWidth: '900px',
        minHeight: '600px',
        background: 'white',
        borderRadius: '12px',
        boxShadow: dragOver
          ? '0 0 0 3px #667eea, 0 4px 20px rgba(0,0,0,0.1)'
          : '0 4px 20px rgba(0,0,0,0.08)',
        padding: '32px',
        transition: 'box-shadow 0.2s ease'
      }}>
        {blocks.length === 0 ? (
          <div style={{
            height: '100%',
            minHeight: '400px',
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            justifyContent: 'center',
            color: '#333'
          }}>
            <Layout size={64} style={{ marginBottom: '24px', color: '#667eea', opacity: 0.8 }} />
            <h2 style={{
              fontSize: '1.75rem',
              margin: '0 0 12px 0',
              fontWeight: '600',
              color: '#1a1a1a'
            }}>
              Start Building Your Page
            </h2>
            <p style={{
              fontSize: '1rem',
              opacity: 0.7,
              marginBottom: '32px',
              textAlign: 'center',
              maxWidth: '400px'
            }}>
              Create beautiful, responsive pages with our intuitive drag-and-drop builder
            </p>
            <button
              onClick={onAddSection}
              style={{
                background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                border: 'none',
                borderRadius: '8px',
                padding: '14px 32px',
                cursor: 'pointer',
                color: 'white',
                fontSize: '1rem',
                fontWeight: '600',
                display: 'flex',
                alignItems: 'center',
                gap: '8px',
                boxShadow: '0 4px 14px rgba(102, 126, 234, 0.4)',
                transition: 'all 0.2s ease'
              }}
              onMouseOver={(e) => {
                e.currentTarget.style.transform = 'translateY(-2px)'
                e.currentTarget.style.boxShadow = '0 6px 20px rgba(102, 126, 234, 0.5)'
              }}
              onMouseOut={(e) => {
                e.currentTarget.style.transform = 'translateY(0)'
                e.currentTarget.style.boxShadow = '0 4px 14px rgba(102, 126, 234, 0.4)'
              }}
            >
              <Layout size={20} />
              Add Section
            </button>
          </div>
        ) : (
          <div>
            {blocks.map((block, index) => (
              <div
                key={block.id}
                onClick={() => onSelectBlock(block.id)}
                style={{
                  padding: '16px',
                  marginBottom: '12px',
                  border: selectedBlock === block.id
                    ? '2px solid #667eea'
                    : '1px solid #eee',
                  borderRadius: '8px',
                  cursor: 'pointer',
                  transition: 'all 0.15s ease'
                }}
              >
                {block.type === 'heading' && (
                  <h2 style={{ margin: 0 }}>New Heading</h2>
                )}
                {block.type === 'text' && (
                  <p style={{ margin: 0, color: '#666' }}>
                    Click to edit this text block. Add your content here.
                  </p>
                )}
                {block.type === 'image' && (
                  <div style={{
                    background: '#f5f5f5',
                    padding: '32px',
                    textAlign: 'center',
                    borderRadius: '4px'
                  }}>
                    <Image size={32} style={{ color: '#999' }} />
                    <p style={{ margin: '8px 0 0 0', color: '#888', fontSize: '0.875rem' }}>
                      Click to add image
                    </p>
                  </div>
                )}
                {block.type === 'columns' && (
                  <div style={{ display: 'flex', gap: '16px' }}>
                    <div style={{ flex: 1, background: '#f9f9f9', padding: '24px', borderRadius: '4px', textAlign: 'center', color: '#888' }}>
                      Column 1
                    </div>
                    <div style={{ flex: 1, background: '#f9f9f9', padding: '24px', borderRadius: '4px', textAlign: 'center', color: '#888' }}>
                      Column 2
                    </div>
                  </div>
                )}
                {block.type === 'container' && (
                  <div style={{ background: '#f9f9f9', padding: '24px', borderRadius: '4px', textAlign: 'center', color: '#888' }}>
                    Container - Drop blocks here
                  </div>
                )}
                {block.type === 'list' && (
                  <ul style={{ margin: 0, paddingLeft: '20px', color: '#666' }}>
                    <li>List item 1</li>
                    <li>List item 2</li>
                    <li>List item 3</li>
                  </ul>
                )}
                {block.type === 'code' && (
                  <pre style={{
                    background: '#1e1e2e',
                    color: '#ddd',
                    padding: '16px',
                    borderRadius: '4px',
                    margin: 0,
                    fontSize: '0.875rem'
                  }}>
                    {'// Your code here\nconsole.log("Hello World");'}
                  </pre>
                )}
                {block.type === 'spacer' && (
                  <div style={{
                    height: '48px',
                    background: '#f5f5f5',
                    borderRadius: '4px',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    color: '#999',
                    fontSize: '0.75rem'
                  }}>
                    SPACER
                  </div>
                )}
                {block.type === 'button' && (
                  <div style={{ textAlign: 'center' }}>
                    <button style={{
                      background: '#667eea',
                      color: 'white',
                      border: 'none',
                      padding: '12px 24px',
                      borderRadius: '6px',
                      fontSize: '1rem',
                      fontWeight: '500',
                      cursor: 'pointer'
                    }}>
                      Click Here
                    </button>
                  </div>
                )}
                {block.type === 'divider' && (
                  <hr style={{
                    border: 'none',
                    borderTop: '1px solid #ddd',
                    margin: '20px 0'
                  }} />
                )}
                {block.type === 'blurb' && (
                  <div style={{
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    textAlign: 'center',
                    padding: '20px'
                  }}>
                    <div style={{
                      width: '48px',
                      height: '48px',
                      background: '#667eea',
                      borderRadius: '50%',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      marginBottom: '16px'
                    }}>
                      <MessageSquare size={24} color="white" />
                    </div>
                    <h3 style={{ margin: '0 0 8px 0', fontSize: '1.25rem' }}>Feature Title</h3>
                    <p style={{ margin: 0, color: '#666', fontSize: '0.875rem' }}>
                      Feature description goes here.
                    </p>
                  </div>
                )}
                {block.type === 'section' && (
                  <div style={{
                    background: '#f9f9f9',
                    padding: '32px',
                    borderRadius: '8px',
                    border: '2px dashed #ddd',
                    textAlign: 'center',
                    color: '#888'
                  }}>
                    Section - Drop blocks here
                  </div>
                )}
              </div>
            ))}
          </div>
        )}
      </div>
    </main>
  )
}
