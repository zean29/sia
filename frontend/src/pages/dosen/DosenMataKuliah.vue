<template>
  <div class="dosen-mata-kuliah">
    <div class="page-header">
      <h1>Mata Kuliah</h1>
      <p>Kelola materi, silabus, dan informasi mata kuliah yang Anda ajar</p>
    </div>

    <div class="content-wrapper">
      <!-- Course Overview -->
      <div class="course-overview">
        <div class="overview-cards">
          <DashboardCard
            title="Mata Kuliah Aktif"
            :value="courseStats.activeCourses"
            icon-text="📚"
            variant="primary"
            :description="`${courseStats.totalClasses} kelas semester ini`"
          />
          
          <DashboardCard
            title="Total Mahasiswa"
            :value="courseStats.totalStudents"
            icon-text="👥"
            variant="success"
            :description="`Dari ${courseStats.totalClasses} kelas`"
          />
          
          <DashboardCard
            title="Beban SKS"
            :value="courseStats.totalCredits"
            icon-text="⚖️"
            variant="warning"
            :description="'SKS semester ini'"
          />
          
          <DashboardCard
            title="Materi Uploaded"
            :value="courseStats.uploadedMaterials"
            icon-text="📋"
            variant="error"
            :description="`${courseStats.pendingMaterials} menunggu upload`"
          />
        </div>
      </div>

      <!-- My Courses -->
      <div class="my-courses">
        <DashboardCard title="Mata Kuliah yang Diajar">
          <template #actions>
            <button @click="refreshCourses" class="btn btn--outline">
              🔄 Refresh
            </button>
          </template>
          
          <DataTable
            :data="myCourses"
            :columns="courseColumns"
            :loading="loadingCourses"
            empty-title="Tidak ada mata kuliah"
            empty-message="Anda belum memiliki mata kuliah yang diajar semester ini"
            show-actions
            show-view
            show-edit
            @view="viewCourseDetail"
            @edit="editCourse"
          />
        </DashboardCard>
      </div>

      <!-- Course Materials -->
      <div class="course-materials">
        <DashboardCard title="Materi Pembelajaran">
          <div class="materials-filters">
            <select v-model="selectedCourseId" class="filter-select">
              <option value="">Semua Mata Kuliah</option>
              <option v-for="course in myCourses" :key="course.id" :value="course.id">
                {{ course.mata_kuliah }}
              </option>
            </select>
            <button @click="showUploadModal = true" class="btn btn--primary">
              📁 Upload Materi
            </button>
          </div>
          
          <DataTable
            :data="filteredMaterials"
            :columns="materialColumns"
            :loading="loadingMaterials"
            empty-title="Belum ada materi"
            empty-message="Belum ada materi pembelajaran yang diupload"
            show-actions
            show-view
            show-edit
            show-delete
            @view="viewMaterial"
            @edit="editMaterial"
            @delete="deleteMaterial"
          />
        </DashboardCard>
      </div>

      <!-- Syllabus Management -->
      <div class="syllabus-management">
        <DashboardCard title="Silabus & RPS">
          <div class="syllabus-grid">
            <div 
              v-for="course in myCourses" 
              :key="course.id"
              class="syllabus-card"
            >
              <div class="syllabus-header">
                <h4>{{ course.mata_kuliah }}</h4>
                <span class="course-code">{{ course.kode }}</span>
              </div>
              <div class="syllabus-status">
                <div class="status-item">
                  <span class="status-label">Silabus:</span>
                  <span class="status-value" :class="course.syllabus_status">
                    {{ course.syllabus_status === 'complete' ? '✅ Lengkap' : '⚠️ Belum Lengkap' }}
                  </span>
                </div>
                <div class="status-item">
                  <span class="status-label">RPS:</span>
                  <span class="status-value" :class="course.rps_status">
                    {{ course.rps_status === 'complete' ? '✅ Lengkap' : '⚠️ Belum Lengkap' }}
                  </span>
                </div>
              </div>
              <div class="syllabus-actions">
                <button @click="editSyllabus(course)" class="btn btn--sm btn--outline">
                  ✏️ Edit Silabus
                </button>
                <button @click="editRPS(course)" class="btn btn--sm btn--primary">
                  📋 Edit RPS
                </button>
              </div>
            </div>
          </div>
        </DashboardCard>
      </div>
    </div>

    <!-- Upload Material Modal -->
    <div v-if="showUploadModal" class="modal-overlay" @click="showUploadModal = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Upload Materi Pembelajaran</h3>
          <button @click="showUploadModal = false" class="modal-close">×</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Mata Kuliah</label>
            <select v-model="uploadForm.courseId" class="form-control">
              <option value="">Pilih Mata Kuliah</option>
              <option v-for="course in myCourses" :key="course.id" :value="course.id">
                {{ course.mata_kuliah }}
              </option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Judul Materi</label>
            <input v-model="uploadForm.title" type="text" class="form-control" placeholder="Contoh: Pengantar Algoritma">
          </div>
          
          <div class="form-group">
            <label>Deskripsi</label>
            <textarea v-model="uploadForm.description" class="form-control" rows="3" placeholder="Deskripsi materi..."></textarea>
          </div>
          
          <div class="form-group">
            <label>File</label>
            <div class="file-upload">
              <input 
                ref="fileInput"
                type="file"
                @change="handleFileSelect"
                accept=".pdf,.ppt,.pptx,.doc,.docx,.zip"
                style="display: none;"
              >
              <button @click="$refs.fileInput.click()" class="btn btn--outline">
                📎 Pilih File
              </button>
              <span v-if="uploadForm.file" class="file-name">{{ uploadForm.file.name }}</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="showUploadModal = false" class="btn btn--outline">Batal</button>
          <button @click="uploadMaterial" class="btn btn--primary" :disabled="!canUpload">
            {{ uploading ? 'Mengupload...' : 'Upload' }}
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
const loadingCourses = ref(false)
const loadingMaterials = ref(false)
const showUploadModal = ref(false)
const uploading = ref(false)
const selectedCourseId = ref('')

