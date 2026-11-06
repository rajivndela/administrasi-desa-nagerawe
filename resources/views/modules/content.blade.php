    <div class="page-heading">
        <div class="page-heading-copy"><span class="eyebrow">ADMINISTRASI DESA</span><h1>{{ $title }}</h1><p class="muted">{{ $description }}</p></div>
        @if ($canCreate)
            <a class="button button-primary" href="#form-data">+ Tambah data</a>
        @endif
    </div>

    @if ($module === 'surat')
        <section class="workflow-banner">
            <div class="workflow-intro"><span class="workflow-icon">↗</span><span><strong>Alur pelayanan surat</strong><small>Pengajuan diproses secara bertahap dan transparan</small></span></div>
            <div class="workflow-steps">
                <span class="workflow-step done"><i>1</i> Penduduk</span><b></b>
                <span class="{{ auth()->user()->role === 'admin' ? 'workflow-step current' : (auth()->user()->role === 'kepala_desa' ? 'workflow-step done' : 'workflow-step') }}"><i>2</i> Pemeriksaan Admin</span><b></b>
                <span class="{{ auth()->user()->role === 'kepala_desa' ? 'workflow-step current' : 'workflow-step' }}"><i>3</i> Persetujuan</span><b></b>
                <span class="workflow-step"><i>4</i> Surat terbit</span>
            </div>
        </section>
    @endif

    @if ($canCreate)
        <details class="panel form-panel" id="form-data" {{ $errors->any() ? 'open' : '' }}>
            <summary><span><strong>Tambah {{ strtolower($title) }}</strong><small>Masukkan informasi pada formulir berikut.</small></span><span class="summary-mark">+</span></summary>
            <form method="POST" action="{{ route($rolePrefix . '.modules.store', $module) }}" class="module-form">
                @csrf
                <div class="form-grid">
                    @foreach ($fields as $field)
                        <label class="{{ $field['type'] === 'textarea' ? 'field-full' : '' }}">
                            {{ $field['label'] }} @if($field['required'])<span class="required-mark">*</span>@endif
                            @if ($field['type'] === 'textarea')
                                <textarea name="{{ $field['name'] }}" rows="3" {{ $field['required'] ? 'required' : '' }}>{{ old($field['name']) }}</textarea>
                            @elseif ($field['type'] === 'select')
                                <select name="{{ $field['name'] }}" {{ $field['required'] ? 'required' : '' }}>
                                    <option value="">Pilih {{ strtolower($field['label']) }}</option>
                                    @foreach ($field['options'] ?? [] as $value => $option)
                                        @php($optionValue = is_int($value) ? $option : $value)
                                        <option value="{{ $optionValue }}" @selected(old($field['name']) == $optionValue)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input
                                    type="{{ $field['type'] }}"
                                    name="{{ $field['name'] }}"
                                    value="{{ old($field['name']) }}"
                                    {{ $field['required'] ? 'required' : '' }}
                                    @if($field['max']) maxlength="{{ $field['max'] }}" @endif
                                    @if($field['name'] === 'nik') inputmode="numeric" pattern="[0-9]{16}" @endif
                                >
                            @endif
                            @error($field['name'])<span class="field-error">{{ $message }}</span>@enderror
                        </label>
                    @endforeach
                </div>
                <div class="form-actions"><button class="button button-primary" type="submit">Simpan data</button><a class="button button-quiet" href="{{ route($rolePrefix . '.modules.index', $module) }}">Batal</a></div>
            </form>
        </details>
    @endif

    <section class="panel table-panel">
        <div class="table-toolbar">
            <div class="table-title"><span class="table-title-icon">{{ strtoupper(substr($module, 0, 1)) }}</span><span><h2>Daftar {{ strtolower($title) }}</h2><p>{{ count($rows) }} data tercatat</p></span></div>
            <div class="table-search"><span>⌕</span><input type="search" placeholder="Cari data..." aria-label="Cari data" oninput="const q=this.value.toLowerCase();document.querySelectorAll('[data-search-row]').forEach(r=>r.hidden=!r.dataset.searchRow.includes(q))"></div>
        </div>
        @if (count($rows) > 0)
            <div class="responsive-table">
                <table>
                    <thead><tr><th>INFORMASI</th><th>RINCIAN</th><th>STATUS</th><th>AKSI</th></tr></thead>
                    <tbody>
                    @foreach ($rows as $row)
                        <tr data-search-row="{{ strtolower($row['primary'] . ' ' . implode(' ', $row['details']) . ' ' . ($row['status'] ?? '')) }}">
                            <td><div class="table-primary">{{ $row['primary'] }}</div><small class="table-id">ID #{{ str_pad((string) $row['id'], 4, '0', STR_PAD_LEFT) }}</small></td>
                            <td><div class="table-details">@foreach ($row['details'] as $detail)<span>{{ $detail }}</span>@endforeach</div></td>
                            <td>@if(isset($row['status']))<span class="badge badge-{{ $row['status'] }}">{{ ucfirst($row['status']) }}</span>@else<span class="muted">—</span>@endif</td>
                            <td class="action-cell">
                                @if (($row['workflow'] ?? '') === 'surat' && $canManage)
                                    <details class="row-action">
                                        <summary class="button button-small button-outline">Tinjau</summary>
                                        <form method="POST" action="{{ route($rolePrefix . '.modules.update', ['module' => 'surat', 'id' => $row['id']]) }}" class="row-action-form">
                                            @csrf @method('PATCH')
                                            <label>Perbarui status
                                                <select name="status" required>
                                                    @foreach ($workflowOptions as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </label>
                                            <label>Catatan (opsional)<textarea name="catatan" rows="2"></textarea></label>
                                            <button class="button button-primary button-small" type="submit">Simpan status</button>
                                        </form>
                                    </details>
                                @elseif (($row['workflow'] ?? '') === 'pengaduan' && $canManage)
                                    <details class="row-action">
                                        <summary class="button button-small button-outline">Tanggapi</summary>
                                        <form method="POST" action="{{ route($rolePrefix . '.modules.update', ['module' => 'pengaduan', 'id' => $row['id']]) }}" class="row-action-form">
                                            @csrf @method('PATCH')
                                            <label>Status
                                                <select name="status" required><option value="ditindaklanjuti">Ditindaklanjuti</option><option value="selesai">Selesai</option></select>
                                            </label>
                                            <label>Tanggapan<textarea name="tanggapan" rows="3" required></textarea></label>
                                            <button class="button button-primary button-small" type="submit">Kirim tanggapan</button>
                                        </form>
                                    </details>
                                @elseif (($row['workflow'] ?? '') === 'notifikasi' && $row['status'] !== 'dibaca')
                                    <form method="POST" action="{{ route($rolePrefix . '.modules.update', ['module' => 'notifikasi', 'id' => $row['id']]) }}">
                                        @csrf @method('PATCH')<button class="button button-small button-outline" type="submit">Tandai dibaca</button>
                                    </form>
                                @endif
                                @if ($module === 'surat' && ($row['status'] ?? '') === 'selesai')
                                    <a class="button button-small button-outline" href="{{ route($rolePrefix . '.letters.print', $row['id']) }}" target="_blank" rel="noopener">Cetak</a>
                                @endif

                                @if (auth()->user()->role === 'admin' && in_array($module, ['penduduk', 'keluarga', 'arsip', 'wilayah', 'jenis-surat', 'pengguna'], true))
                                    <form method="POST" action="{{ route($rolePrefix . '.modules.destroy', ['module' => $module, 'id' => $row['id']]) }}" onsubmit="return confirm('Hapus data ini? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf @method('DELETE')<button class="icon-button delete-button" type="submit" aria-label="Hapus data">×</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state table-empty"><span class="empty-icon">{{ strtoupper(substr($module, 0, 1)) }}</span><strong>Belum ada data</strong><p>Data akan muncul di sini setelah dicatat.</p></div>
        @endif
    </section>
