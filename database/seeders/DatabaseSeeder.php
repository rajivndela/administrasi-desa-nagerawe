<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\JenisSurat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            ['name' => 'Administrator Desa', 'role' => 'admin', 'password' => Hash::make('abcd12345')]
        );
        User::updateOrCreate(
            ['email' => 'kepaladesa@gmail.com'],
            ['name' => 'Kepala Desa', 'role' => 'kepala_desa', 'password' => Hash::make('abcd12345')]
        );

        foreach ([
            ['Surat Keterangan Domisili', 'Keterangan tempat tinggal penduduk', 2],
            ['Surat Keterangan Usaha', 'Keterangan kepemilikan atau kegiatan usaha', 3],
            ['Surat Keterangan Tidak Mampu', 'Keterangan kondisi ekonomi penduduk', 3],
            ['Surat Pengantar', 'Surat pengantar ke instansi tujuan', 1],
            ['Surat Keterangan Kelahiran', 'Keterangan peristiwa kelahiran', 2],
        ] as [$name, $description, $days]) {
            JenisSurat::firstOrCreate(
                ['nama_surat' => $name],
                ['deskripsi' => $description, 'standar_waktu_layanan' => $days, 'aktif' => true]
            );
        }
    }
}
