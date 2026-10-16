{{-- Pemakaian: @include('partials.avatar', ['user' => $u, 'ukuran' => 36]) --}}
@php $ukuran = $ukuran ?? 36; @endphp
@if($user->foto_url)
    <img src="{{ $user->foto_url }}" alt="{{ $user->name }}" class="rounded-full object-cover shrink-0" style="width:{{ $ukuran }}px;height:{{ $ukuran }}px">
@else
    <span class="rounded-full shrink-0 inline-flex items-center justify-center font-bold text-white"
          style="width:{{ $ukuran }}px;height:{{ $ukuran }}px;font-size:{{ round($ukuran * .38) }}px;background:linear-gradient(135deg, rgb(var(--p1-rgb)), rgb(var(--p2-rgb)))">{{ $user->inisial }}</span>
@endif