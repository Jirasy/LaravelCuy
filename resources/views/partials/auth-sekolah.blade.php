{{-- Identitas sekolah di kepala kartu masuk/daftar: logo + nama lengkap. Sumber: config/sekolah.php --}}
@include('partials.logo-sekolah', ['ukuran' => 64, 'class' => 'auth-logo'])
<p class="auth-sekolah">{{ config('sekolah.nama') }}</p>
