<template>
  <div class="dosen-mahasiswa">
    <div class="page-header">
      <h1>Daftar Mahasiswa</h1>
      <p>Mahasiswa yang mengambil mata kuliah yang Anda ajar</p>
    </div>

    <div class="content-wrapper">
      <!-- Student Statistics -->
      <div class="student-stats">
        <div class="stats-cards">
          <DashboardCard
            title="Total Mahasiswa"
            :value="studentStats.total"
            icon-text="👥"
            variant="primary"
            :description="`Dari ${studentStats.totalClasses} kelas`"
          />
          
          <DashboardCard
            title="Mahasiswa Aktif"
            :value="studentStats.active"
            icon-text="✅"
            variant="success"
            format="percentage"
            :description="`${studentStats.activeCount} dari ${studentStats.total} mahasiswa`"
          />
          
          <DashboardCard
            title="Rata-rata Nilai"
            :value="studentStats.averageGrade"
            icon-text="📊"
            variant="warning"
            :description="'Nilai keseluruhan'"
          />
          
          <DashboardCard
            title="Kehadiran Rata-rata"
            :value="studentStats.averageAttendance"
            icon-text="📅"
            variant="error"
            format="percentage"
            :description="'Tingkat kehadiran'"
          />
        </div>
      </div>

      <!-- Filter and Search -->
      <div class="filter-section">
        <DashboardCard title="Filter Mahasiswa">
          <div class="filters">
            <div class="filter-group">
              <label>Mata Kuliah:</label>
              <select v-model="selectedCourse" class="filter-select">
                <option value="">Semua Mata Kuliah</option>
                <option v-for="course in courses" :key="course.id" :value="course.id">
                  {{ course.nama }}
                </option>
              </select>
            </div>
            
            <div class="filter-group">
              <label>Kelas:</label>
              <select v-model="selectedClass" class="filter-select">
                <option value="">Semua Kelas</option>
                <option v-for="cls in classes" :key="cls" :value="cls">
                  {{ cls }}
                </option>
              </select>
            </div>
            
            <div class="filter-group">
              <label>Status:</label>
              <select v-model="selectedStatus" class="filter-select">
                <option value="">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="tidak_aktif">Tidak Aktif</option>
                <option value="lulus">Lulus</option>
              </select>
            </div>
            
            <div class="filter-group">
              <label>Cari:</label>
              <input 
                v-model="searchQuery" 
                type="text" 
                placeholder="Nama atau NIM..." 
                class="search-input"
              >
            </div>
            
            <button @click="resetFilters" class="btn btn--outline">
              🔄 Reset
            </button>
          </div>
        </DashboardCard>
      </div>

      <!-- Student List -->
      <div class="student-list">
        <DashboardCard title="Daftar Mahasiswa">
          <template #actions>
            <div class="list-actions">
              <button @click="exportStudents" class="btn btn--outline">
                📊 Export
              </button>
              <button @click="showBulkGradeModal = true" class="btn btn--primary">
                📝 Input Nilai Massal
              </button>
            </div>
          </template>
          
          <DataTable
            :data="filteredStudents"
            :columns="studentColumns"
            :loading="loadingStudents"
            empty-title="Tidak ada mahasiswa"
            empty-message="Tidak ada mahasiswa yang sesuai dengan filter"
            show-actions
            show-view
            show-edit
            @view="viewStudentDetail"
            @edit="editStudentGrade"
          />
        </DashboardCard>
      </div>

      <!-- Performance Summary -->
      <div class="performance-summary">
        <DashboardCard title="Ringkasan Performa">
          <div class="performance-grid">
            <div class="performance-card">
              <h4>📈 Distribusi Nilai</h4>
              <div class="grade-distribution">
                <div v-for="grade in gradeDistribution" :key="grade.grade" class="grade-item">
                  <span class="grade-label">{{ grade.grade }}</span>
                  <div class="grade-bar">
                    <div 
                      class="grade-fill" 
                      :style="{ width: `${grade.percentage}%` }"
                      :class="`grade-${grade.grade.toLowerCase()}`"
                    ></div>
                  </div>
                  <span class="grade-count">{{ grade.count }}</span>
                </div>
              </div>
            </div>

            <div class="performance-card">
              <h4>📊 Statistik Kehadiran</h4>
              <div class="attendance-stats">
                <div class="stat-item">
                  <span class="stat-label">Sangat Baik (>90%)</span>
                  <span class="stat-value">{{ attendanceStats.excellent }}</span>
                </div>
                <div class="stat-item">
                  <span class="stat-label">Baik (80-90%)</span>
                  <span class="stat-value">{{ attendanceStats.good }}</span>
                </div>
                <div class="stat-item">
                  <span class="stat-label">Cukup (70-80%)</span>
                  <span class="stat-value">{{ attendanceStats.fair }}</span>
                </div>
                <div class="stat-item">
                  <span class="stat-label">Kurang (<70%)</span>
                  <span class="stat-value">{{ attendanceStats.poor }}</span>
                </div>
              </div>
            </div>

            <div class="performance-card">
              <h4>⚠️ Mahasiswa Perlu Perhatian</h4>
              <div class="attention-list">
                <div 
                  v-for="student in studentsNeedAttention" 
                  :key="student.id"
                  class="attention-item"
                >
                  <span class="student-name">{{ student.nama }}</span>
                  <span class="student-issue">{{ student.issue }}</span>
                </div>
              </div>
            </div>
          </div>
        </DashboardCard>
      </div>
    </div>

    <!-- Bulk Grade Modal -->
    <div v-if="showBulkGradeModal" class="modal-overlay" @click="showBulkGradeModal = false">
      <div class="modal large" @click.stop>
        <div class="modal-header">
          <h3>Input Nilai Massal</h3>
          <button @click="showBulkGradeModal = false" class="modal-close">×</button>
        </div>
        <div class="modal-body">
          <div class="bulk-grade-form">
            <div class="form-group">
              <label>Mata Kuliah & Kelas:</label>
              <select v-model="bulkGradeForm.courseId" class="form-control">
                <option value="">Pilih Mata Kuliah</option>
                <option v-for="course in courses" :key="course.id" :value="course.id">
                  {{ course.nama }}
                </option>
              </select>
            </div>
            
            <div class="form-group">
              <label>Jenis Nilai:</label>
              <select v-model="bulkGradeForm.gradeType" class="form-control">
                <option value="">Pilih Jenis</option>
                <option value="tugas">Tugas</option>
                <option value="uts">UTS</option>
                <option value="uas">UAS</option>
                <option value="praktikum">Praktikum</option>
              </select>
            </div>
            
            <div class="grade-table">
              <table>
                <thead>
                  <tr>
                    <th>NIM</th>
                    <th>Nama Mahasiswa</th>
                    <th>Nilai</th>
                    <th>Keterangan</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="student in bulkGradeStudents" :key="student.id">
                    <td>{{ student.nim }}</td>
                    <td>{{ student.nama }}</td>
                    <td>
                      <input 
                        v-model="student.grade" 
                        type="number" 
                        min="0" 
                        max="100" 
                        class="grade-input"
                      >
                    </td>
                    <td>
                      <input 
                        v-model="student.note" 
                        type="text" 
                        placeholder="Optional..."
                        class="note-input"
                      >
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="showBulkGradeModal = false" class="btn btn--outline">Batal</button>
          <button @click="saveBulkGrades" class="btn btn--primary" :disabled="!canSaveBulkGrades">
            {{ savingGrades ? 'Menyimpan...' : 'Simpan Nilai' }}
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
const loadingStudents = ref(false)
const showBulkGradeModal = ref(false)
const savingGrades = ref(false)

