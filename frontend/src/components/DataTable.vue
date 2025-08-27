<template>
  <div class="data-table">
    <div v-if="title || $slots.header" class="data-table__header">
      <div class="data-table__title">
        <h3 v-if="title">{{ title }}</h3>
        <slot name="header" />
      </div>
      <div v-if="$slots.actions" class="data-table__actions">
        <slot name="actions" />
      </div>
    </div>

    <div class="data-table__content">
      <div v-if="loading" class="data-table__loading">
        <div class="loading-spinner"></div>
        <p>Memuat data...</p>
      </div>

      <div v-else-if="data.length === 0" class="data-table__empty">
        <div class="empty-icon">📄</div>
        <h4>{{ emptyTitle || 'Tidak ada data' }}</h4>
        <p>{{ emptyMessage || 'Belum ada data yang tersedia untuk ditampilkan.' }}</p>
      </div>

      <div v-else class="data-table__wrapper">
        <table class="data-table__table">
          <thead>
            <tr>
              <th 
                v-for="column in columns" 
                :key="column.key"
                :class="['data-table__th', column.align && `text-${column.align}`]"
                @click="column.sortable && handleSort(column.key)"
              >
                <div class="data-table__th-content">
                  {{ column.label }}
                  <span v-if="column.sortable" class="sort-icon">
                    <span v-if="sortBy === column.key">
                      {{ sortOrder === 'asc' ? '↑' : '↓' }}
                    </span>
                    <span v-else>↕</span>
                  </span>
                </div>
              </th>
              <th v-if="$slots.actions || showActions" class="data-table__th text-center">
                Aksi
              </th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="(item, index) in sortedData" 
              :key="getRowKey(item, index)"
              class="data-table__tr"
            >
              <td 
                v-for="column in columns" 
                :key="column.key"
                :class="['data-table__td', column.align && `text-${column.align}`]"
              >
                <slot 
                  :name="`cell-${column.key}`" 
                  :item="item" 
                  :value="getNestedValue(item, column.key)"
                  :index="index"
                >
                  {{ formatCellValue(getNestedValue(item, column.key), column) }}
                </slot>
              </td>
              <td v-if="$slots.actions || showActions" class="data-table__td text-center">
                <slot name="actions" :item="item" :index="index">
                  <div class="action-buttons">
                    <button 
                      v-if="showView"
                      @click="$emit('view', item)"
                      class="btn btn--sm btn--outline"
                      title="Lihat Detail"
                    >
                      👁️
                    </button>
                    <button 
                      v-if="showEdit"
                      @click="$emit('edit', item)"
                      class="btn btn--sm btn--primary"
                      title="Edit"
                    >
                      ✏️
                    </button>
                    <button 
                      v-if="showDelete"
                      @click="$emit('delete', item)"
                      class="btn btn--sm btn--danger"
                      title="Hapus"
                    >
                      🗑️
                    </button>
                  </div>
                </slot>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="showPagination && totalPages > 1" class="data-table__pagination">
        <button 
          @click="goToPage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="btn btn--sm btn--outline"
        >
          Sebelumnya
        </button>
        
        <div class="pagination-info">
          Halaman {{ currentPage }} dari {{ totalPages }}
        </div>
        
        <button 
          @click="goToPage(currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="btn btn--sm btn--outline"
        >
          Selanjutnya
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'

interface Column {
  key: string
  label: string
  sortable?: boolean
  align?: 'left' | 'center' | 'right'
  format?: 'currency' | 'date' | 'datetime' | 'number' | 'percentage'
}

interface Props {
  title?: string
  data: any[]
  columns: Column[]
  loading?: boolean
  emptyTitle?: string
  emptyMessage?: string
  showActions?: boolean
  showView?: boolean
  showEdit?: boolean
  showDelete?: boolean
  showPagination?: boolean
  pageSize?: number
  rowKey?: string | ((item: any) => string)
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  showActions: false,
  showView: false,
  showEdit: false,
  showDelete: false,
  showPagination: false,
  pageSize: 10,
  rowKey: 'id'
})

