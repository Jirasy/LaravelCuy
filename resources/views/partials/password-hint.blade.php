{{-- Daftar syarat password yang berubah hijau saat terpenuhi. Pemakaian: @include('partials.password-hint') --}}
<ul class="pw-hint text-xs space-y-0.5 mt-2" data-target="{{ $target ?? 'input[name=password]' }}">
    <li data-cek="len">Minimal 10 karakter</li>
    <li data-cek="up">Huruf besar (A-Z)</li>
    <li data-cek="low">Huruf kecil (a-z)</li>
    <li data-cek="num">Angka (0-9)</li>
    <li data-cek="sym">Simbol (! @ # $ %)</li>
</ul>
<script>
    (function () {
        var daftar = document.currentScript.previousElementSibling;
        var input = document.querySelector(daftar.dataset.target);
        if (!input) return;
        var aturan = {
            len: function (v) { return v.length >= 10; },
            up:  function (v) { return /[A-Z]/.test(v); },
            low: function (v) { return /[a-z]/.test(v); },
            num: function (v) { return /\d/.test(v); },
            sym: function (v) { return /[^A-Za-z0-9]/.test(v); }
        };
        function periksa() {
            daftar.querySelectorAll('li').forEach(function (li) {
                var ok = aturan[li.dataset.cek](input.value);
                li.classList.toggle('text-emerald-600', ok);
                li.classList.toggle('font-semibold', ok);
                li.classList.toggle('text-gray-500', !ok);
                li.textContent = (ok ? '✓ ' : '• ') + li.textContent.replace(/^[✓•] /, '');
            });
        }
        input.addEventListener('input', periksa);
        periksa();
    })();
</script>