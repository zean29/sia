<template>
  <div class="mahasiswa-berkas">
    <div class="page-header">
      <h1>Berkas & Dokumen</h1>
      <p>Unduh transkrip, sertifikat, dan dokumen akademik</p>
    </div>

    <div class="content-wrapper">
      <!-- Available Documents -->
      <div class="available-documents">
        <DashboardCard title="Dokumen Tersedia">
          <div class="document-grid">
            <div 
              v-for="doc in availableDocuments" 
              :key="doc.id"
              class="document-card"
              :class="{ 'disabled': !doc.available }"
            >
              <div class="document-icon">
                {{ doc.icon }}
              </div>
              <div class="document-info">
                <h4>{{ doc.name }}</h4>
                <p>{{ doc.description }}</p>
                <div class="document-meta">
                  <span class="document-size">{{ doc.size }}</span>
                  <span class="document-date">{{ doc.lastUpdated }}</span>
                </div>
              </div>
              <div class="document-actions">
                <button 
                  v-if="doc.available"
                  @click="downloadDocument(doc)"
                  class="btn btn--primary"
                  :disabled="downloading === doc.id"
                >
                  {{ downloading === doc.id ? 'Mengunduh...' : 'Unduh' }}
                </button>
                <button 
                  v-else
                  class="btn btn--disabled"
                  disabled
                >
                  Tidak Tersedia
                </button>
              </div>
            </div>
          </div>
        </DashboardCard>
      </div>

      <!-- Upload Documents -->
      <div class="upload-documents">
        <DashboardCard title="Upload Dokumen">
          <div class="upload-section">
            <p class="upload-info">
              Upload dokumen pendukung seperti surat izin, surat keterangan dokter, atau dokumen lainnya.
            </p>
            
            <div class="upload-area" @drop="handleDrop" @dragover.prevent @dragenter.prevent>
              <div class="upload-icon">📁</div>
              <h4>Drag & drop file di sini</h4>
              <p>atau klik untuk memilih file</p>
              <input 
                ref="fileInput"
                type="file"
                multiple
                @change="handleFileSelect"
                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                style="display: none;"
              >
              <button @click="$refs.fileInput.click()" class="btn btn--outline">
                Pilih File
              </button>
            </div>

            <div v-if="uploadedFiles.length > 0" class="uploaded-files">
              <h5>File yang dipilih:</h5>
              <div 
                v-for="(file, index) in uploadedFiles" 
                :key="index"
                class="file-item"
              >
                <span class="file-name">{{ file.name }}</span>
                <span class="file-size">{{ formatFileSize(file.size) }}</span>
                <button @click="removeFile(index)" class="btn btn--sm btn--danger">
                  Hapus
                </button>
              </div>
              <button @click="uploadFiles" class="btn btn--primary" :disabled="uploading">
                {{ uploading ? 'Mengupload...' : 'Upload File' }}
              </button>
            </div>
          </div>
        </DashboardCard>
      </div>

      <!-- Document History -->
      <div class="document-history">
        <DashboardCard title="Riwayat Dokumen">
          <DataTable
            :data="documentHistory"
            :columns="historyColumns"
            :loading="loadingHistory"
            empty-title="Tidak ada riwayat"
            empty-message="Belum ada riwayat download atau upload dokumen"
            show-actions
            show-view
            @view="viewDocumentDetail"
          />
        </DashboardCard>
      </div>

      <!-- Academic Documents Info -->
      <div class="academic-info">
        <DashboardCard title="Informasi Dokumen Akademik">
          <div class="info-sections">
            <div class="info-section">
              <h4>📋 Transkrip Nilai</h4>
              <p>Dokumen yang berisi daftar mata kuliah yang telah ditempuh beserta nilai yang diperoleh. Transkrip dapat diunduh setelah minimal menyelesaikan 1 semester.</p>
              <ul>
                <li>Format: PDF</li>
                <li>Bahasa: Indonesia & Inggris</li>
                <li>Berlaku: Selamanya</li>
              </ul>
            </div>

            <div class="info-section">
              <h4>🎓 Sertifikat Kelulusan</h4>
              <p>Sertifikat resmi yang menyatakan telah menyelesaikan program studi. Tersedia setelah dinyatakan lulus dan menyelesaikan semua administrasi.</p>
              <ul>
                <li>Format: PDF (Scan Asli)</li>
                <li>Legalisasi: Tersertifikasi Digital</li>
                <li>Pengambilan: Online & Offline</li>
              </ul>
            </div>

            <div class="info-section">
              <h4>📄 Surat Keterangan</h4>
              <p>Berbagai surat keterangan akademik seperti surat keterangan mahasiswa aktif, surat rekomendasi, dan lainnya.</p>
              <ul>
                <li>Proses: 2-3 hari kerja</li>
                <li>Persyaratan: Sesuai jenis surat</li>
                <li>Biaya: Sesuai ketentuan</li>
              </ul>
            </div>
          </div>
        </DashboardCard>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import DashboardCard from '@/components/DashboardCard.vue'