defineEmits<{
  view: [item: any]
  edit: [item: any]
  delete: [item: any]
  'page-change': [page: number]
}>()

const sortBy = ref<string>('')
const sortOrder = ref<'asc' | 'desc'>('asc')
const currentPage = ref(1)

const sortedData = computed(() => {
  let result = [...props.data]
  
  if (sortBy.value) {
    result.sort((a, b) => {
      const aVal = getNestedValue(a, sortBy.value)
      const bVal = getNestedValue(b, sortBy.value)
      
      if (aVal < bVal) return sortOrder.value === 'asc' ? -1 : 1
      if (aVal > bVal) return sortOrder.value === 'asc' ? 1 : -1
      return 0
    })
  }
  
  if (props.showPagination) {
    const start = (currentPage.value - 1) * props.pageSize
    const end = start + props.pageSize
    result = result.slice(start, end)
  }
  
  return result
})

const totalPages = computed(() => {
  return Math.ceil(props.data.length / props.pageSize)
})

const handleSort = (key: string) => {
  if (sortBy.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = key
    sortOrder.value = 'asc'
  }
}

const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}

const getRowKey = (item: any, index: number) => {
  if (typeof props.rowKey === 'function') {
    return props.rowKey(item)
  }
  return item[props.rowKey] || index
}

const getNestedValue = (obj: any, path: string) => {
  return path.split('.').reduce((current, key) => current?.[key], obj)
}

const formatCellValue = (value: any, column: Column) => {
  if (value === null || value === undefined) return '-'
  
  switch (column.format) {
    case 'currency':
      return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
      }).format(value)
    case 'number':
      return new Intl.NumberFormat('id-ID').format(value)
    case 'percentage':
      return `${value}%`
    case 'date':
      return new Date(value).toLocaleDateString('id-ID')
    case 'datetime':
      return new Date(value).toLocaleString('id-ID')
    default:
      return value
  }
}
</script>

<style scoped>
.data-table {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

.data-table__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
}

.data-table__title h3 {
  margin: 0;
  color: #374151;
  font-weight: 600;
}

.data-table__loading,
.data-table__empty {
  padding: 60px 24px;
  text-align: center;
  color: #6b7280;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e5e7eb;
  border-top: 3px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 16px;
}

.data-table__empty h4 {
  margin: 0 0 8px 0;
  color: #374151;
}

.data-table__empty p {
  margin: 0;
  color: #6b7280;
}

.data-table__wrapper {
  overflow-x: auto;
}

.data-table__table {
  width: 100%;
  border-collapse: collapse;
}

.data-table__th {
  padding: 16px 24px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
  cursor: pointer;
  user-select: none;
}

.data-table__th:hover {
  background: #f3f4f6;
}

.data-table__th-content {
  display: flex;
  align-items: center;
  gap: 8px;
}

.sort-icon {
  opacity: 0.5;
  font-size: 0.875rem;
}

.data-table__td {
  padding: 16px 24px;
  border-bottom: 1px solid #f3f4f6;
  vertical-align: middle;
}

.data-table__tr:hover {
  background: #f9fafb;
}

.text-left { text-align: left; }
.text-center { text-align: center; }
.text-right { text-align: right; }

.action-buttons {
  display: flex;
  gap: 8px;
  justify-content: center;
}

.btn {
  padding: 6px 12px;
  border-radius: 6px;
  font-weight: 500;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.2s;
  font-size: 0.875rem;
}

.btn--sm {
  padding: 4px 8px;
  font-size: 0.75rem;
}

.btn--outline {
  background: white;
  border-color: #d1d5db;
  color: #374151;
}

.btn--outline:hover {
  background: #f9fafb;
}

.btn--primary {
  background: #667eea;
  color: white;
}

.btn--primary:hover {
  background: #5a67d8;
}

.btn--danger {
  background: #ef4444;
  color: white;
}

.btn--danger:hover {
  background: #dc2626;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.data-table__pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
}

.pagination-info {
  font-size: 0.875rem;
  color: #6b7280;
}
</style>