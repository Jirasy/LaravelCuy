{{-- Saklar tema gelap/terang: matahari <-> bulan, seluruhnya CSS (gaya di partials/head.blade.php) --}}
<button type="button" role="switch" aria-checked="false" data-theme-switch onclick="toggleTheme()"
        title="Ganti tema gelap/terang" aria-label="Mode gelap" class="theme-switch">
    <i class="ts-cloud" aria-hidden="true"></i>
    <i class="ts-star ts-star-1" aria-hidden="true"></i>
    <i class="ts-star ts-star-2" aria-hidden="true"></i>
    <i class="ts-star ts-star-3" aria-hidden="true"></i>
    <span class="ts-knob" aria-hidden="true">
        <i class="ts-crater ts-crater-1"></i>
        <i class="ts-crater ts-crater-2"></i>
        <i class="ts-crater ts-crater-3"></i>
    </span>
</button>
