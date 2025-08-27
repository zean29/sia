# SIA - Dummy Data Testing Guide

This document provides comprehensive information about the dummy data created for testing the SIA (Sistem Informasi Akademik) application.

## 🗃️ Database Overview

The database has been successfully populated with realistic dummy data for testing all application features.

### Data Statistics:
- **Total Users**: 7
- **Admin Users**: 2
- **Students (Mahasiswa)**: 2
- **Lecturers (Dosen)**: 2
- **Staff (Staf)**: 1
- **Faculties (Fakultas)**: 3
- **Study Programs (Program Studi)**: 5
- **Courses (Mata Kuliah)**: 4
- **Class Schedules (Jadwal Kelas)**: 2

## 🔐 Test Login Credentials

### Admin Accounts
| Email | Password | Role | Description |
|-------|----------|------|-------------|
| `admin@sia.ac.id` | `admin123` | admin | Main administrator |
| `superadmin@sia.ac.id` | `superadmin123` | admin | Super administrator |

### Student Accounts (Mahasiswa)
| Email | Password | Role | Name | Program | NIM |
|-------|----------|------|------|---------|-----|
| `ahmad.sutanto@student.sia.ac.id` | `mahasiswa123` | mahasiswa | Ahmad Sutanto | Teknik Informatika | 24010001 |
| `siti.nurhaliza@student.sia.ac.id` | `mahasiswa123` | mahasiswa | Siti Nurhaliza | Sistem Informasi | 24010002 |

### Lecturer Accounts (Dosen)
| Email | Password | Role | Name | Faculty | Specialization |
|-------|----------|------|------|---------|----------------|
| `bambang.suharto@sia.ac.id` | `dosen123` | dosen | Dr. Ir. Bambang Suharto, M.T. | Fakultas Teknik | Programming, Database, AI |
| `citra.dewi@sia.ac.id` | `dosen123` | dosen | Dr. Ir. Citra Dewi, M.Kom. | Fakultas Teknik | Information Systems, E-Business |

### Staff Accounts (Staf)
| Email | Password | Role | Name | Position |
|-------|----------|------|------|----------|
| `rini.kusuma@sia.ac.id` | `staf123` | staf | Rini Kusuma, S.Kom. | Academic Staff |

## 🏛️ Academic Structure

### Faculties (Fakultas)
1. **Fakultas Teknik (FT)**
   - Dean: Prof. Dr. Ir. Ahmad Susanto, M.T.
   - Study Programs: Teknik Informatika, Sistem Informasi

2. **Fakultas Ekonomi (FE)**
   - Dean: Prof. Dr. Siti Nurhaliza, S.E., M.M.
   - Study Programs: Manajemen, Akuntansi

3. **Fakultas Ilmu Komunikasi (FIKOM)**
   - Dean: Dr. Maya Sari, S.Sos., M.I.Kom.
   - Study Programs: Ilmu Komunikasi

### Study Programs (Program Studi)
| Code | Name | Faculty | Level | Accreditation |
|------|------|---------|-------|---------------|
| TI | Teknik Informatika | Fakultas Teknik | S1 | A |
| SI | Sistem Informasi | Fakultas Teknik | S1 | B+ |
| MJ | Manajemen | Fakultas Ekonomi | S1 | A |
| AK | Akuntansi | Fakultas Ekonomi | S1 | B+ |
| IKOM | Ilmu Komunikasi | Fakultas Ilmu Komunikasi | S1 | B |

## 📚 Academic Data

### Academic Periods (Periode Akademik)
| Code | Name | Status | Start Date | End Date |
|------|------|--------|------------|----------|
| 20241 | Semester Ganjil 2024/2025 | Active | 2024-09-01 | 2025-01-31 |
| 20232 | Semester Genap 2023/2024 | Completed | 2024-02-01 | 2024-07-31 |

