<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\Notifikasi;
use App\Models\Penduduk;
use App\Models\Pengaduan;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiController extends Controller
{
    public function villageProfile()
    {
        return response()->json([
            'name' => 'Nagerawe',
            'administrative_code' => '53.16.03.2008',
            'district' => 'Boawae',
            'regency' => 'Nagekeo',
            'province' => 'Nusa Tenggara Timur',
            'area_km2' => 39.03,
            'population' => 1171,
            'male' => 605,
            'female' => 566,
            'density' => 30,
            'distance_to_district_km' => 18.0,
            'latitude' => -8.6419,
            'longitude' => 121.1333,
            'data_year' => 2023,
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['Email atau kata sandi tidak sesuai.']]);
        }

        $user->tokens()->delete();
        return response()->json([
            'token' => $user->createToken('mobile-app')->plainTextToken,
            'user' => $this->userPayload($user),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'nik' => ['required', 'digits:16', 'unique:penduduks,nik'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'alamat' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'no_hp' => $data['no_hp'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => 'penduduk',
            ]);
            Penduduk::create([
                'user_id' => $user->id,
                'nik' => $data['nik'],
                'nama' => $data['name'],
                'alamat' => $data['alamat'],
            ]);
            return $user;
        });

        return response()->json([
            'token' => $user->createToken('mobile-app')->plainTextToken,
            'user' => $this->userPayload($user),
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Berhasil keluar.']);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $resident = $user->penduduk;
        $letters = Pengajuan::with(['jenisSurat', 'penduduk'])->when(
            $user->role === 'penduduk',
            fn ($query) => $query->where('penduduk_id', $resident?->id),
        )->latest()->limit(5)->get();
        $complaints = Pengaduan::with('penduduk')->when(
            $user->role === 'penduduk',
            fn ($query) => $query->where('penduduk_id', $resident?->id),
        )->latest('tanggal')->limit(5)->get();

        $cards = $user->role === 'penduduk' ? [
            ['label' => 'Pengajuan surat', 'value' => Pengajuan::where('penduduk_id', $resident?->id)->count()],
            ['label' => 'Menunggu proses', 'value' => Pengajuan::where('penduduk_id', $resident?->id)->whereIn('status', ['menunggu', 'diproses'])->count()],
            ['label' => 'Surat selesai', 'value' => Pengajuan::where('penduduk_id', $resident?->id)->where('status', 'selesai')->count()],
            ['label' => 'Pengaduan', 'value' => Pengaduan::where('penduduk_id', $resident?->id)->count()],
        ] : [
            ['label' => 'Total penduduk', 'value' => Penduduk::count()],
            ['label' => 'Kepala keluarga', 'value' => \App\Models\Keluarga::count()],
            ['label' => 'Permohonan masuk', 'value' => Pengajuan::whereIn('status', ['menunggu', 'diproses'])->count()],
            ['label' => 'Pengaduan baru', 'value' => Pengaduan::where('status', 'baru')->count()],
        ];

        return response()->json(['cards' => $cards, 'letters' => $letters, 'complaints' => $complaints]);
    }

    public function letterTypes()
    {
        return response()->json(['data' => JenisSurat::where('aktif', true)->orderBy('nama_surat')->get()]);
    }

    public function letters(Request $request)
    {
        $user = $request->user();
        $resident = $user->penduduk;
        $letters = Pengajuan::with(['jenisSurat', 'penduduk'])
            ->when($user->role === 'penduduk', fn ($query) => $query->where('penduduk_id', $resident?->id))
            ->latest()->get();
        return response()->json(['data' => $letters]);
    }

    public function storeLetter(Request $request)
    {
        abort_unless($request->user()->role === 'penduduk', 403);
        $resident = $request->user()->penduduk;
        abort_unless($resident, 422, 'Profil penduduk belum terhubung dengan akun ini.');
        $data = $request->validate([
            'jenis_surat_id' => ['required', 'exists:jenis_surats,id'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);
        $letter = Pengajuan::create($data + ['penduduk_id' => $resident->id]);
        Notifikasi::create([
            'user_id' => $request->user()->id,
            'pesan' => 'Pengajuan surat Anda telah diterima dan menunggu verifikasi.',
        ]);
        return response()->json(['data' => $letter->load(['jenisSurat', 'penduduk'])], 201);
    }

    public function complaints(Request $request)
    {
        $user = $request->user();
        $resident = $user->penduduk;
        $complaints = Pengaduan::with('penduduk')
            ->when($user->role === 'penduduk', fn ($query) => $query->where('penduduk_id', $resident?->id))
            ->latest('tanggal')->get();
        return response()->json(['data' => $complaints]);
    }

    public function storeComplaint(Request $request)
    {
        abort_unless($request->user()->role === 'penduduk', 403);
        $resident = $request->user()->penduduk;
        abort_unless($resident, 422, 'Profil penduduk belum terhubung dengan akun ini.');
        $data = $request->validate(['isi_pengaduan' => ['required', 'string', 'max:3000']]);
        $complaint = Pengaduan::create($data + ['penduduk_id' => $resident->id, 'status' => 'baru']);
        Notifikasi::create([
            'user_id' => $request->user()->id,
            'pesan' => 'Pengaduan Anda telah dikirim dan akan ditindaklanjuti.',
        ]);
        return response()->json(['data' => $complaint->load('penduduk')], 201);
    }

    public function notifications(Request $request)
    {
        $notifications = Notifikasi::where('user_id', $request->user()->id)->latest('tanggal')->get();
        return response()->json(['data' => $notifications]);
    }

    public function markNotificationRead(Request $request, int $id)
    {
        $notification = Notifikasi::where('user_id', $request->user()->id)->findOrFail($id);
        $notification->update(['status_baca' => true]);
        return response()->json(['data' => $notification]);
    }

    private function userPayload(User $user): array
    {
        $user->loadMissing('penduduk');
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'penduduk' => $user->penduduk,
        ];
    }
}