const uploadForm = ref({
  courseId: '',
  title: '',
  description: '',
  file: null as File | null
})

// Mock data
const courseStats = ref({
  activeCourses: 4,
  totalClasses: 6,
  totalStudents: 180,
  totalCredits: 12,
  uploadedMaterials: 24,
  pendingMaterials: 8
})

const myCourses = ref([
  {
    id: 1,
    mata_kuliah: 'Algoritma dan Pemrograman',
    kode: 'TI101',
    sks: 3,
    kelas: ['A', 'B'],
    jumlah_mahasiswa: 75,
    ruang: 'R.101, R.102',
    jadwal: 'Senin 08:00-10:30, Selasa 13:00-15:30',
    syllabus_status: 'complete',
    rps_status: 'incomplete'
  },
  {
    id: 2,
    mata_kuliah: 'Basis Data',
    kode: 'TI201',
    sks: 3,
    kelas: ['A'],
    jumlah_mahasiswa: 40,
    ruang: 'R.203',
    jadwal: 'Rabu 10:30-13:00',
    syllabus_status: 'complete',
    rps_status: 'complete'
  }
])

const courseMaterials = ref([
  {
    id: 1,
    courseId: 1,
    mata_kuliah: 'Algoritma dan Pemrograman',
    judul: 'Pengantar Algoritma',
    jenis: 'Slide Presentasi',
    ukuran: '2.5 MB',
    tanggal_upload: '2024-09-20',
    downloads: 45,
    status: 'Aktif'
  },
  {
    id: 2,
    courseId: 1,
    mata_kuliah: 'Algoritma dan Pemrograman',
    judul: 'Latihan Sorting',
    jenis: 'Dokumen',
    ukuran: '1.8 MB',
    tanggal_upload: '2024-09-18',
    downloads: 38,
    status: 'Aktif'
  },
  {
    id: 3,
    courseId: 2,
    mata_kuliah: 'Basis Data',
    judul: 'ERD dan Normalisasi',
    jenis: 'Slide Presentasi',
    ukuran: '3.2 MB',
    tanggal_upload: '2024-09-15',
    downloads: 42,
    status: 'Aktif'
  }
])

// Computed
const filteredMaterials = computed(() => {
  if (!selectedCourseId.value) return courseMaterials.value
  return courseMaterials.value.filter(material => material.courseId === parseInt(selectedCourseId.value))
})

const canUpload = computed(() => {
  return uploadForm.value.courseId && uploadForm.value.title && uploadForm.value.file
})

// Table columns
const courseColumns = [
  { key: 'mata_kuliah', label: 'Mata Kuliah', sortable: true },
  { key: 'kode', label: 'Kode', align: 'center' },
  { key: 'sks', label: 'SKS', align: 'center' },
  { key: 'kelas', label: 'Kelas', align: 'center' },
  { key: 'jumlah_mahasiswa', label: 'Mahasiswa', align: 'center', format: 'number' },
  { key: 'jadwal', label: 'Jadwal' }
]

