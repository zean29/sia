<template>
  <teleport to="body">
    <div class="notification-container">
      <transition-group name="notification" tag="div">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          :class="['notification', `notification--${notification.type}`]"
        >
          <div class="notification__icon">
            <svg v-if="notification.type === 'success'" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <svg v-else-if="notification.type === 'error'" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <svg v-else-if="notification.type === 'warning'" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <svg v-else width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="notification__content">
            <h4 class="notification__title">{{ notification.title }}</h4>
            <p v-if="notification.message" class="notification__message">{{ notification.message }}</p>
          </div>
          <button
            @click="removeNotification(notification.id)"
            class="notification__close"
            type="button"
          >
            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </transition-group>
    </div>
  </teleport>
</template>

<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useNotificationStore } from '../stores/notification'

const notificationStore = useNotificationStore()
const { notifications } = storeToRefs(notificationStore)
const { removeNotification } = notificationStore
</script>

<style scoped>
.notification-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1000;
  pointer-events: none;
}

.notification {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  max-width: 400px;
  margin-bottom: 12px;
  padding: 16px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  pointer-events: auto;
  position: relative;
}

.notification--success {
  background: #f0f9ff;
  border: 1px solid #38bdf8;
  color: #0c4a6e;
}

.notification--error {
  background: #fef2f2;
  border: 1px solid #f87171;
  color: #991b1b;
}

.notification--warning {
  background: #fffbeb;
  border: 1px solid #fbbf24;
  color: #92400e;
}

.notification--info {
  background: #f8fafc;
  border: 1px solid #64748b;
  color: #334155;
}

.notification__icon {
  flex-shrink: 0;
  margin-top: 2px;
}

.notification--success .notification__icon {
  color: #059669;
}

.notification--error .notification__icon {
  color: #dc2626;
}

.notification--warning .notification__icon {
  color: #d97706;
}

.notification--info .notification__icon {
  color: #0f172a;
}

.notification__content {
  flex: 1;
  min-width: 0;
}

.notification__title {
  margin: 0 0 4px 0;
  font-size: 14px;
  font-weight: 600;
  line-height: 1.4;
}

.notification__message {
  margin: 0;
  font-size: 13px;
  line-height: 1.4;
  opacity: 0.8;
}

.notification__close {
  flex-shrink: 0;
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: background-color 0.2s;
  margin-top: -2px;
  margin-right: -4px;
}

.notification__close:hover {
  background: rgba(0, 0, 0, 0.1);
}

.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>