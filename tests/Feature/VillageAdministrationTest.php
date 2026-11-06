<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\Notifikasi;
use App\Models\Penduduk;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VillageAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_resident_registration_creates_a_linked_profile(): void
    {
        $response = $this->post('/register', [
            'name' => 'Siti Nagerawe',
            'nik' => '5301010101010001',
            'email' => 'siti@example.test',
            'no_hp' => '08123456789',
            'alamat' => 'Dusun Nagerawe',
            'password' => 'Rahasia123!',
            'password_confirmation' => 'Rahasia123!',
        ]);

        $response->assertRedirect('/penduduk/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'siti@example.test', 'role' => 'penduduk']);
        $this->assertDatabaseHas('penduduks', ['nik' => '5301010101010001', 'nama' => 'Siti Nagerawe']);
    }

    public function test_resident_can_request_a_letter_and_staff_can_issue_it(): void
    {
        $resident = User::create([
            'name' => 'Budi Warga',
            'email' => 'budi@example.test',
            'password' => 'Rahasia123!',
            'role' => 'penduduk',
        ]);
        $profile = Penduduk::create([
            'user_id' => $resident->id,
            'nik' => '5301010101010002',
            'nama' => 'Budi Warga',
            'alamat' => 'Dusun Utara',
        ]);
        $type = JenisSurat::create([
            'nama_surat' => 'Surat Keterangan Domisili',
            'standar_waktu_layanan' => 2,
            'aktif' => true,
        ]);

        $this->actingAs($resident)->post('/penduduk/surat', [
            'jenis_surat_id' => $type->id,
            'keterangan' => 'Keperluan administrasi',
        ])->assertRedirect('/penduduk/surat');

        $application = Pengajuan::firstOrFail();
        $this->assertSame($profile->id, $application->penduduk_id);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $resident->id]);

        $staff = User::create([
            'name' => 'Admin Desa',
            'email' => 'admin@example.test',
            'password' => 'Rahasia123!',
            'role' => 'admin',
        ]);

        $this->actingAs($staff)->withHeader('Referer', 'http://localhost/admin/surat')->patch('/admin/surat/' . $application->id, [
            'status' => 'diproses',
            'catatan' => 'Dokumen diterima untuk ditinjau',
        ])->assertRedirect('/admin/surat');

        $headman = User::create([
            'name' => 'Kepala Desa',
            'email' => 'kepala@example.test',
            'password' => 'Rahasia123!',
            'role' => 'kepala_desa',
        ]);
        $this->actingAs($headman)->withHeader('Referer', 'http://localhost/kepala-desa/surat')->patch('/kepala-desa/surat/' . $application->id, [
            'status' => 'disetujui',
            'catatan' => 'Dokumen lengkap dan disetujui',
        ])->assertRedirect('/kepala-desa/surat');

        $this->actingAs($staff);
        $this->withHeader('Referer', 'http://localhost/admin/surat')->patch('/admin/surat/' . $application->id, [
            'status' => 'selesai',
            'catatan' => 'Surat diterbitkan',
        ])->assertRedirect('/admin/surat');

        $this->assertDatabaseHas('pengajuans', ['id' => $application->id, 'status' => 'selesai']);
        $this->assertDatabaseHas('surats', ['pengajuan_id' => $application->id]);
        $this->assertDatabaseHas('arsips', ['user_id' => $resident->id]);
        $this->assertGreaterThanOrEqual(2, Notifikasi::where('user_id', $resident->id)->count());

        $this->actingAs($resident)->get('/penduduk/arsip')->assertOk()->assertSee('Surat Keterangan Domisili');
        $this->get(route('penduduk.letters.print', $application->id))
            ->assertOk()
            ->assertSee($application->surat->no_surat);
    }

    public function test_staff_cannot_issue_a_letter_before_approval(): void
    {
        $staff = User::create([
            'name' => 'Admin Desa',
            'email' => 'admin@example.test',
            'password' => 'Rahasia123!',
            'role' => 'admin',
        ]);
        $resident = User::create([
            'name' => 'Budi Warga',
            'email' => 'budi@example.test',
            'password' => 'Rahasia123!',
            'role' => 'penduduk',
        ]);
        $profile = Penduduk::create([
            'user_id' => $resident->id,
            'nik' => '5301010101010003',
            'nama' => 'Budi Warga',
            'alamat' => 'Dusun Utara',
        ]);
        $type = JenisSurat::create(['nama_surat' => 'Surat Pengantar', 'aktif' => true]);
        $application = Pengajuan::create(['penduduk_id' => $profile->id, 'jenis_surat_id' => $type->id]);

        $this->actingAs($staff)->patch('/admin/surat/' . $application->id, [
            'status' => 'selesai',
            'catatan' => 'Terbitkan',
        ])->assertStatus(422);

        $this->assertDatabaseCount('surats', 0);
        $this->assertSame('menunggu', $application->fresh()->status);
    }

    public function test_admin_can_open_each_administration_menu_and_report(): void
    {
        $admin = User::create([
            'name' => 'Admin Desa',
            'email' => 'admin@example.test',
            'password' => 'Rahasia123!',
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        foreach ([
            '/admin/dashboard' => 'admin.dashboard',
            '/admin/penduduk' => 'admin.penduduk',
            '/admin/keluarga' => 'admin.keluarga',
            '/admin/surat' => 'admin.surat',
            '/admin/pengaduan' => 'admin.pengaduan',
            '/admin/arsip' => 'admin.arsip',
            '/admin/wilayah' => 'admin.wilayah',
            '/admin/jenis-surat' => 'admin.jenis-surat',
            '/admin/pengguna' => 'admin.pengguna',
            '/admin/notifikasi' => 'admin.notifikasi',
            '/admin/laporan' => 'admin.laporan',
        ] as $path => $view) {
            $response = $this->get($path)->assertOk()->assertViewIs($view);
            if ($path === '/admin/surat') {
                $response->assertSee('Proses Pengajuan Surat');
            }
        }
    }

    public function test_resident_cannot_open_administrator_data(): void
    {
        $resident = User::create([
            'name' => 'Penduduk',
            'email' => 'warga@example.test',
            'password' => 'Rahasia123!',
            'role' => 'penduduk',
        ]);

        $this->actingAs($resident)->get('/admin/penduduk')->assertForbidden();
    }

    public function test_headman_and_resident_have_separate_menu_views_and_routes(): void
    {
        $headman = User::create([
            'name' => 'Kepala Desa',
            'email' => 'kepala@example.test',
            'password' => 'Rahasia123!',
            'role' => 'kepala_desa',
        ]);
        $resident = User::create([
            'name' => 'Penduduk',
            'email' => 'warga@example.test',
            'password' => 'Rahasia123!',
            'role' => 'penduduk',
        ]);

        foreach ([
            '/kepala-desa/dashboard' => 'kepala-desa.dashboard',
            '/kepala-desa/surat' => 'kepala-desa.surat',
            '/kepala-desa/pengaduan' => 'kepala-desa.pengaduan',
            '/kepala-desa/arsip' => 'kepala-desa.arsip',
            '/kepala-desa/notifikasi' => 'kepala-desa.notifikasi',
            '/kepala-desa/laporan' => 'kepala-desa.laporan',
        ] as $path => $view) {
            $response = $this->actingAs($headman)->get($path)->assertOk()->assertViewIs($view);
            if ($path === '/kepala-desa/surat') {
                $response->assertSee('Persetujuan Surat');
            }
        }
        $this->actingAs($headman)->get('/dashboard')->assertRedirect('/kepala-desa/dashboard');

        foreach ([
            '/penduduk/dashboard' => 'penduduk.dashboard',
            '/penduduk/surat' => 'penduduk.surat',
            '/penduduk/pengaduan' => 'penduduk.pengaduan',
            '/penduduk/arsip' => 'penduduk.arsip',
            '/penduduk/notifikasi' => 'penduduk.notifikasi',
        ] as $path => $view) {
            $response = $this->actingAs($resident)->get($path)->assertOk()->assertViewIs($view);
            if ($path === '/penduduk/surat') {
                $response->assertSee('Ajukan Surat');
            } elseif ($path === '/penduduk/pengaduan') {
                $response->assertSee('Pengaduan Saya');
            }
        }
        $this->actingAs($resident)->get('/dashboard')->assertRedirect('/penduduk/dashboard');

        $this->actingAs($headman)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($headman)->get('/kepala-desa/penduduk')->assertNotFound();
        $this->actingAs($resident)->get('/kepala-desa/dashboard')->assertForbidden();
        $this->actingAs($resident)->get('/penduduk/laporan')->assertNotFound();
    }

    public function test_login_redirects_each_role_to_its_own_dashboard(): void
    {
        foreach ([
            ['admin', 'admin@example.test', '/admin/dashboard'],
            ['kepala_desa', 'kepala@example.test', '/kepala-desa/dashboard'],
            ['penduduk', 'warga@example.test', '/penduduk/dashboard'],
        ] as [$role, $email, $dashboard]) {
            User::create([
                'name' => ucfirst($role),
                'email' => $email,
                'password' => 'Rahasia123!',
                'role' => $role,
            ]);

            $this->post('/login', [
                'email' => $email,
                'password' => 'Rahasia123!',
            ])->assertRedirect($dashboard);

            $this->post('/logout');
        }
    }
}
