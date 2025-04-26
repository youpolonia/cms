<template>
  <div class="grid-container" ref="grid">
    <template v-for="row in rows" :key="row.id">
      <div class="row" :style="{ height: `${row.height}px` }">
        <template v-for="col in row.columns" :key="col.id">
          <div 
            class="column"
            :style="{ 
              width: `${col.width}px`,
              'background-color': getColumnBgColor(col)
            }"
            @mousedown="startDrag(col.id)"
          >
            <slot name="column" :column="col" />
          </div>
        </template>
      </div>
    </template>
  </div>
</template>

<script>
import { computed, ref } from 'vue'

export default {
  props: {
    rows: {
      type: Array,
      required: true,
      default: () => []
    }
  },

  setup(props, { emit }) {
    const isDragging = ref(false)
    const draggedColumn = ref(null)
    const grid = ref(null)
    const dragOffset = ref({ x: 0, y: 0 })

    // Helper functions
    const getColumnBgColor = (col) => {
      if (col.dragging) return '#e6f3ff'
      return col.marked ? '#f0f0f0' : 'transparent' 
    }

    // Dragging handlers
    const startDrag = (colId) => {
      if (isDragging.value) return

      const colIndex = props.rows.flatMap(r => r.columns)
        .findIndex(c => c.id === colId)
      if (colIndex === -1) return

      isDragging.value = true
      draggedColumn.value = colId
      emit('column-activated', colId)
    }

    const onMouseMove = (e) => {
      if (!isDragging.value) return

      const gridRect = grid.value.getBoundingClientRect()
      const posX = e.clientX - gridRect.left - dragOffset.value.x 
      const posY = e.clientY - gridRect.top - dragOffset.value.y

      emit('column-dragged', {
        columnId: draggedColumn.value,
        position: { x: posX, y: posY }
      })
    }

    const onMouseUp = () => {
      if (!isDragging.value) return

      isDragging.value = false
      draggedColumn.value = null
      emit('column-dropped')
    }

    return {
      grid,
      isDragging,
      draggedColumn,
      dragOffset,
      getColumnBgColor,
      startDrag,
      onMouseMove,
      onMouseUp
    }
  }
}
</script>

<style scoped>
.grid-container {
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
  min-height: 500px;
}

.row {
  display: flex;
  flex-direction: row;
  margin-bottom: 10px;
}

.column {
  position: relative;
  margin-right: 10px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: move;
  transition: background-color 0.2s;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.column:hover {
  background-color: #f0f0f0!important;
}
</style>