<template>
  <div class="admin-sistem">
    <div class="page-header">
      <h1>Pengaturan Sistem</h1>
      <p>Konfigurasi sistem, backup data, dan user management</p>
    </div>

    <div class="content-wrapper">
      <!-- System Status -->
      <div class="system-status">
        <DashboardCard title="Status Sistem">
          <div class="status-grid">
            <div class="status-item">
              <span class="status-label">Database</span>
              <span class="status-value online">🟢 Online</span>
            </div>
            <div class="status-item">
              <span class="status-label">Storage</span>
              <span class="status-value">💾 75% Used</span>
            </div>
            <div class="status-item">
              <span class="status-label">Last Backup</span>
              <span class="status-value">📅 2 hours ago</span>
            </div>
            <div class="status-item">
              <span class="status-label">System Health</span>
              <span class="status-value online">✅ Good</span>
            </div>
          </div>
        </DashboardCard>
      </div>

      <!-- User Management -->
      <div class="user-management">
        <DashboardCard title="Manajemen Pengguna">
          <template #actions>
            <button @click="showAddUserModal = true" class="btn btn--primary">
              👤 Tambah Pengguna
            </button>
          </template>
          
          <DataTable
            :data="users"
            :columns="userColumns"
            :loading="loadingUsers"
            empty-title="Tidak ada pengguna"
            empty-message="Belum ada data pengguna"
            show-actions
            show-view
            show-edit
            show-delete
            @view="viewUser"
            @edit="editUser"
            @delete="deleteUser"
          />
        </DashboardCard>
      </div>

      <!-- System Settings -->
      <div class="system-settings">
        <DashboardCard title="Pengaturan Sistem">
          <div class="settings-sections">
            <div class="settings-section">
              <h4>📚 Akademik</h4>
              <div class="setting-item">
                <label>Tahun Akademik Aktif:</label>
                <select v-model="settings.activeYear" class="setting-input">
                  <option value="2024/2025">2024/2025</option>
                  <option value="2025/2026">2025/2026</option>
                </select>
              </div>
              <div class="setting-item">
                <label>Semester Aktif:</label>
                <select v-model="settings.activeSemester" class="setting-input">
                  <option value="ganjil">Ganjil</option>
                  <option value="genap">Genap</option>
                </select>
              </div>
              <div class="setting-item">
                <label>Batas SKS Maksimal:</label>
                <input v-model="settings.maxCredits" type="number" class="setting-input">
              </div>
            </div>

            <div class="settings-section">
              <h4>💰 Keuangan</h4>
              <div class="setting-item">
                <label>SPP per SKS:</label>
                <input v-model="settings.feePerCredit" type="number" class="setting-input">
              </div>
              <div class="setting-item">
                <label>Batas Pembayaran:</label>
                <input v-model="settings.paymentDeadline" type="date" class="setting-input">
              </div>
              <div class="setting-item">
                <label>Denda Keterlambatan:</label>
                <input v-model="settings.lateFee" type="number" class="setting-input">
              </div>
            </div>

            <div class="settings-section">
              <h4>🔧 Sistem</h4>
              <div class="setting-item">
                <label>Maintenance Mode:</label>
                <input v-model="settings.maintenanceMode" type="checkbox" class="setting-checkbox">
              </div>
              <div class="setting-item">
                <label>Auto Backup:</label>
                <input v-model="settings.autoBackup" type="checkbox" class="setting-checkbox">
              </div>
              <div class="setting-item">
                <label>Email Notifications:</label>
                <input v-model="settings.emailNotifications" type="checkbox" class="setting-checkbox">
              </div>
            </div>
          </div>
          
          <div class="settings-actions">
            <button @click="saveSettings" class="btn btn--primary" :disabled="savingSettings">
              {{ savingSettings ? 'Menyimpan...' : '💾 Simpan Pengaturan' }}
            </button>
            <button @click="resetSettings" class="btn btn--outline">
              🔄 Reset ke Default
            </button>
          </div>
        </DashboardCard>
      </div>

      <!-- Backup & Maintenance -->
      <div class="backup-maintenance">
        <DashboardCard title="Backup & Maintenance">
          <div class="backup-grid">
            <div class="backup-section">
              <h4>📦 Backup Data</h4>
              <p>Backup terakhir: {{ lastBackup }}</p>
              <div class="backup-actions">
                <button @click="createBackup" class="btn btn--primary" :disabled="creatingBackup">
                  {{ creatingBackup ? 'Creating...' : '📦 Buat Backup' }}
                </button>
                <button @click="downloadBackup" class="btn btn--outline">
                  📥 Download Backup
                </button>
              </div>
            </div>

            <div class="backup-section">
              <h4>🔧 Maintenance</h4>
              <p>Status: {{ settings.maintenanceMode ? 'Mode Maintenance' : 'Normal' }}</p>
              <div class="maintenance-actions">
                <button @click="toggleMaintenance" class="btn btn--warning">
                  {{ settings.maintenanceMode ? '🟢 Keluar Maintenance' : '🟡 Mode Maintenance' }}
                </button>
                <button @click="clearCache" class="btn btn--outline">
                  🗑️ Clear Cache
                </button>
              </div>
            </div>

            <div class="backup-section">
              <h4>📊 Logs</h4>
              <p>Monitor aktivitas sistem</p>
              <div class="log-actions">
                <button @click="viewLogs" class="btn btn--outline">
                  📄 Lihat Logs
                </button>
                <button @click="downloadLogs" class="btn btn--outline">
                  📥 Download Logs
                </button>
              </div>
            </div>
          </div>
        </DashboardCard>
      </div>
    </div>

    <!-- Add User Modal -->
    <div v-if="showAddUserModal" class="modal-overlay" @click="showAddUserModal = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Tambah Pengguna Baru</h3>
          <button @click="showAddUserModal = false" class="modal-close">×</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nama Lengkap:</label>
            <input v-model="newUser.name" type="text" class="form-control">
          </div>
          
          <div class="form-group">
            <label>Email:</label>
            <input v-model="newUser.email" type="email" class="form-control">
          </div>
          
          <div class="form-group">
            <label>Role:</label>
            <select v-model="newUser.role" class="form-control">
              <option value="">Pilih Role</option>
              <option value="admin">Administrator</option>
              <option value="staf">Staf</option>
              <option value="dosen">Dosen</option>
              <option value="mahasiswa">Mahasiswa</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Password:</label>
            <input v-model="newUser.password" type="password" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button @click="showAddUserModal = false" class="btn btn--outline">Batal</button>
          <button @click="addUser" class="btn btn--primary" :disabled="!canAddUser">
            Tambah Pengguna
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import DashboardCard from '@/components/DashboardCard.vue'
import DataTable from '@/components/DataTable.vue'

