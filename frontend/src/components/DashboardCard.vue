<template>
  <div :class="['dashboard-card', `dashboard-card--${variant}`]">
    <div v-if="$slots.icon || icon" class="dashboard-card__icon">
      <slot name="icon">
        <component :is="icon" v-if="icon" />
        <span v-else class="dashboard-card__icon-text">{{ iconText }}</span>
      </slot>
    </div>
    
    <div class="dashboard-card__content">
      <div class="dashboard-card__header">
        <h3 class="dashboard-card__title">{{ title }}</h3>
        <span v-if="badge" class="dashboard-card__badge" :class="`badge--${badgeType}`">
          {{ badge }}
        </span>
      </div>
      
      <div v-if="value !== undefined" class="dashboard-card__value">
        {{ formattedValue }}
      </div>
      
      <div v-if="description" class="dashboard-card__description">
        {{ description }}
      </div>
      
      <div v-if="$slots.default" class="dashboard-card__body">
        <slot />
      </div>
      
      <div v-if="$slots.actions || showViewMore" class="dashboard-card__actions">
        <slot name="actions">
          <button v-if="showViewMore" @click="$emit('view-more')" class="btn btn--link">
            Lihat Selengkapnya
          </button>
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  title: string
  value?: string | number
  description?: string
  icon?: string
  iconText?: string
  badge?: string
  badgeType?: 'success' | 'warning' | 'error' | 'info'
  variant?: 'default' | 'primary' | 'success' | 'warning' | 'error'
  showViewMore?: boolean
  format?: 'number' | 'currency' | 'percentage'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  badgeType: 'info',
  format: 'number'
})

defineEmits<{
  'view-more': []
}>()

const formattedValue = computed(() => {
  if (props.value === undefined) return ''
  
  switch (props.format) {
    case 'currency':
      return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
      }).format(Number(props.value))
    case 'percentage':
      return `${props.value}%`
    case 'number':
    default:
      return new Intl.NumberFormat('id-ID').format(Number(props.value))
  }
})
</script>

<style scoped>
.dashboard-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
  transition: all 0.2s;
  display: flex;
  gap: 16px;
}

.dashboard-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.dashboard-card--primary {
  border-color: #667eea;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.dashboard-card--success {
  border-color: #059669;
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  color: white;
}

.dashboard-card--warning {
  border-color: #d97706;
  background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
  color: white;
}

.dashboard-card--error {
  border-color: #dc2626;
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  color: white;
}

.dashboard-card__icon {
  flex-shrink: 0;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  font-size: 1.5rem;
}

.dashboard-card--default .dashboard-card__icon {
  background: #f3f4f6;
  color: #374151;
}

.dashboard-card__icon-text {
  font-size: 1.5rem;
  font-weight: 600;
}

.dashboard-card__content {
  flex: 1;
  min-width: 0;
}

.dashboard-card__header {
  display: flex;
  justify-content: between;
  align-items: flex-start;
  margin-bottom: 8px;
}

.dashboard-card__title {
  font-size: 1rem;
  font-weight: 600;
  margin: 0;
  color: inherit;
}

.dashboard-card--default .dashboard-card__title {
  color: #374151;
}

.dashboard-card__badge {
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 500;
  margin-left: auto;
}

.badge--success {
  background: #dcfce7;
  color: #166534;
}

.badge--warning {
  background: #fef3c7;
  color: #92400e;
}

.badge--error {
  background: #fecaca;
  color: #991b1b;
}

.badge--info {
  background: #dbeafe;
  color: #1e40af;
}

.dashboard-card__value {
  font-size: 2rem;
  font-weight: 700;
  margin: 8px 0;
  color: inherit;
}

.dashboard-card--default .dashboard-card__value {
  color: #667eea;
}

.dashboard-card__description {
  font-size: 0.875rem;
  opacity: 0.8;
  margin-bottom: 16px;
  color: inherit;
}

.dashboard-card--default .dashboard-card__description {
  color: #6b7280;
}

.dashboard-card__body {
  margin: 16px 0;
}

.dashboard-card__actions {
  margin-top: 16px;
}

.btn {
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: 500;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn--link {
  background: none;
  color: inherit;
  text-decoration: underline;
  padding: 4px 0;
}

.dashboard-card--default .btn--link {
  color: #667eea;
}

.btn--link:hover {
  opacity: 0.8;
}
</style>