// Filters
const selectedCourse = ref('')
const selectedClass = ref('')
const selectedStatus = ref('')
const searchQuery = ref('')

// Mock data
const studentStats = ref({
  total: 180,
  totalClasses: 6,
  active: 95.5,
  activeCount: 172,
  averageGrade: 78.5,
  averageAttendance: 88.2
})

const courses = ref([
  { id: 1, nama: 'Algoritma dan Pemrograman', kelas: ['A', 'B'] },
  { id: 2, nama: 'Basis Data', kelas: ['A'] },
  { id: 3, nama: 'Struktur Data', kelas: ['A', 'B'] }
])

const classes = ref(['A', 'B'])

const students = ref([
  {
    id: 1,
    nim: '24010001',
    nama: 'Ahmad Sutanto',
    mata_kuliah: 'Algoritma dan Pemrograman',
    kelas: 'A',
    nilai_akhir: 85,
    kehadiran: 92,
    status: 'aktif',
    email: 'ahmad.sutanto@student.sia.ac.id'
  },
  {
    id: 2,
    nim: '24010002',
    nama: 'Siti Nurhaliza',
    mata_kuliah: 'Basis Data',
    kelas: 'A',
    nilai_akhir: 88,
    kehadiran: 95,
    status: 'aktif',
    email: 'siti.nurhaliza@student.sia.ac.id'
  }
])

