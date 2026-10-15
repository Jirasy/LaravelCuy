{{-- Pemakaian: @include('partials.status-badge', ['status' => $p->status, 'label' => $p->status_label]) --}}
@php
    $kelasBadge = ['pending' => 'badge-pending', 'diterima' => 'badge-diterima', 'ditolak' => 'badge-ditolak'][$status] ?? 'badge-pending';
@endphp
<span class="badge-pill {{ $kelasBadge }}"><span class="dot"></span>{{ $label }}</span>