import DataTable from '@/components/DataTable.vue'

// Data
const downloading = ref<number | null>(null)
const uploading = ref(false)
const loadingHistory = ref(false)
const uploadedFiles = ref<File[]>([])

// Mock data
const availableDocuments = ref([
  {
    id: 1,
    name: 'Transkrip Nilai Semester 1',
    description: 'Transkrip nilai untuk semester ganjil 2024/2025',
    icon: '📋',
    size: '1.2 MB',
    lastUpdated: '25 Sep 2024',
    available: false, // Not available for first semester student
    format: 'PDF'
  },
  {
    id: 2,
    name: 'Kartu Hasil Studi (KHS)',
    description: 'KHS semester berjalan',
    icon: '📊',
    size: '450 KB',
    lastUpdated: '20 Sep 2024',
    available: false, // Not available until semester ends
    format: 'PDF'
  },
  {
    id: 3,
    name: 'Surat Keterangan Mahasiswa Aktif',
    description: 'Surat keterangan status mahasiswa aktif',
    icon: '📄',
    size: '320 KB',
    lastUpdated: '01 Sep 2024',
    available: true,
    format: 'PDF'
  },
  {
    id: 4,
    name: 'Kartu Tanda Mahasiswa (KTM)',
    description: 'KTM digital untuk semester ini',
    icon: '🆔',
    size: '180 KB',
    lastUpdated: '01 Sep 2024',
    available: true,
    format: 'PDF'
  },
  {
    id: 5,
    name: 'Sertifikat Kelulusan',
    description: 'Sertifikat kelulusan program studi',
    icon: '🎓',
    size: '2.1 MB',
    lastUpdated: '-',
    available: false, // Not graduated yet
    format: 'PDF'
  }
])

const documentHistory = ref([
  {
    id: 1,
    nama_dokumen: 'KTM Digital',
    jenis: 'Download',
    tanggal: '2024-09-20',
    ukuran: '180 KB',
    status: 'Berhasil'
  },
  {
    id: 2,
    nama_dokumen: 'Surat Keterangan Aktif',
    jenis: 'Download',
    tanggal: '2024-09-15',
    ukuran: '320 KB',
    status: 'Berhasil'
  },
  {
    id: 3,
    nama_dokumen: 'Surat Izin Sakit',
    jenis: 'Upload',
    tanggal: '2024-09-10',
    ukuran: '1.1 MB',
    status: 'Disetujui'
  }
])

// Table columns
const historyColumns = [
  { key: 'nama_dokumen', label: 'Nama Dokumen', sortable: true },
  { key: 'jenis', label: 'Jenis', align: 'center' },
  { key: 'tanggal', label: 'Tanggal', sortable: true, format: 'date' },
  { key: 'ukuran', label: 'Ukuran', align: 'center' },
  { key: 'status', label: 'Status', align: 'center' }
]

// Methods
const downloadDocument = async (doc: any) => {
  downloading.value = doc.id
  try {
    // Simulate download
    await new Promise(resolve => setTimeout(resolve, 2000))
    
    // Add to history
    documentHistory.value.unshift({
      id: Date.now(),
      nama_dokumen: doc.name,
      jenis: 'Download',
      tanggal: new Date().toISOString().split('T')[0],
      ukuran: doc.size,
      status: 'Berhasil'
    })
    
    console.log('Downloaded:', doc.name)
  } catch (error) {
    console.error('Download failed:', error)
  } finally {
    downloading.value = null
  }
}

const handleFileSelect = (event: Event) => {
  const files = (event.target as HTMLInputElement).files
  if (files) {
    uploadedFiles.value = [...uploadedFiles.value, ...Array.from(files)]
  }
}