const materialColumns = [
  { key: 'mata_kuliah', label: 'Mata Kuliah', sortable: true },
  { key: 'judul', label: 'Judul Materi', sortable: true },
  { key: 'jenis', label: 'Jenis', align: 'center' },
  { key: 'ukuran', label: 'Ukuran', align: 'center' },
  { key: 'tanggal_upload', label: 'Upload', sortable: true, format: 'date' },
  { key: 'downloads', label: 'Downloads', align: 'center', format: 'number' },
  { key: 'status', label: 'Status', align: 'center' }
]

// Methods
const refreshCourses = () => {
  loadingCourses.value = true
  setTimeout(() => {
    loadingCourses.value = false
  }, 1000)
}

const viewCourseDetail = (course: any) => {
  console.log('View course detail:', course)
}

const editCourse = (course: any) => {
  console.log('Edit course:', course)
}

const viewMaterial = (material: any) => {
  console.log('View material:', material)
}

const editMaterial = (material: any) => {
  console.log('Edit material:', material)
}

const deleteMaterial = (material: any) => {
  if (confirm(`Hapus materi "${material.judul}"?`)) {
    const index = courseMaterials.value.findIndex(m => m.id === material.id)
    if (index > -1) {
      courseMaterials.value.splice(index, 1)
    }
  }
}

const editSyllabus = (course: any) => {
  console.log('Edit syllabus for:', course)
}

const editRPS = (course: any) => {
  console.log('Edit RPS for:', course)
}

const handleFileSelect = (event: Event) => {
  const files = (event.target as HTMLInputElement).files
  if (files && files.length > 0) {
    uploadForm.value.file = files[0]
  }
}

const uploadMaterial = async () => {
  uploading.value = true
  try {
    // Simulate upload
    await new Promise(resolve => setTimeout(resolve, 2000))
    
    const course = myCourses.value.find(c => c.id === parseInt(uploadForm.value.courseId))
    
    // Add to materials
    courseMaterials.value.unshift({
      id: Date.now(),
      courseId: parseInt(uploadForm.value.courseId),
      mata_kuliah: course?.mata_kuliah || '',
      judul: uploadForm.value.title,
      jenis: 'Dokumen',
      ukuran: formatFileSize(uploadForm.value.file?.size || 0),
      tanggal_upload: new Date().toISOString().split('T')[0],
      downloads: 0,
      status: 'Aktif'
    })
    
    // Reset form
    uploadForm.value = {
      courseId: '',
      title: '',
      description: '',
      file: null
    }
    
    showUploadModal.value = false
  } catch (error) {
    console.error('Upload failed:', error)
  } finally {
    uploading.value = false
  }
}

const formatFileSize = (bytes: number) => {
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  if (bytes === 0) return '0 Bytes'
  const i = Math.floor(Math.log(bytes) / Math.log(1024))
  return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i]
}

// Lifecycle
onMounted(() => {
  // Load course data
})
</script>

<style scoped>
.dosen-mata-kuliah {
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

.overview-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
}

.materials-filters {
  display: flex;
  gap: 16px;
  margin-bottom: 20px;
  align-items: center;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  color: #374151;
  font-size: 0.9rem;
}

.syllabus-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.syllabus-card {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 20px;
  background: white;
}

.syllabus-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.syllabus-header h4 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
  flex: 1;
}

.course-code {
  background: #f3f4f6;
  color: #6b7280;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 500;
}

.syllabus-status {
  margin-bottom: 16px;
}

.status-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.status-label {
  color: #6b7280;
  font-size: 0.9rem;
}

.status-value {
  font-size: 0.9rem;
  font-weight: 500;
}

.status-value.complete {
  color: #059669;
}

.status-value.incomplete {
  color: #d97706;
}

.syllabus-actions {
  display: flex;
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
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
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

.form-control:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.file-upload {
  display: flex;
  align-items: center;
  gap: 12px;
}

.file-name {
  color: #6b7280;
  font-size: 0.9rem;
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

.btn--sm {
  padding: 6px 12px;
  font-size: 0.8rem;
}

@media (max-width: 768px) {
  .overview-cards {
    grid-template-columns: 1fr;
  }
  
  .materials-filters {
    flex-direction: column;
    align-items: stretch;
  }
  
  .syllabus-grid {
    grid-template-columns: 1fr;
  }
  
  .syllabus-header {
    flex-direction: column;
    gap: 8px;
  }
  
  .modal {
    width: 95%;
    margin: 20px;
  }
}
</style>