### Courses (Mata Kuliah)
| Code | Name | Program | Credits | Semester |
|------|------|---------|---------|----------|
| TI101 | Algoritma dan Pemrograman | Teknik Informatika | 3 | 1 |
| TI102 | Basis Data | Teknik Informatika | 3 | 3 |
| SI101 | Pengantar Sistem Informasi | Sistem Informasi | 3 | 1 |
| SI102 | Analisis dan Perancangan Sistem | Sistem Informasi | 3 | 4 |

### Class Schedules (Jadwal Kelas)
| Course | Lecturer | Class | Room | Day | Time |
|--------|----------|-------|------|-----|------|
| Algoritma dan Pemrograman | Dr. Bambang Suharto | A | R.101 | Monday | 08:00-10:30 |
| Pengantar Sistem Informasi | Dr. Citra Dewi | A | R.201 | Tuesday | 10:30-13:00 |

## 🔧 API Testing

### Base API URL
```
http://127.0.0.1:8000/api
```

### Test API Status
```bash
GET http://127.0.0.1:8000/api
```

### Authentication Endpoints

#### Login
```bash
POST http://127.0.0.1:8000/api/autentikasi/masuk
Content-Type: application/json

{
  "email": "admin@sia.ac.id",
  "kata_sandi": "admin123"
}
```

#### Get User Info
```bash
GET http://127.0.0.1:8000/api/autentikasi/saya
Authorization: Bearer {token}
```

#### Logout
```bash
POST http://127.0.0.1:8000/api/autentikasi/keluar
Authorization: Bearer {token}
```

## 🖥️ Frontend Testing

### Application URLs
- **Frontend**: http://localhost:5174
- **Backend**: http://127.0.0.1:8000

### Testing Scenarios

#### Admin Dashboard Testing
1. Login with admin credentials
2. Access admin dashboard features:
   - View statistics
   - Manage students, lecturers, staff
   - Manage courses and schedules
   - Generate reports

#### Student Portal Testing
1. Login with student credentials
2. Test student features:
   - View profile
   - Course registration (KRS)
   - View grades
   - Payment status
   - Academic transcript

#### Lecturer Portal Testing
1. Login with lecturer credentials
2. Test lecturer features:
   - View teaching schedule
   - Input grades
   - View student list

#### Staff Portal Testing
1. Login with staff credentials
2. Test staff features:
   - Academic administration
   - Student data management

## 📋 Testing Checklist

### ✅ Completed
- [x] Database structure created
- [x] Dummy data seeded successfully
- [x] All user types can login
- [x] API authentication working
- [x] Frontend components created
- [x] Backend API routes configured

### 🔄 Available for Testing
- [ ] Complete student workflow (enrollment, courses, grades)
- [ ] Complete lecturer workflow (teaching, grading)
- [ ] Complete admin workflow (management, reports)
- [ ] Payment system testing
- [ ] Document upload/download
- [ ] Academic transcript generation
- [ ] Report generation
- [ ] PDDIKTI integration testing

## 🚀 Quick Start Testing

1. **Start the application** (if not already running):
   ```bash
   # Backend
   cd backend
   php artisan serve

   # Frontend
   cd frontend
   npm run dev
   ```

2. **Access the frontend**: http://localhost:5174

3. **Login with any test credentials** listed above

4. **Test different user roles** to see role-based features

## 📝 Notes

- All passwords are simple for testing purposes (`admin123`, `mahasiswa123`, `dosen123`, `staf123`)
- Data is realistic but fictional
- Database uses SQLite for easy testing
- All features are ready for comprehensive testing
- Additional test data can be added by modifying the seeder

## 🔍 Troubleshooting

### Common Issues
1. **Login not working**: Ensure backend server is running on port 8000
2. **API errors**: Check Laravel logs in `storage/logs/laravel.log`
3. **Frontend errors**: Check browser console for JavaScript errors
4. **Database issues**: Re-run `php artisan migrate:refresh --seed`

### Support
For any issues with the dummy data or testing, check:
- Laravel server logs
- Browser developer console
- Database connection
- API endpoint responses

---

**Happy Testing! 🎉**

The SIA application is now fully loaded with comprehensive dummy data and ready for thorough testing of all features and user workflows.