<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\Keluarga;
use App\Models\JenisSurat;
use App\Models\Penduduk;
use App\Models\Pengaduan;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isResident = $user->role === 'penduduk';
        $residentId = $user->penduduk?->id;

        $requests = Pengajuan::with(['jenisSurat', 'penduduk'])
            ->when($isResident, fn ($query) => $query->where('penduduk_id', $residentId))
            ->latest()
            ->limit(5)
            ->get();

        $complaints = Pengaduan::with('penduduk')
            ->when($isResident, fn ($query) => $query->where('penduduk_id', $residentId))
            ->latest('tanggal')
            ->limit(5)
            ->get();

        if ($isResident) {
            $cards = [
                ['label' => 'Pengajuan surat', 'value' => Pengajuan::where('penduduk_id', $residentId)->count(), 'icon' => 'S', 'tone' => 'blue'],
                ['label' => 'Menunggu proses', 'value' => Pengajuan::where('penduduk_id', $residentId)->whereIn('status', ['menunggu', 'diproses'])->count(), 'icon' => '…', 'tone' => 'amber'],
                ['label' => 'Surat selesai', 'value' => Pengajuan::where('penduduk_id', $residentId)->where('status', 'selesai')->count(), 'icon' => '✓', 'tone' => 'green'],
                ['label' => 'Pengaduan', 'value' => Pengaduan::where('penduduk_id', $residentId)->count(), 'icon' => '!', 'tone' => 'purple'],
            ];
        } else {
            $cards = [
                ['label' => 'Total penduduk', 'value' => Penduduk::count(), 'icon' => 'P', 'tone' => 'blue'],
                ['label' => 'Kepala keluarga', 'value' => Keluarga::count(), 'icon' => 'K', 'tone' => 'green'],
                ['label' => 'Permohonan masuk', 'value' => Pengajuan::whereIn('status', ['menunggu', 'diproses'])->count(), 'icon' => 'S', 'tone' => 'amber'],
                ['label' => 'Pengaduan baru', 'value' => Pengaduan::where('status', 'baru')->count(), 'icon' => '!', 'tone' => 'purple'],
            ];
        }

        $rolePrefix = match ($user->role) {
            'admin' => 'admin',
            'kepala_desa' => 'kepala-desa',
            default => 'penduduk',
        };
        $pendingWorkflowCount = Pengajuan::where('status', $user->role === 'kepala_desa' ? 'diproses' : 'menunggu')->count();

        return view($rolePrefix . '.dashboard', [
            'rolePrefix' => $rolePrefix,
            'pendingWorkflowCount' => $pendingWorkflowCount,
            'cards' => $cards,
            'requests' => $requests,
            'complaints' => $complaints,
            'resident' => $user->penduduk,
            'activeTypes' => JenisSurat::where('aktif', true)->count(),
            'archiveCount' => $isResident
                ? Arsip::where('user_id', $user->id)->count()
                : Arsip::count(),
            'userCount' => User::count(),
        ]);
    }
}