const handleDrop = (event: DragEvent) => {
  event.preventDefault()
  const files = event.dataTransfer?.files
  if (files) {
    uploadedFiles.value = [...uploadedFiles.value, ...Array.from(files)]
  }
}

const removeFile = (index: number) => {
  uploadedFiles.value.splice(index, 1)
}

const formatFileSize = (bytes: number) => {
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  if (bytes === 0) return '0 Bytes'
  const i = Math.floor(Math.log(bytes) / Math.log(1024))
  return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i]
}

const uploadFiles = async () => {
  uploading.value = true
  try {
    // Simulate upload
    await new Promise(resolve => setTimeout(resolve, 3000))
    
    // Add to history
    uploadedFiles.value.forEach(file => {
      documentHistory.value.unshift({
        id: Date.now() + Math.random(),
        nama_dokumen: file.name,
        jenis: 'Upload',
        tanggal: new Date().toISOString().split('T')[0],
        ukuran: formatFileSize(file.size),
        status: 'Menunggu Verifikasi'
      })
    })
    
    uploadedFiles.value = []
    console.log('Files uploaded successfully')
  } catch (error) {
    console.error('Upload failed:', error)
  } finally {
    uploading.value = false
  }
}

const viewDocumentDetail = (doc: any) => {
  console.log('View document detail:', doc)
}

// Lifecycle
onMounted(() => {
  // Load document data
})
</script>

<style scoped>
.mahasiswa-berkas {
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

.document-grid {
  display: grid;
  gap: 20px;
}

.document-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  transition: all 0.2s ease;
}

.document-card:hover:not(.disabled) {
  border-color: #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
}

.document-card.disabled {
  opacity: 0.6;
  background: #f9fafb;
}

.document-icon {
  font-size: 2.5rem;
  flex-shrink: 0;
}

.document-info {
  flex: 1;
  min-width: 0;
}

.document-info h4 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.document-info p {
  color: #6b7280;
  font-size: 0.9rem;
  margin: 0 0 8px 0;
}

.document-meta {
  display: flex;
  gap: 16px;
  font-size: 0.8rem;
  color: #9ca3af;
}

.document-actions {
  flex-shrink: 0;
}

.upload-section {
  padding: 20px;
}

.upload-info {
  color: #6b7280;
  margin-bottom: 24px;
  line-height: 1.6;
}

.upload-area {
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  padding: 40px 20px;
  text-align: center;
  transition: all 0.2s ease;
  cursor: pointer;
}

.upload-area:hover {
  border-color: #667eea;
  background: #f8faff;
}

.upload-icon {
  font-size: 3rem;
  margin-bottom: 16px;
}

.upload-area h4 {
  font-size: 1.2rem;
  color: #374151;
  margin: 0 0 8px 0;
}

.upload-area p {
  color: #6b7280;
  margin: 0 0 20px 0;
}

.uploaded-files {
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid #e5e7eb;
}

.uploaded-files h5 {
  color: #374151;
  margin-bottom: 16px;
}

.file-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 8px;
  margin-bottom: 8px;
}

.file-name {
  flex: 1;
  font-weight: 500;
  color: #374151;
}

.file-size {
  color: #6b7280;
  font-size: 0.9rem;
}

.info-sections {
  display: grid;
  gap: 24px;
}

.info-section {
  padding: 20px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}

.info-section h4 {
  color: #1f2937;
  margin: 0 0 12px 0;
  font-size: 1.1rem;
}

.info-section p {
  color: #6b7280;
  line-height: 1.6;
  margin: 0 0 12px 0;
}

.info-section ul {
  margin: 0;
  padding-left: 20px;
}

.info-section li {
  color: #6b7280;
  margin-bottom: 4px;
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

.btn--disabled {
  background: #f3f4f6;
  color: #9ca3af;
  cursor: not-allowed;
}

.btn--sm {
  padding: 4px 8px;
  font-size: 0.8rem;
}

.btn--danger {
  background: #ef4444;
  color: white;
}

.btn--danger:hover {
  background: #dc2626;
}

@media (max-width: 768px) {
  .document-card {
    flex-direction: column;
    text-align: center;
  }
  
  .document-meta {
    justify-content: center;
  }
  
  .file-item {
    flex-direction: column;
    text-align: center;
    gap: 8px;
  }
}
</style>