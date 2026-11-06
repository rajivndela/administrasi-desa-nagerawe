<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\Dusun;
use App\Models\JenisSurat;
use App\Models\Keluarga;
use App\Models\LogAktivitas;
use App\Models\Notifikasi;
use App\Models\Penduduk;
use App\Models\Pengaduan;
use App\Models\Pengajuan;
use App\Models\Rt;
use App\Models\Surat;
use App\Models\User;
use App\Models\VerifikasiSurat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
{
    private const ADMIN_ONLY = ['penduduk', 'keluarga', 'wilayah', 'jenis-surat', 'pengguna'];

    private const TITLES = [
        'penduduk' => ['Data Penduduk', 'Kelola identitas dan informasi warga desa.'],
        'keluarga' => ['Data Keluarga', 'Kelola kartu keluarga dan alamat tempat tinggal.'],
        'surat' => ['Pengajuan Surat', 'Pantau, proses, dan verifikasi layanan surat warga.'],
        'pengaduan' => ['Pengaduan Warga', 'Terima pengaduan dan sampaikan tindak lanjut.'],
        'arsip' => ['Arsip Surat', 'Simpan dan telusuri dokumen administrasi desa.'],
        'wilayah' => ['Wilayah Dusun & RT', 'Kelola wilayah dusun dan pengurus RT.'],
        'jenis-surat' => ['Jenis Layanan Surat', 'Atur jenis surat dan standar waktu pelayanan.'],
        'pengguna' => ['Pengguna Sistem', 'Kelola akun administrator dan perangkat desa.'],
        'notifikasi' => ['Notifikasi', 'Informasi perkembangan layanan dan aktivitas desa.'],
    ];

    public function index(Request $request, string $module)
    {
        $this->authorizeModule($request, $module);

        [$title, $description] = self::TITLES[$module];
        if ($module === 'surat') {
            [$title, $description] = match ($request->user()->role) {
                'admin' => ['Proses Pengajuan Surat', 'Periksa kelengkapan dokumen sebelum diteruskan ke Kepala Desa.'],
                'kepala_desa' => ['Persetujuan Surat', 'Tinjau dan setujui atau tolak surat yang sudah diperiksa.'],
                default => ['Ajukan Surat', 'Ajukan layanan administrasi dan pantau status permohonan Anda.'],
            };
        } elseif ($module === 'pengaduan') {
            [$title, $description] = $request->user()->role === 'penduduk'
                ? ['Pengaduan Saya', 'Sampaikan aspirasi dan pantau tindak lanjut dari pemerintah desa.']
                : ['Pengaduan Warga', 'Tinjau pengaduan dan catat tindak lanjut untuk masyarakat.'];
        } elseif ($module === 'arsip' && $request->user()->role === 'penduduk') {
            [$title, $description] = ['Arsip Surat Saya', 'Lihat dokumen surat yang sudah selesai diterbitkan.'];
        }
        $fields = $this->fields($module);
        $rows = $this->rows($request, $module);
        $rolePrefix = $this->rolePrefix($request);

        return view($rolePrefix . '.' . $module, [
            'rolePrefix' => $rolePrefix,
            'module' => $module,
            'title' => $title,
            'description' => $description,
            'fields' => $fields,
            'rows' => $rows,
            'canCreate' => $fields !== []
                && ($module !== 'surat' || $request->user()->role === 'penduduk')
                && ($module !== 'pengaduan' || $request->user()->role === 'penduduk')
                && ($module !== 'arsip' || $request->user()->role === 'admin'),
            'canManage' => in_array($request->user()->role, ['admin', 'kepala_desa'], true),
            'workflowOptions' => $request->user()->role === 'admin'
                ? ['diproses' => 'Mulai proses', 'selesai' => 'Terbitkan surat']
                : ['disetujui' => 'Setujui / tanda tangani', 'ditolak' => 'Tolak pengajuan'],
        ]);
    }

    public function store(Request $request, string $module)
    {
        $this->authorizeModule($request, $module);
        if (in_array($module, ['surat', 'pengaduan'], true)) {
            abort_unless($request->user()->role === 'penduduk', 403);
        }
        if ($module === 'arsip') {
            $this->authorizeAdmin($request);
        }
        $data = $this->validatedData($request, $module);
        $user = $request->user();

        DB::transaction(function () use ($module, $data, $user, $request) {
            switch ($module) {
                case 'penduduk':
                    Penduduk::create($data);
                    break;
                case 'keluarga':
                    Keluarga::create($data);
                    break;
                case 'jenis-surat':
                    JenisSurat::create($data);
                    break;
                case 'wilayah':
                    $dusun = Dusun::create(['nama_dusun' => $data['nama_dusun']]);
                    if (! empty($data['no_rt'])) {
                        Rt::create([
                            'dusun_id' => $dusun->id,
                            'no_rt' => $data['no_rt'],
                            'nama_ketua_rt' => $data['nama_ketua_rt'] ?? null,
                            'no_hp' => $data['no_hp'] ?? null,
                        ]);
                    }
                    break;
                case 'pengguna':
                    User::create($data);
                    break;
                case 'surat':
                    $resident = $user->penduduk;
                    if (! $resident) {
                        abort(422, 'Profil penduduk belum terhubung dengan akun ini.');
                    }
                    $pengajuan = Pengajuan::create([
                        'penduduk_id' => $resident->id,
                        'jenis_surat_id' => $data['jenis_surat_id'],
                        'keterangan' => $data['keterangan'] ?? null,
                    ]);
                    $this->notify($user->id, 'Pengajuan surat Anda telah diterima dan menunggu verifikasi.');
                    $this->activity($request, 'Mengajukan surat #' . $pengajuan->id);
                    break;
                case 'pengaduan':
                    $resident = $user->penduduk;
                    if (! $resident) {
                        abort(422, 'Profil penduduk belum terhubung dengan akun ini.');
                    }
                    $complaint = Pengaduan::create([
                        'penduduk_id' => $resident->id,
                        'isi_pengaduan' => $data['isi_pengaduan'],
                    ]);
                    $this->notify($user->id, 'Pengaduan Anda telah dikirim dan akan ditindaklanjuti.');
                    $this->activity($request, 'Mengirim pengaduan #' . $complaint->id);
                    break;
                case 'arsip':
                    Arsip::create($data + ['user_id' => $user->id]);
                    break;
                default:
                    abort(404);
            }

            if (in_array($module, ['penduduk', 'keluarga', 'jenis-surat', 'wilayah', 'pengguna', 'arsip'], true)) {
                $this->activity($request, 'Menambahkan data ' . self::TITLES[$module][0]);
            }
        });

        return redirect()->route($this->rolePrefix($request) . '.modules.index', $module)->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, string $module, int $id)
    {
        $this->authorizeModule($request, $module);

        if ($module === 'notifikasi') {
            $notification = Notifikasi::where('user_id', $request->user()->id)->findOrFail($id);
            $notification->update(['status_baca' => true]);

            return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
        }

        if ($module === 'surat') {
            $role = $request->user()->role;
            abort_unless(in_array($role, ['admin', 'kepala_desa'], true), 403);
            $allowedStatuses = $role === 'admin' ? ['diproses', 'selesai'] : ['disetujui', 'ditolak'];
            $data = $request->validate([
                'status' => ['required', Rule::in($allowedStatuses)],
                'catatan' => ['nullable', 'string', 'max:1000'],
            ]);

            DB::transaction(function () use ($request, $id, $data) {
                $application = Pengajuan::with(['penduduk.user', 'jenisSurat'])->findOrFail($id);
                $allowedFrom = match ($data['status']) {
                    'diproses' => ['menunggu'],
                    'disetujui', 'ditolak' => ['diproses'],
                    'selesai' => ['disetujui'],
                };
                abort_unless(
                    in_array($application->status, $allowedFrom, true),
                    422,
                    $data['status'] === 'selesai'
                        ? 'Pengajuan harus disetujui Kepala Desa sebelum surat diterbitkan.'
                        : 'Status pengajuan tidak mendukung perubahan ini.'
                );

                $application->update([
                    'status' => $data['status'],
                    'waktu_mulai_proses' => $application->waktu_mulai_proses ?? now(),
                    'waktu_selesai' => $data['status'] === 'selesai' ? now() : null,
                ]);

                VerifikasiSurat::create([
                    'pengajuan_id' => $application->id,
                    'user_id' => $request->user()->id,
                    'status_verifikasi' => $data['status'],
                    'catatan' => $data['catatan'] ?? null,
                ]);

                if ($data['status'] === 'selesai') {
                    $letter = Surat::firstOrCreate(
                        ['pengajuan_id' => $application->id],
                        [
                            'no_surat' => sprintf('400/%03d/%s', $application->id, now()->format('Y')),
                            'tgl_terbit' => now()->toDateString(),
                            'qr_code' => (string) Str::uuid(),
                            'ditandatangani_oleh' => $request->user()->id,
                        ]
                    );
                    Arsip::firstOrCreate(
                        ['surat_id' => $letter->id],
                        [
                            'user_id' => $application->penduduk->user_id,
                            'no_dokumen' => $letter->no_surat,
                            'tanggal' => $letter->tgl_terbit,
                            'perihal' => $application->jenisSurat->nama_surat,
                            'status' => 'tersimpan',
                        ]
                    );
                }

                if ($application->penduduk->user_id) {
                    $this->notify(
                        $application->penduduk->user_id,
                        'Status pengajuan surat ' . $application->jenisSurat->nama_surat . ' berubah menjadi ' . $data['status'] . '.'
                    );
                }
                $this->activity($request, 'Memperbarui status pengajuan surat #' . $application->id);
            });

            return back()->with('success', 'Status pengajuan berhasil diperbarui.');
        }

        if ($module === 'pengaduan') {
            abort_unless(in_array($request->user()->role, ['admin', 'kepala_desa'], true), 403);
            $data = $request->validate([
                'status' => ['required', Rule::in(['baru', 'ditindaklanjuti', 'selesai'])],
                'tanggapan' => ['required', 'string', 'max:2000'],
            ]);
            $complaint = Pengaduan::with('penduduk')->findOrFail($id);
            $complaint->update($data + ['ditanggapi_oleh' => $request->user()->id]);
            if ($complaint->penduduk->user_id) {
                $this->notify($complaint->penduduk->user_id, 'Ada tanggapan baru untuk pengaduan #' . $complaint->id . '.');
            }
            $this->activity($request, 'Menanggapi pengaduan #' . $complaint->id);

            return back()->with('success', 'Tanggapan pengaduan berhasil disimpan.');
        }

        abort(404);
    }

    public function destroy(Request $request, string $module, int $id)
    {
        $this->authorizeAdmin($request);

        $model = match ($module) {
            'penduduk' => Penduduk::class,
            'keluarga' => Keluarga::class,
            'arsip' => Arsip::class,
            'wilayah' => Dusun::class,
            'jenis-surat' => JenisSurat::class,
            'pengguna' => User::class,
            default => abort(404),
        };

        $record = $model::findOrFail($id);
        abort_if($record instanceof User && $record->is($request->user()), 422, 'Akun yang sedang digunakan tidak dapat dihapus.');
        $record->delete();
        $this->activity($request, 'Menghapus data ' . self::TITLES[$module][0]);

        return back()->with('success', 'Data berhasil dihapus.');
    }

    public function report(Request $request)
    {
        $this->authorizeStaff($request);
        $stats = [
            'Penduduk' => Penduduk::count(),
            'Keluarga' => Keluarga::count(),
            'Pengajuan surat' => Pengajuan::count(),
            'Surat selesai' => Pengajuan::where('status', 'selesai')->count(),
            'Pengaduan' => Pengaduan::count(),
            'Arsip dokumen' => Arsip::count(),
        ];
        $monthly = array_fill(1, 12, 0);
        foreach (Pengajuan::whereYear('created_at', now()->year)->get(['created_at']) as $application) {
            $monthly[$application->created_at->month]++;
        }

        $rolePrefix = $this->rolePrefix($request);
        $title = 'Laporan Desa';

        return view($rolePrefix . '.laporan', compact('stats', 'monthly', 'rolePrefix', 'title'));
    }

    public function printLetter(Request $request, int $id)
    {
        $letter = Pengajuan::with(['jenisSurat', 'penduduk.user', 'surat.ditandatanganiOleh'])
            ->where('status', 'selesai')
            ->findOrFail($id);

        if ($request->user()->role === 'penduduk') {
            abort_unless($letter->penduduk->user_id === $request->user()->id, 403);
        } else {
            $this->authorizeStaff($request);
        }

        abort_unless($letter->surat, 404);

        return view($this->rolePrefix($request) . '.print-letter', compact('letter'));
    }

    private function rolePrefix(Request $request): string
    {
        return match ($request->user()->role) {
            'admin' => 'admin',
            'kepala_desa' => 'kepala-desa',
            default => 'penduduk',
        };
    }

    private function authorizeModule(Request $request, string $module): void
    {
        abort_unless(isset(self::TITLES[$module]), 404);
        if (in_array($module, self::ADMIN_ONLY, true)) {
            $this->authorizeAdmin($request);
        }
        if ($module === 'arsip') {
            $this->authorizeStaffOrResident($request);
        }
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403, 'Menu ini hanya dapat diakses administrator.');
    }

    private function authorizeStaff(Request $request): void
    {
        abort_unless(in_array($request->user()->role, ['admin', 'kepala_desa'], true), 403);
    }

    private function authorizeStaffOrResident(Request $request): void
    {
        abort_unless(in_array($request->user()->role, ['admin', 'kepala_desa', 'penduduk'], true), 403);
    }

    private function fields(string $module): array
    {
        return match ($module) {
            'penduduk' => [
                $this->field('keluarga_id', 'Nomor KK (opsional)', 'select', false, Keluarga::orderBy('no_kk')->pluck('no_kk', 'id')->all()),
                $this->field('nik', 'NIK', 'text', true, null, 16),
                $this->field('nama', 'Nama lengkap', 'text', true),
                $this->field('jenis_kelamin', 'Jenis kelamin', 'select', false, ['Laki-laki', 'Perempuan']),
                $this->field('alamat', 'Alamat', 'text', true),
                $this->field('dusun', 'Dusun', 'text'),
                $this->field('rt', 'RT', 'text'),
                $this->field('rw', 'RW', 'text'),
                $this->field('pekerjaan', 'Pekerjaan', 'text'),
            ],
            'keluarga' => [
                $this->field('rt_id', 'Wilayah RT (opsional)', 'select', false, Rt::with('dusun')->get()->mapWithKeys(fn ($rt) => [
                    $rt->id => $rt->dusun->nama_dusun . ' - RT ' . $rt->no_rt,
                ])->all()),
                $this->field('no_kk', 'Nomor KK', 'text', true),
                $this->field('kepala_keluarga', 'Kepala keluarga', 'text', true),
                $this->field('alamat', 'Alamat', 'text', true),
                $this->field('dusun', 'Dusun', 'text'),
                $this->field('rt', 'RT', 'text'),
                $this->field('rw', 'RW', 'text'),
            ],
            'jenis-surat' => [
                $this->field('nama_surat', 'Nama layanan', 'text', true),
                $this->field('deskripsi', 'Deskripsi', 'textarea'),
                $this->field('standar_waktu_layanan', 'Standar waktu (hari)', 'number', true),
            ],
            'wilayah' => [
                $this->field('nama_dusun', 'Nama dusun', 'text', true),
                $this->field('no_rt', 'Nomor RT (opsional)', 'text'),
                $this->field('nama_ketua_rt', 'Nama ketua RT', 'text'),
                $this->field('no_hp', 'Nomor telepon RT', 'text'),
            ],
            'pengguna' => [
                $this->field('name', 'Nama', 'text', true),
                $this->field('email', 'Email', 'email', true),
                $this->field('no_hp', 'Nomor telepon', 'text'),
                $this->field('role', 'Peran', 'select', true, ['admin', 'kepala_desa']),
                $this->field('password', 'Kata sandi sementara', 'password', true),
            ],
            'surat' => [
                $this->field('jenis_surat_id', 'Jenis surat', 'select', true, JenisSurat::where('aktif', true)->orderBy('nama_surat')->pluck('nama_surat', 'id')->all()),
                $this->field('keterangan', 'Keperluan / keterangan', 'textarea'),
            ],
            'pengaduan' => [$this->field('isi_pengaduan', 'Isi pengaduan', 'textarea', true)],
            'arsip' => [
                $this->field('no_dokumen', 'Nomor dokumen', 'text', true),
                $this->field('tanggal', 'Tanggal dokumen', 'date', true),
                $this->field('perihal', 'Perihal', 'text', true),
                $this->field('file', 'Tautan berkas (opsional)', 'text'),
            ],
            default => [],
        };
    }

    private function field(string $name, string $label, string $type, bool $required = false, ?array $options = null, ?int $max = null): array
    {
        return compact('name', 'label', 'type', 'required', 'options', 'max');
    }

    private function validatedData(Request $request, string $module): array
    {
        $rules = match ($module) {
            'penduduk' => [
                'keluarga_id' => ['nullable', 'exists:keluargas,id'],
                'nik' => ['required', 'digits:16', 'unique:penduduks,nik'],
                'nama' => ['required', 'string', 'max:120'],
                'jenis_kelamin' => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
                'alamat' => ['required', 'string', 'max:255'],
                'dusun' => ['nullable', 'string', 'max:100'],
                'rt' => ['nullable', 'string', 'max:5'],
                'rw' => ['nullable', 'string', 'max:5'],
                'pekerjaan' => ['nullable', 'string', 'max:100'],
            ],
            'keluarga' => [
                'rt_id' => ['nullable', 'exists:rts,id'],
                'no_kk' => ['required', 'string', 'max:20', 'unique:keluargas,no_kk'],
                'kepala_keluarga' => ['required', 'string', 'max:120'],
                'alamat' => ['required', 'string', 'max:255'],
                'dusun' => ['nullable', 'string', 'max:100'],
                'rt' => ['nullable', 'string', 'max:5'],
                'rw' => ['nullable', 'string', 'max:5'],
            ],
            'jenis-surat' => [
                'nama_surat' => ['required', 'string', 'max:150'],
                'deskripsi' => ['nullable', 'string', 'max:1000'],
                'standar_waktu_layanan' => ['required', 'integer', 'min:1', 'max:90'],
            ],
            'wilayah' => [
                'nama_dusun' => ['required', 'string', 'max:100', 'unique:dusuns,nama_dusun'],
                'no_rt' => ['nullable', 'string', 'max:5'],
                'nama_ketua_rt' => ['nullable', 'string', 'max:120'],
                'no_hp' => ['nullable', 'string', 'max:20'],
            ],
            'pengguna' => [
                'name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'no_hp' => ['nullable', 'string', 'max:20'],
                'role' => ['required', Rule::in(['admin', 'kepala_desa'])],
                'password' => ['required', 'string', 'min:8'],
            ],
            'surat' => [
                'jenis_surat_id' => ['required', 'exists:jenis_surats,id'],
                'keterangan' => ['nullable', 'string', 'max:1000'],
            ],
            'pengaduan' => ['isi_pengaduan' => ['required', 'string', 'max:3000']],
            'arsip' => [
                'no_dokumen' => ['required', 'string', 'max:100'],
                'tanggal' => ['required', 'date'],
                'perihal' => ['required', 'string', 'max:200'],
                'file' => ['nullable', 'url', 'max:500'],
            ],
            default => abort(404),
        };

        return $request->validate($rules);
    }

    private function rows(Request $request, string $module): array
    {
        $user = $request->user();
        $residentId = $user->penduduk?->id;

        return match ($module) {
            'penduduk' => Penduduk::with('keluarga')->latest()->get()->map(fn ($item) => [
                'id' => $item->id, 'primary' => $item->nama,
                'details' => [$item->nik, $item->jenis_kelamin ?: '—', $item->alamat],
                'status' => $item->status,
            ])->all(),
            'keluarga' => Keluarga::withCount('penduduks')->latest()->get()->map(fn ($item) => [
                'id' => $item->id, 'primary' => $item->kepala_keluarga,
                'details' => [$item->no_kk, $item->alamat, $item->penduduks_count . ' anggota'],
            ])->all(),
            'surat' => $this->scoped(Pengajuan::with(['jenisSurat', 'penduduk', 'surat']), $user, $residentId)
                ->latest()->get()->map(fn ($item) => [
                    'id' => $item->id,
                    'primary' => $item->jenisSurat?->nama_surat ?? 'Layanan dihapus',
                    'details' => array_filter([
                        $item->penduduk?->nama ?? 'Penduduk',
                        $item->created_at->format('d M Y'),
                        $item->keterangan ?: '—',
                        $item->surat?->no_surat ? 'Nomor surat: ' . $item->surat->no_surat : null,
                    ]),
                    'status' => $item->status,
                    'workflow' => 'surat',
                ])->all(),
            'pengaduan' => $this->scoped(Pengaduan::with('penduduk'), $user, $residentId)
                ->latest('tanggal')->get()->map(fn ($item) => [
                    'id' => $item->id, 'primary' => Str::limit($item->isi_pengaduan, 72),
                    'details' => [$item->penduduk?->nama ?? 'Penduduk', $item->tanggal?->format('d M Y') ?? '—', $item->tanggapan ? 'Tanggapan: ' . Str::limit($item->tanggapan, 60) : 'Belum ada tanggapan'],
                    'status' => $item->status, 'workflow' => 'pengaduan',
                ])->all(),
            'arsip' => Arsip::when($user->role === 'penduduk', fn ($query) => $query->where('user_id', $user->id))
                ->latest('tanggal')->get()->map(fn ($item) => [
                    'id' => $item->id, 'primary' => $item->perihal,
                    'details' => [$item->no_dokumen, $item->tanggal->format('d M Y'), $item->file ?: 'Berkas belum dilampirkan'],
                    'status' => $item->status,
                ])->all(),
            'wilayah' => Dusun::withCount('rts')->latest()->get()->map(fn ($item) => [
                'id' => $item->id, 'primary' => $item->nama_dusun,
                'details' => [$item->rts_count . ' RT', 'Wilayah Desa Nagerawe'],
            ])->all(),
            'jenis-surat' => JenisSurat::withCount('pengajuans')->latest()->get()->map(fn ($item) => [
                'id' => $item->id, 'primary' => $item->nama_surat,
                'details' => [$item->standar_waktu_layanan . ' hari kerja', $item->deskripsi ?: 'Tanpa deskripsi', $item->pengajuans_count . ' pengajuan'],
                'status' => $item->aktif ? 'aktif' : 'nonaktif',
            ])->all(),
            'pengguna' => User::latest()->get()->map(fn ($item) => [
                'id' => $item->id, 'primary' => $item->name,
                'details' => [$item->email, $item->no_hp ?: '—', $this->roleLabel($item->role)],
                'status' => $item->status,
            ])->all(),
            'notifikasi' => Notifikasi::where('user_id', $user->id)->latest('tanggal')->get()->map(fn ($item) => [
                'id' => $item->id, 'primary' => Str::limit($item->pesan, 100),
                'details' => [$item->tanggal->format('d M Y H:i')],
                'status' => $item->status_baca ? 'dibaca' : 'baru',
                'workflow' => 'notifikasi',
            ])->all(),
            default => [],
        };
    }

    private function scoped(Builder $query, User $user, ?int $residentId): Builder
    {
        if ($user->role === 'penduduk') {
            return $query->where('penduduk_id', $residentId ?? 0);
        }

        return $query;
    }

    private function notify(int $userId, string $message): void
    {
        Notifikasi::create(['user_id' => $userId, 'pesan' => $message]);
    }

    private function activity(Request $request, string $activity): void
    {
        LogAktivitas::create([
            'user_id' => $request->user()->id,
            'aktivitas' => $activity,
            'ip_address' => $request->ip(),
        ]);
    }

    private function roleLabel(string $role): string
    {
        return match ($role) {
            'admin' => 'Administrator',
            'kepala_desa' => 'Kepala Desa',
            default => 'Penduduk',
        };
    }
}
