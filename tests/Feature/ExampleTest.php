<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_homepage_shows_public_services_and_guest_dashboard_redirects_to_login(): void
    {
        JenisSurat::create([
            'nama_surat' => 'Surat Keterangan Domisili',
            'standar_waktu_layanan' => 2,
            'aktif' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Urus kebutuhan desa')
            ->assertSee('Surat Keterangan Domisili')
            ->assertSee('1 layanan aktif')
            ->assertSee(route('login'))
            ->assertSee(route('register'));
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
