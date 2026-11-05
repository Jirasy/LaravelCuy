{{-- Logo web (identitas sekolah dari config/sekolah.php). Pemakaian: @include('partials.logo-sekolah', ['ukuran' => 36]) --}}
@php $ukuran = $ukuran ?? 36; @endphp
<img src="{{ asset(config('sekolah.logo')) }}" alt="Logo {{ config('sekolah.nama') }}" width="{{ $ukuran }}" height="{{ $ukuran }}"
     class="shrink-0 object-contain {{ $class ?? '' }}" style="width:{{ $ukuran }}px;height:{{ $ukuran }}px">
