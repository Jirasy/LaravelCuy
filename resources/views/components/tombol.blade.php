{{--
    Tombol aksi dengan warna sesuai fungsinya (satu tempat untuk seluruh skema warna).
    Pakai: <x-tombol jenis="edit" href="{{ route(...) }}">Edit</x-tombol>
           <x-tombol jenis="hapus" type="submit">Hapus</x-tombol>
    jenis : edit (indigo) | hapus (merah) | detail (sky) | sukses (hijau zamrud: simpan, tambah, setujui, aktifkan)
            | peringatan (oranye: nonaktifkan, tutup) | unduh (teal) | netral (abu gelap)
    ukuran: sm (default, untuk tabel) | md (untuk form)
    Kalau href diisi, hasilnya tautan <a>; kalau tidak, <button>.
--}}
@props(['jenis' => 'netral', 'ukuran' => 'sm', 'href' => null, 'type' => 'button'])

@php
    $warna = [
        'edit'       => 'bg-indigo-600 hover:bg-indigo-700',
        'hapus'      => 'bg-red-600 hover:bg-red-700',
        'detail'     => 'bg-sky-600 hover:bg-sky-700',
        'sukses'     => 'bg-emerald-600 hover:bg-emerald-700',
        'peringatan' => 'bg-orange-500 hover:bg-orange-600',
        'unduh'      => 'bg-teal-600 hover:bg-teal-700',
        'netral'     => 'bg-gray-800 hover:bg-gray-900',
    ][$jenis] ?? 'bg-gray-800 hover:bg-gray-900';

    $besar = $ukuran === 'md' ? 'px-5 py-2 rounded-lg' : 'px-3 py-1 rounded text-xs';
    $kelas = "{$warna} {$besar} text-white font-semibold transition-colors";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $kelas]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $kelas]) }}>{{ $slot }}</button>
@endif