const gradeDistribution = ref([
  { grade: 'A', count: 15, percentage: 25 },
  { grade: 'B', count: 25, percentage: 42 },
  { grade: 'C', count: 18, percentage: 30 },
  { grade: 'D', count: 2, percentage: 3 }
])

const attendanceStats = ref({
  excellent: 45,
  good: 35,
  fair: 15,
  poor: 5
})

const studentsNeedAttention = ref([
  { id: 1, nama: 'Budi Santoso', issue: 'Kehadiran rendah (65%)' },
  { id: 2, nama: 'Lisa Wijaya', issue: 'Nilai tugas belum lengkap' },
  { id: 3, nama: 'Ricky Hakim', issue: 'Sering terlambat' }
])

const bulkGradeForm = ref({
  courseId: '',
  gradeType: ''
})

const bulkGradeStudents = ref([
  { id: 1, nim: '24010001', nama: 'Ahmad Sutanto', grade: '', note: '' },
  { id: 2, nim: '24010002', nama: 'Siti Nurhaliza', grade: '', note: '' }
])

// Computed
const filteredStudents = computed(() => {
  let filtered = students.value

  if (selectedCourse.value) {
    const course = courses.value.find(c => c.id === parseInt(selectedCourse.value))
    if (course) {
      filtered = filtered.filter(s => s.mata_kuliah === course.nama)
    }
  }

  if (selectedClass.value) {
    filtered = filtered.filter(s => s.kelas === selectedClass.value)
  }

  if (selectedStatus.value) {
    filtered = filtered.filter(s => s.status === selectedStatus.value)
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(s => 
      s.nama.toLowerCase().includes(query) || 
      s.nim.includes(query)
    )
  }

  return filtered
})

const canSaveBulkGrades = computed(() => {
  return bulkGradeForm.value.courseId && 
         bulkGradeForm.value.gradeType &&
         bulkGradeStudents.value.some(s => s.grade)
})

// Table columns
const studentColumns = [
  { key: 'nim', label: 'NIM', sortable: true },
  { key: 'nama', label: 'Nama Mahasiswa', sortable: true },
  { key: 'mata_kuliah', label: 'Mata Kuliah', sortable: true },
  { key: 'kelas', label: 'Kelas', align: 'center' },
  { key: 'nilai_akhir', label: 'Nilai', align: 'center', format: 'number' },
  { key: 'kehadiran', label: 'Kehadiran', align: 'center', format: 'percentage' },
  { key: 'status', label: 'Status', align: 'center' }
]

// Methods
const resetFilters = () => {
  selectedCourse.value = ''
  selectedClass.value = ''
  selectedStatus.value = ''
  searchQuery.value = ''
}

const viewStudentDetail = (student: any) => {
  console.log('View student detail:', student)
}

