{{-- Khusus pengelola. Variabel: $ekskul, $anggotaTugas --}}
<div class="mt-4 pt-4 border-t border-gray-200">
    <details>
        <summary class="cursor-pointer text-sm text-indigo-600 font-semibold">📝 Beri task ke anggota</summary>
        <div class="mt-3">
            @include('tugas._form', ['mode' => 'ekskul', 'ekskul' => $ekskul, 'anggotaOpsi' => $anggotaTugas])
        </div>
    </details>
</div>