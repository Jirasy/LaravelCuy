<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\EksPengguna;
use App\Models\EksSiswa;
use App\Models\EksEskul;

class EksPenggunaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Admin
        EksPengguna::create([
            'username' => 'admin',
            'nama'     => 'Administrator Utama',
            'password' => Hash::make('password123'),
            'peran'    => 'Admin',
        ]);

        // 2. Buat Akun Pembina (Pak Budi)
        $pembina1 = EksPengguna::create([
            'username' => 'pembina_futsal',
            'nama'     => 'Budi Santoso, S.Pd',
            'password' => Hash::make('password123'),
            'peran'    => 'Pembina',
        ]);

        // Buat Akun Pembina (Bu Siti)
        $pembina2 = EksPengguna::create([
            'username' => 'pembina_pramuka',
            'nama'     => 'Siti Aminah, S.Pd',
            'password' => Hash::make('password123'),
            'peran'    => 'Pembina',
        ]);

        // 3. Buat Akun Siswa (Login menggunakan NIS)
        $penggunaSiswa1 = EksPengguna::create([
            'username' => '1023001', // NIS dijadikan username
            'nama'     => 'Ahmad Rizky',
            'password' => Hash::make('password123'),
            'peran'    => 'Siswa',
        ]);

        // Profil Detail Siswa 1
        $siswa1 = EksSiswa::create([
            'pengguna_id' => $penggunaSiswa1->id,
            'nis'         => '1023001',
            'kelas'       => 'XI PPLG 1',
            'jurusan'     => 'PPLG',
            'no_tlp'      => '081234567890',
        ]);

        $penggunaSiswa2 = EksPengguna::create([
            'username' => '1023002',
            'nama'     => 'Siti Rahmawati',
            'password' => Hash::make('password123'),
            'peran'    => 'Siswa',
        ]);

        // Profil Detail Siswa 2
        $siswa2 = EksSiswa::create([
            'pengguna_id' => $penggunaSiswa2->id,
            'nis'         => '1023002',
            'kelas'       => 'XI PPLG 2',
            'jurusan'     => 'PPLG',
            'no_tlp'      => '089876543210',
        ]);

        // 4. Buat Data Ekstrakurikuler Awal
        EksEskul::create([
            'eks_name'        => 'Futsal',
            'eks_description' => 'Ekskul olahraga futsal untuk melatih ketangkasan dan kerjasama tim.',
            'eks_pembina_id'  => $pembina1->id,
            'eks_jadwal'       => 'Jumat, 15:30 WIB',
        ]);

        EksEskul::create([
            'eks_name'        => 'Pramuka',
            'eks_description' => 'Ekskul kepramukaan wajib untuk pembentukan karakter dan kepemimpinan.',
            'eks_pembina_id'  => $pembina2->id,
            'eks_jadwal'       => 'Sabtu, 08:00 WIB',
        ]);
    }
}