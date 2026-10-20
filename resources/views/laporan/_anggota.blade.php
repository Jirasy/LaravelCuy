<table>
    <thead>
        <tr>
            <th class="tengah">No</th>
            <th>Nama</th>
            <th>Kelas</th>
            <th>NISN</th>
            <th>Jabatan</th>
            <th>Angkatan Ekskul</th>
            <th>Mulai</th>
        </tr>
    </thead>
    <tbody>
        @forelse($d['baris'] as $a)
            <tr>
                <td class="tengah">{{ $loop->iteration }}</td>
                <td>{{ $a->nama_pendaftar }}</td>
                <td>{{ $a->kelas ?? '-' }}</td>
                <td>{{ $a->nisn ?? '-' }}</td>
                <td>{{ $a->jabatan_label }}</td>
                <td>{{ $a->angkatan_ekskul ?? '-' }}</td>
                <td>{{ $a->tahun_mulai ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="tengah">Tidak ada anggota aktif.</td></tr>
        @endforelse
    </tbody>
</table>