const editStudentGrade = (student: any) => {
  console.log('Edit student grade:', student)
}

const exportStudents = () => {
  console.log('Export students data')
}

const saveBulkGrades = async () => {
  savingGrades.value = true
  try {
    // Simulate saving
    await new Promise(resolve => setTimeout(resolve, 2000))
    
    console.log('Bulk grades saved:', bulkGradeStudents.value)
    showBulkGradeModal.value = false
    
    // Reset form
    bulkGradeForm.value = { courseId: '', gradeType: '' }
    bulkGradeStudents.value.forEach(s => {
      s.grade = ''
      s.note = ''
    })
  } catch (error) {
    console.error('Failed to save grades:', error)
  } finally {
    savingGrades.value = false
  }
}

// Lifecycle
onMounted(() => {
  // Load student data
})
</script>

<style scoped>
.dosen-mahasiswa {
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

.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.filters {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  align-items: end;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.filter-group label {
  font-weight: 500;
  color: #374151;
  font-size: 0.9rem;
}

.filter-select,
.search-input {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  color: #374151;
  font-size: 0.9rem;
}

.list-actions {
  display: flex;
  gap: 12px;
}

.performance-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
}

.performance-card {
  background: #f9fafb;
  border-radius: 8px;
  padding: 20px;
}

.performance-card h4 {
  color: #1f2937;
  margin: 0 0 16px 0;
  font-size: 1.1rem;
}

.grade-distribution {
  display: grid;
  gap: 12px;
}

.grade-item {
  display: grid;
  grid-template-columns: 30px 1fr 40px;
  gap: 12px;
  align-items: center;
}

.grade-label {
  font-weight: 600;
  color: #374151;
}

.grade-bar {
  height: 20px;
  background: #e5e7eb;
  border-radius: 10px;
  overflow: hidden;
}

.grade-fill {
  height: 100%;
  transition: width 0.3s ease;
}

.grade-fill.grade-a { background: #10b981; }
.grade-fill.grade-b { background: #3b82f6; }
.grade-fill.grade-c { background: #f59e0b; }
.grade-fill.grade-d { background: #ef4444; }

.grade-count {
  font-weight: 500;
  color: #6b7280;
  text-align: right;
}

.attendance-stats {
  display: grid;
  gap: 12px;
}

.stat-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-label {
  color: #6b7280;
  font-size: 0.9rem;
}

.stat-value {
  font-weight: 600;
  color: #1f2937;
  background: white;
  padding: 4px 8px;
  border-radius: 4px;
}

.attention-list {
  display: grid;
  gap: 12px;
}

.attention-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: white;
  border-radius: 6px;
  border-left: 4px solid #f59e0b;
}

.student-name {
  font-weight: 500;
  color: #1f2937;
}

.student-issue {
  font-size: 0.9rem;
  color: #d97706;
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
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal.large {
  max-width: 1000px;
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

.bulk-grade-form {
  display: grid;
  gap: 20px;
}

.form-group {
  display: grid;
  gap: 8px;
}

.form-group label {
  font-weight: 500;
  color: #374151;
}

.form-control {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.9rem;
  color: #374151;
}

.grade-table {
  overflow-x: auto;
}

.grade-table table {
  width: 100%;
  border-collapse: collapse;
}

.grade-table th,
.grade-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.grade-table th {
  background: #f9fafb;
  font-weight: 600;
  color: #374151;
}

.grade-input,
.note-input {
  width: 100%;
  padding: 6px 8px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 0.9rem;
}

.grade-input {
  width: 80px;
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

@media (max-width: 768px) {
  .stats-cards {
    grid-template-columns: 1fr;
  }
  
  .filters {
    grid-template-columns: 1fr;
  }
  
  .performance-grid {
    grid-template-columns: 1fr;
  }
  
  .list-actions {
    flex-direction: column;
  }
  
  .modal {
    width: 95%;
    margin: 20px;
  }
  
  .grade-item {
    grid-template-columns: 40px 1fr 50px;
  }
}
</style>