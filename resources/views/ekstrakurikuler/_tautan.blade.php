{{-- Variabel: $tautans, $ekskul, $staff --}}
@if($tautans->isNotEmpty() || $staff)
    <div class="mt-4 pt-4 border-t border-gray-200">
        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Tautan</p>
        <div class="flex flex-wrap gap-2">
            @forelse($tautans as $t)
                <span class="inline-flex items-center gap-1">
                    <a href="{{ $t->url }}" target="_blank" rel="noopener noreferrer"
                       class="px-3 py-1 text-sm rounded-full bg-indigo-100 text-indigo-800 hover:bg-indigo-200">{{ $t->ikon }} {{ $t->label }}</a>
                    @if($staff)
                        <form action="{{ route('tautan.destroy', $t->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus tautan ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:underline" title="Hapus">✕</button>
                        </form>
                    @endif
                </span>
            @empty
                <span class="text-sm text-gray-400">Belum ada tautan.</span>
            @endforelse
        </div>

        @if($staff)
            <details class="mt-3">
                <summary class="cursor-pointer text-sm text-indigo-600 font-semibold">+ Tambah tautan</summary>
                <form action="{{ route('tautan.store') }}" method="POST" class="mt-3 space-y-2">
                    @csrf
                    @include('tautan._form', ['ekskul' => $ekskul])
                </form>
            </details>
        @endif
    </div>
@endif