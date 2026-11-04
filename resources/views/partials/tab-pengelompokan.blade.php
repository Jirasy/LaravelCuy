{{-- Tab untuk pindah antara halaman Tipe Ekskul dan Kategori Ekskul (keduanya satu menu di navbar) --}}
<div class="flex gap-1 border-b border-gray-200 mb-6 overflow-x-auto" role="tablist" aria-label="Pengelompokan ekskul">
    @foreach(['jenis-ekskul.index' => ['jenis-ekskul.*', 'Tipe Ekskul'], 'kategori-ekskul.index' => ['kategori-ekskul.*', 'Kategori Ekskul']] as $rute => [$pola, $label])
        @php $aktif = request()->routeIs($pola); @endphp
        <a href="{{ route($rute) }}" role="tab" aria-selected="{{ $aktif ? 'true' : 'false' }}"
           class="px-4 py-2 text-sm font-semibold border-b-2 whitespace-nowrap {{ $aktif ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700' }}">{{ $label }}</a>
    @endforeach
</div>
