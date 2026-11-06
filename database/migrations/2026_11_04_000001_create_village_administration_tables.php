<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('penduduk')->after('password');
            $table->string('no_hp', 20)->nullable()->after('role');
            $table->string('status')->default('aktif')->after('no_hp');
        });

        Schema::create('dusuns', function (Blueprint $table) {
            $table->id();
            $table->string('nama_dusun')->unique();
            $table->timestamps();
        });

        Schema::create('rts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dusun_id')->constrained()->cascadeOnDelete();
            $table->string('no_rt', 5);
            $table->string('nama_ketua_rt')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->timestamps();
            $table->unique(['dusun_id', 'no_rt']);
        });

        Schema::create('keluargas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->nullable()->constrained('rts')->nullOnDelete();
            $table->string('no_kk', 20)->unique();
            $table->string('kepala_keluarga');
            $table->string('alamat');
            $table->string('dusun')->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->timestamps();
        });

        Schema::create('penduduks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('keluarga_id')->nullable()->constrained('keluargas')->nullOnDelete();
            $table->string('nik', 20)->unique();
            $table->string('nama');
            $table->string('jenis_kelamin', 20)->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama')->nullable();
            $table->string('alamat');
            $table->string('dusun')->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('status_perkawinan')->nullable();
            $table->string('status')->default('aktif');
            $table->timestamps();
        });

        Schema::create('jenis_surats', function (Blueprint $table) {
            $table->id();
            $table->string('nama_surat');
            $table->text('deskripsi')->nullable();
            $table->unsignedSmallInteger('standar_waktu_layanan')->default(3);
            $table->string('template_file')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('pengajuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penduduk_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jenis_surat_id')->constrained('jenis_surats')->restrictOnDelete();
            $table->string('status')->default('menunggu');
            $table->text('keterangan')->nullable();
            $table->string('file_pendukung')->nullable();
            $table->timestamp('waktu_mulai_proses')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->timestamps();
        });

        Schema::create('surats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->unique()->constrained('pengajuans')->cascadeOnDelete();
            $table->string('no_surat')->unique();
            $table->date('tgl_terbit');
            $table->string('file_surat')->nullable();
            $table->string('qr_code')->nullable();
            $table->foreignId('ditandatangani_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('verifikasi_surats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status_verifikasi');
            $table->text('catatan')->nullable();
            $table->timestamp('tgl_verifikasi')->useCurrent();
            $table->timestamps();
        });

        Schema::create('pengaduans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penduduk_id')->constrained()->cascadeOnDelete();
            $table->text('isi_pengaduan');
            $table->string('status')->default('baru');
            $table->text('tanggapan')->nullable();
            $table->foreignId('ditanggapi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal')->useCurrent();
            $table->timestamps();
        });

        Schema::create('notifikasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('pesan');
            $table->boolean('status_baca')->default(false);
            $table->timestamp('tanggal')->useCurrent();
            $table->timestamps();
        });

        Schema::create('arsips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->nullable()->constrained('surats')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('no_dokumen');
            $table->date('tanggal');
            $table->string('perihal');
            $table->string('file')->nullable();
            $table->string('status')->default('tersimpan');
            $table->timestamps();
        });

        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('aktivitas');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('waktu')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
        Schema::dropIfExists('arsips');
        Schema::dropIfExists('notifikasis');
        Schema::dropIfExists('pengaduans');
        Schema::dropIfExists('verifikasi_surats');
        Schema::dropIfExists('surats');
        Schema::dropIfExists('pengajuans');
        Schema::dropIfExists('jenis_surats');
        Schema::dropIfExists('penduduks');
        Schema::dropIfExists('keluargas');
        Schema::dropIfExists('rts');
        Schema::dropIfExists('dusuns');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'no_hp', 'status']);
        });
    }
};
