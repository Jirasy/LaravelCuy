<table>
    <thead>
        <tr>
            <th class="tengah">No</th>
            <th>Nama</th>
            <th>Kelas</th>
            @foreach(\App\Models\EkskulNilai::ASPEK as $label)
                <th class="tengah">{{ $label }}</th>
            @endforeach
            <th class="tengah">Rata-rata</th>
            <th class="tengah">Predikat</th>
            <th>Saran</th>
            <th>Kritik</th>
        </tr>
    </thead>
    <tbody>
        @forelse($d['baris'] as $b)
            @php $n = $b['nilai']; @endphp
            <tr>
                <td class="tengah">{{ $loop->iteration }}</td>
                <td>{{ $b['nama'] }}</td>
                <td>{{ $b['kelas'] ?? '-' }}</td>
                @foreach(\App\Models\EkskulNilai::ASPEK as $kolom => $label)
                    <td class="tengah">{{ $n ? $n->{$kolom} : '-' }}</td>
                @endforeach
                <td class="tengah">{{ $n ? number_format($n->rata_rata, 1) : '-' }}</td>
                <td class="tengah">{{ $n ? $n->predikat : '-' }}</td>
                <td>{{ $n->saran ?? '' }}</td>
                <td>{{ $n->kritik ?? '' }}</td>
            </tr>
        @empty
            <tr><td colspan="11" class="tengah">Tidak ada data.</td></tr>
        @endforelse
    </tbody>
</table>