<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModuleController;
use App\Models\JenisSurat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

$rolePrefix = static fn (string $role): string => $role === 'kepala_desa' ? 'kepala-desa' : $role;

Route::get('/', function () use ($rolePrefix) {
    if (Auth::check()) {
        return redirect()->route($rolePrefix(Auth::user()->role) . '.dashboard');
    }

    $activeLetterTypes = JenisSurat::where('aktif', true)->orderBy('nama_surat');

    return view('home', [
        'letterTypes' => (clone $activeLetterTypes)->limit(4)->get(),
        'activeLetterCount' => $activeLetterTypes->count(),
        'villageProfile' => [
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
        ],
    ]);
})->name('home');

Route::get('/dashboard', function () use ($rolePrefix) {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return redirect()->route($rolePrefix(Auth::user()->role) . '.dashboard');
})->name('dashboard.legacy');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'storeRegistration'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

$registerRoleRoutes = static function (string $prefix, string $role): void {
    $modules = $role === 'admin'
        ? ['penduduk', 'keluarga', 'surat', 'pengaduan', 'arsip', 'wilayah', 'jenis-surat', 'pengguna', 'notifikasi']
        : ['surat', 'pengaduan', 'arsip', 'notifikasi'];
    $creatableModules = $role === 'admin'
        ? ['penduduk', 'keluarga', 'arsip', 'wilayah', 'jenis-surat', 'pengguna']
        : ($role === 'penduduk' ? ['surat', 'pengaduan'] : []);
    $updatableModules = $role === 'penduduk'
        ? ['notifikasi']
        : ['surat', 'pengaduan', 'notifikasi'];

    Route::prefix($prefix)
        ->name($prefix . '.')
        ->middleware(['auth', 'role:' . $role])
        ->group(function () use ($role, $modules, $creatableModules, $updatableModules) {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
            if ($role !== 'penduduk') {
                Route::get('/laporan', [ModuleController::class, 'report'])->name('reports.index');
            }
            Route::get('/surat/{id}/cetak', [ModuleController::class, 'printLetter'])
                ->whereNumber('id')
                ->name('letters.print');
            Route::get('/{module}', [ModuleController::class, 'index'])
                ->whereIn('module', $modules)
                ->name('modules.index');
            if ($creatableModules !== []) {
                Route::post('/{module}', [ModuleController::class, 'store'])
                    ->whereIn('module', $creatableModules)
                    ->name('modules.store');
            }
            Route::patch('/{module}/{id}', [ModuleController::class, 'update'])
                ->whereIn('module', $updatableModules)
                ->whereNumber('id')
                ->name('modules.update');
            if ($role === 'admin') {
                Route::delete('/{module}/{id}', [ModuleController::class, 'destroy'])
                    ->whereIn('module', ['penduduk', 'keluarga', 'arsip', 'wilayah', 'jenis-surat', 'pengguna'])
                    ->whereNumber('id')
                    ->name('modules.destroy');
            }
        });
};

$registerRoleRoutes('admin', 'admin');
$registerRoleRoutes('kepala-desa', 'kepala_desa');
$registerRoleRoutes('penduduk', 'penduduk');
