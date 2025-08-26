<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\Nilai;
use App\Models\IntegrasiPddikti;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class PddiktiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $universityId;

    public function __construct()
    {
        $this->baseUrl = config('services.pddikti.base_url', 'https://api-pddikti.kemdikbud.go.id/v1');
        $this->apiKey = config('services.pddikti.api_key', '');
        $this->universityId = config('services.pddikti.university_id', '');
    }

    /**
     * Sync student data to PDDIKTI.
     */
    public function syncMahasiswa(Mahasiswa $mahasiswa): array
    {
        try {
            // Check if already synced
            $existing = IntegrasiPddikti::where('entitas_type', Mahasiswa::class)
                ->where('entitas_id', $mahasiswa->id)
                ->where('jenis_data', 'mahasiswa')
                ->first();

            // Prepare data for PDDIKTI format
            $pddiktiData = $this->formatMahasiswaData($mahasiswa);

            // Make API call to PDDIKTI
            $response = $this->makeApiCall('/mahasiswa', $pddiktiData, $existing ? 'PUT' : 'POST');

            if ($response['success']) {
                // Update or create integration record
                $integrationData = [
                    'entitas_type' => Mahasiswa::class,
                    'entitas_id' => $mahasiswa->id,
                    'jenis_data' => 'mahasiswa',
                    'status_sinkronisasi' => 'sync_berhasil',
                    'terakhir_sync' => now(),
                    'data_terkirim' => $pddiktiData,
                    'respon_pddikti' => $response['data'],
                    'percobaan_sync' => ($existing->percobaan_sync ?? 0) + 1,
                ];

                if (isset($response['data']['id_pddikti'])) {
                    $integrationData['id_pddikti'] = $response['data']['id_pddikti'];
                }

                if ($existing) {
                    $existing->update($integrationData);
                } else {
                    IntegrasiPddikti::create($integrationData);
                }

                return [
                    'success' => true,
                    'message' => 'Mahasiswa berhasil disinkronisasi ke PDDIKTI',
                    'pddikti_id' => $response['data']['id_pddikti'] ?? null
                ];
            } else {
                // Update failed sync record
                $this->updateFailedSync($mahasiswa, 'mahasiswa', $response, $existing);
                
                return [
                    'success' => false,
                    'message' => 'Gagal sinkronisasi ke PDDIKTI: ' . $response['error']
                ];
            }

        } catch (\Exception $e) {
            Log::error('PDDIKTI Sync Error - Mahasiswa', [
                'mahasiswa_id' => $mahasiswa->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sync lecturer data to PDDIKTI.
     */
    public function syncDosen(Dosen $dosen): array
    {
        try {
            $existing = IntegrasiPddikti::where('entitas_type', Dosen::class)
                ->where('entitas_id', $dosen->id)
                ->where('jenis_data', 'dosen')
                ->first();

            $pddiktiData = $this->formatDosenData($dosen);
            $response = $this->makeApiCall('/dosen', $pddiktiData, $existing ? 'PUT' : 'POST');

            if ($response['success']) {
                $integrationData = [
                    'entitas_type' => Dosen::class,
                    'entitas_id' => $dosen->id,
                    'jenis_data' => 'dosen',
                    'status_sinkronisasi' => 'sync_berhasil',
                    'terakhir_sync' => now(),
                    'data_terkirim' => $pddiktiData,
                    'respon_pddikti' => $response['data'],
                    'percobaan_sync' => ($existing->percobaan_sync ?? 0) + 1,
                ];

                if (isset($response['data']['id_pddikti'])) {
                    $integrationData['id_pddikti'] = $response['data']['id_pddikti'];
                }

                if ($existing) {
                    $existing->update($integrationData);
                } else {
                    IntegrasiPddikti::create($integrationData);
                }

                return [
                    'success' => true,
                    'message' => 'Dosen berhasil disinkronisasi ke PDDIKTI'
                ];
            } else {
                $this->updateFailedSync($dosen, 'dosen', $response, $existing);
                return [
                    'success' => false,
                    'message' => 'Gagal sinkronisasi ke PDDIKTI: ' . $response['error']
                ];
            }

        } catch (\Exception $e) {
            Log::error('PDDIKTI Sync Error - Dosen', [
                'dosen_id' => $dosen->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sync course data to PDDIKTI.
     */
    public function syncMataKuliah(MataKuliah $mataKuliah): array
    {
        try {
            $existing = IntegrasiPddikti::where('entitas_type', MataKuliah::class)
                ->where('entitas_id', $mataKuliah->id)
                ->where('jenis_data', 'mata_kuliah')
                ->first();

            $pddiktiData = $this->formatMataKuliahData($mataKuliah);
            $response = $this->makeApiCall('/mata-kuliah', $pddiktiData, $existing ? 'PUT' : 'POST');

            if ($response['success']) {
                $integrationData = [
                    'entitas_type' => MataKuliah::class,
                    'entitas_id' => $mataKuliah->id,
                    'jenis_data' => 'mata_kuliah',
                    'status_sinkronisasi' => 'sync_berhasil',
                    'terakhir_sync' => now(),
                    'data_terkirim' => $pddiktiData,
                    'respon_pddikti' => $response['data'],
                ];

                if ($existing) {
                    $existing->update($integrationData);
                } else {
                    IntegrasiPddikti::create($integrationData);
                }

                return [
                    'success' => true,
                    'message' => 'Mata kuliah berhasil disinkronisasi ke PDDIKTI'
                ];
            } else {
                $this->updateFailedSync($mataKuliah, 'mata_kuliah', $response, $existing);
                return [
                    'success' => false,
                    'message' => 'Gagal sinkronisasi ke PDDIKTI: ' . $response['error']
                ];
            }

        } catch (\Exception $e) {
            Log::error('PDDIKTI Sync Error - Mata Kuliah', [
                'mata_kuliah_id' => $mataKuliah->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sync grades to PDDIKTI.
     */
    public function syncNilai(Nilai $nilai): array
    {
        try {
            // Only sync final grades
            if ($nilai->status !== 'final') {
                return [
                    'success' => false,
                    'message' => 'Hanya nilai final yang dapat disinkronisasi'
                ];
            }

            $existing = IntegrasiPddikti::where('entitas_type', Nilai::class)
                ->where('entitas_id', $nilai->id)
                ->where('jenis_data', 'nilai')
                ->first();

            $pddiktiData = $this->formatNilaiData($nilai);
            $response = $this->makeApiCall('/nilai', $pddiktiData, $existing ? 'PUT' : 'POST');

            if ($response['success']) {
                $integrationData = [
                    'entitas_type' => Nilai::class,
                    'entitas_id' => $nilai->id,
                    'jenis_data' => 'nilai',
                    'status_sinkronisasi' => 'sync_berhasil',
                    'terakhir_sync' => now(),
                    'data_terkirim' => $pddiktiData,
                    'respon_pddikti' => $response['data'],
                ];

                if ($existing) {
                    $existing->update($integrationData);
                } else {
                    IntegrasiPddikti::create($integrationData);
                }

                return [
                    'success' => true,
                    'message' => 'Nilai berhasil disinkronisasi ke PDDIKTI'
                ];
            } else {
                $this->updateFailedSync($nilai, 'nilai', $response, $existing);
                return [
                    'success' => false,
                    'message' => 'Gagal sinkronisasi ke PDDIKTI: ' . $response['error']
                ];
            }

        } catch (\Exception $e) {
            Log::error('PDDIKTI Sync Error - Nilai', [
                'nilai_id' => $nilai->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Format student data for PDDIKTI.
     */
    private function formatMahasiswaData(Mahasiswa $mahasiswa): array
    {
        return [
            'nim' => $mahasiswa->nim,
            'nama_mahasiswa' => $mahasiswa->data_pribadi['nama_lengkap'],
            'jenis_kelamin' => $mahasiswa->data_pribadi['jenis_kelamin'] === 'laki-laki' ? 'L' : 'P',
            'tanggal_lahir' => $mahasiswa->data_pribadi['tanggal_lahir'],
            'tempat_lahir' => $mahasiswa->data_pribadi['tempat_lahir'],
            'agama' => strtoupper($mahasiswa->data_pribadi['agama']),
            'kewarganegaraan' => $mahasiswa->data_pribadi['kewarganegaraan'],
            'program_studi' => $mahasiswa->programStudi->kode_program_studi,
            'jenjang' => $mahasiswa->programStudi->jenjang,
            'status_mahasiswa' => $this->mapStatusMahasiswa($mahasiswa->status),
            'tanggal_masuk' => $mahasiswa->tanggal_masuk->format('Y-m-d'),
            'semester_berjalan' => $mahasiswa->semester_aktif,
            'ipk' => $mahasiswa->ipk,
            'alamat' => $mahasiswa->data_kontak['alamat'] ?? '',
            'telepon' => $mahasiswa->data_kontak['telepon'] ?? '',
        ];
    }

    /**
     * Format lecturer data for PDDIKTI.
     */
    private function formatDosenData(Dosen $dosen): array
    {
        return [
            'nidn' => $dosen->nidn,
            'nama_dosen' => $dosen->data_pribadi['nama_lengkap'],
            'jenis_kelamin' => $dosen->data_pribadi['jenis_kelamin'] === 'laki-laki' ? 'L' : 'P',
            'tanggal_lahir' => $dosen->data_pribadi['tanggal_lahir'],
            'tempat_lahir' => $dosen->data_pribadi['tempat_lahir'],
            'agama' => strtoupper($dosen->data_pribadi['agama']),
            'jabatan_fungsional' => $this->mapJabatanFungsional($dosen->jabatan_fungsional),
            'status_kepegawaian' => strtoupper($dosen->status_kepegawaian),
            'fakultas' => $dosen->fakultas->kode_fakultas,
            'pendidikan_terakhir' => $dosen->kredensial_akademik['pendidikan_terakhir'] ?? 'S2',
            'bidang_keahlian' => implode(',', $dosen->bidang_keahlian ?? []),
        ];
    }

    /**
     * Format course data for PDDIKTI.
     */
    private function formatMataKuliahData(MataKuliah $mataKuliah): array
    {
        return [
            'kode_mata_kuliah' => $mataKuliah->kode_mata_kuliah,
            'nama_mata_kuliah' => $mataKuliah->nama_mata_kuliah,
            'sks' => $mataKuliah->sks,
            'semester' => $mataKuliah->semester_rekomendasi,
            'jenis_mata_kuliah' => strtoupper($mataKuliah->jenis),
            'program_studi' => $mataKuliah->programStudi->kode_program_studi,
            'status' => $mataKuliah->status === 'aktif' ? 'AKTIF' : 'TIDAK_AKTIF',
        ];
    }

    /**
     * Format grade data for PDDIKTI.
     */
    private function formatNilaiData(Nilai $nilai): array
    {
        return [
            'nim' => $nilai->mahasiswa->nim,
            'kode_mata_kuliah' => $nilai->mataKuliah->kode_mata_kuliah,
            'nilai_angka' => $nilai->nilai_angka,
            'nilai_huruf' => $nilai->nilai_huruf,
            'nilai_indeks' => $nilai->nilai_indeks,
            'semester' => $nilai->periodeAkademik->jenis_periode,
            'tahun_akademik' => $nilai->periodeAkademik->getTahunAkademikAttribute(),
            'status_lulus' => $nilai->lulus ? 'LULUS' : 'TIDAK_LULUS',
        ];
    }

    /**
     * Make API call to PDDIKTI.
     */
    private function makeApiCall(string $endpoint, array $data, string $method = 'POST'): array
    {
        try {
            $headers = [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'X-University-ID' => $this->universityId,
            ];

            $response = Http::withHeaders($headers)
                ->timeout(30);

            switch (strtoupper($method)) {
                case 'POST':
                    $response = $response->post($this->baseUrl . $endpoint, $data);
                    break;
                case 'PUT':
                    $response = $response->put($this->baseUrl . $endpoint, $data);
                    break;
                default:
                    throw new \Exception('Unsupported HTTP method: ' . $method);
            }

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status_code' => $response->status()
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response->body(),
                    'status_code' => $response->status()
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => 0
            ];
        }
    }

    /**
     * Update failed sync record.
     */
    private function updateFailedSync($model, string $jenis, array $response, $existing = null): void
    {
        $integrationData = [
            'entitas_type' => get_class($model),
            'entitas_id' => $model->id,
            'jenis_data' => $jenis,
            'status_sinkronisasi' => 'sync_gagal',
            'terakhir_sync' => now(),
            'pesan_error' => $response['error'],
            'percobaan_sync' => ($existing->percobaan_sync ?? 0) + 1,
        ];

        if ($existing) {
            $existing->update($integrationData);
        } else {
            IntegrasiPddikti::create($integrationData);
        }
    }

    /**
     * Map student status to PDDIKTI format.
     */
    private function mapStatusMahasiswa(string $status): string
    {
        $mapping = [
            'aktif' => 'AKTIF',
            'tidak_aktif' => 'TIDAK_AKTIF',
            'lulus' => 'LULUS',
            'do' => 'DROP_OUT',
            'skorsing' => 'SKORSING',
            'cuti' => 'CUTI',
        ];

        return $mapping[$status] ?? 'AKTIF';
    }

    /**
     * Map functional position to PDDIKTI format.
     */
    private function mapJabatanFungsional(string $jabatan): string
    {
        $mapping = [
            'asisten_ahli' => 'ASISTEN_AHLI',
            'lektor' => 'LEKTOR',
            'lektor_kepala' => 'LEKTOR_KEPALA',
            'guru_besar' => 'GURU_BESAR',
            'tidak_ada' => 'TIDAK_ADA',
        ];

        return $mapping[$jabatan] ?? 'TIDAK_ADA';
    }

    /**
     * Get synchronization status.
     */
    public function getStatusSync(): array
    {
        $stats = [
            'total_mahasiswa' => Mahasiswa::count(),
            'mahasiswa_synced' => IntegrasiPddikti::where('jenis_data', 'mahasiswa')
                ->where('status_sinkronisasi', 'sync_berhasil')->count(),
            
            'total_dosen' => Dosen::count(),
            'dosen_synced' => IntegrasiPddikti::where('jenis_data', 'dosen')
                ->where('status_sinkronisasi', 'sync_berhasil')->count(),
            
            'total_mata_kuliah' => MataKuliah::count(),
            'mata_kuliah_synced' => IntegrasiPddikti::where('jenis_data', 'mata_kuliah')
                ->where('status_sinkronisasi', 'sync_berhasil')->count(),
            
            'total_nilai' => Nilai::where('status', 'final')->count(),
            'nilai_synced' => IntegrasiPddikti::where('jenis_data', 'nilai')
                ->where('status_sinkronisasi', 'sync_berhasil')->count(),
        ];

        $stats['total_failed'] = IntegrasiPddikti::where('status_sinkronisasi', 'sync_gagal')->count();
        $stats['last_sync'] = IntegrasiPddikti::max('terakhir_sync');

        return $stats;
    }
}