// Data
const loadingUsers = ref(false)
const savingSettings = ref(false)
const creatingBackup = ref(false)
const showAddUserModal = ref(false)

const lastBackup = ref('2 jam yang lalu')

const settings = ref({
  activeYear: '2024/2025',
  activeSemester: 'ganjil',
  maxCredits: 24,
  feePerCredit: 500000,
  paymentDeadline: '2024-09-30',
  lateFee: 100000,
  maintenanceMode: false,
  autoBackup: true,
  emailNotifications: true
})

const users = ref([
  {
    id: 1,
    nama: 'Super Admin',
    email: 'superadmin@sia.ac.id',
    role: 'admin',
    status: 'aktif',
    last_login: '2024-09-25 10:30:00'
  },
  {
    id: 2,
    nama: 'Dr. Bambang Suharto',
    email: 'bambang.suharto@sia.ac.id',
    role: 'dosen',
    status: 'aktif',
    last_login: '2024-09-25 08:15:00'
  }
])

const newUser = ref({
  name: '',
  email: '',
  role: '',
  password: ''
})

// Computed
const canAddUser = computed(() => {
  return newUser.value.name && 
         newUser.value.email && 
         newUser.value.role && 
         newUser.value.password
})

// Table columns
const userColumns = [
  { key: 'nama', label: 'Nama', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'role', label: 'Role', align: 'center' },
  { key: 'status', label: 'Status', align: 'center' },
  { key: 'last_login', label: 'Login Terakhir', sortable: true, format: 'datetime' }
]

// Methods
const saveSettings = async () => {
  savingSettings.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1000))
    console.log('Settings saved:', settings.value)
  } finally {
    savingSettings.value = false
  }
}

const resetSettings = () => {
  if (confirm('Reset semua pengaturan ke default?')) {
    // Reset to default values
    console.log('Settings reset')
  }
}

const createBackup = async () => {
  creatingBackup.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 3000))
    lastBackup.value = 'Baru saja'
    console.log('Backup created')
  } finally {
    creatingBackup.value = false
  }
}

const downloadBackup = () => {
  console.log('Downloading backup...')
}

const toggleMaintenance = () => {
  settings.value.maintenanceMode = !settings.value.maintenanceMode
  console.log('Maintenance mode:', settings.value.maintenanceMode)
}

const clearCache = () => {
  console.log('Cache cleared')
}

const viewLogs = () => {
  console.log('View logs')
}

const downloadLogs = () => {
  console.log('Download logs')
}

const addUser = async () => {
  try {
    // Add user logic
    users.value.push({
      id: Date.now(),
      nama: newUser.value.name,
      email: newUser.value.email,
      role: newUser.value.role,
      status: 'aktif',
      last_login: 'Belum pernah'
    })
    
    // Reset form
    newUser.value = { name: '', email: '', role: '', password: '' }
    showAddUserModal.value = false
    
    console.log('User added successfully')
  } catch (error) {
    console.error('Failed to add user:', error)
  }
}

const viewUser = (user: any) => {
  console.log('View user:', user)
}

const editUser = (user: any) => {
  console.log('Edit user:', user)
}

const deleteUser = (user: any) => {
  if (confirm(`Hapus pengguna "${user.nama}"?`)) {
    const index = users.value.findIndex(u => u.id === user.id)
    if (index > -1) {
      users.value.splice(index, 1)
    }
  }
}

// Lifecycle
onMounted(() => {
  // Load system data
})
</script>

<style scoped>
.admin-sistem {
  padding: 20px 0;
}

.page-header {
  margin-bottom: 32px;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 8px;
}

.page-header p {
  color: #6b7280;
  margin: 0;
}

.content-wrapper {
  display: grid;
  gap: 32px;
}

.status-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  padding: 20px;
}

.status-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: #f9fafb;
  border-radius: 8px;
}

.status-label {
  color: #6b7280;
  font-weight: 500;
}

.status-value {
  font-weight: 600;
  color: #1f2937;
}

.status-value.online {
  color: #059669;
}

.settings-sections {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
  padding: 20px;
}

.settings-section {
  background: #f9fafb;
  border-radius: 8px;
  padding: 20px;
}

.settings-section h4 {
  color: #1f2937;
  margin: 0 0 16px 0;
  font-size: 1.1rem;
}

.setting-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.setting-item label {
  color: #374151;
  font-weight: 500;
  font-size: 0.9rem;
}

.setting-input {
  width: 150px;
  padding: 6px 8px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 0.9rem;
}

.setting-checkbox {
  width: 20px;
  height: 20px;
}

.settings-actions {
  display: flex;
  gap: 12px;
  padding: 20px;
  border-top: 1px solid #e5e7eb;
}

.backup-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 24px;
  padding: 20px;
}

.backup-section {
  background: #f9fafb;
  border-radius: 8px;
  padding: 20px;
}

.backup-section h4 {
  color: #1f2937;
  margin: 0 0 8px 0;
  font-size: 1.1rem;
}

.backup-section p {
  color: #6b7280;
  margin: 0 0 16px 0;
  font-size: 0.9rem;
}

.backup-actions,
.maintenance-actions,
.log-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
  margin: 0;
  color: #1f2937;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #6b7280;
  cursor: pointer;
  padding: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-body {
  padding: 20px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 20px;
  border-top: 1px solid #e5e7eb;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 500;
  color: #374151;
}

.form-control {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.9rem;
  color: #374151;
}

.btn {
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn--primary {
  background: #667eea;
  color: white;
}

.btn--primary:hover {
  background: #5a6fd8;
}

.btn--primary:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

.btn--outline {
  background: white;
  border: 1px solid #d1d5db;
  color: #374151;
}

.btn--outline:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.btn--warning {
  background: #f59e0b;
  color: white;
}

.btn--warning:hover {
  background: #d97706;
}

@media (max-width: 768px) {
  .status-grid {
    grid-template-columns: 1fr;
  }
  
  .settings-sections {
    grid-template-columns: 1fr;
  }
  
  .backup-grid {
    grid-template-columns: 1fr;
  }
  
  .settings-actions {
    flex-direction: column;
  }
  
  .setting-item {
    flex-direction: column;
    align-items: stretch;
    gap: 8px;
  }
  
  .setting-input {
    width: 100%;
  }
}
</style>