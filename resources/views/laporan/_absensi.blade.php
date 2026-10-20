<table>
    <thead>
        <tr>
            <th class="tengah">No</th>
            <th>Nama</th>
            <th>Kelas</th>
            @foreach($d['pertemuans'] as $p)
                <th class="tengah">{{ $p->tanggal->format('d/m') }}</th>
            @endforeach
            <th class="tengah">H</th>
            <th class="tengah">I</th>
            <th class="tengah">S</th>
            <th class="tengah">A</th>
            <th class="tengah">% Hadir</th>
        </tr>
    </thead>
    <tbody>
        @forelse($d['baris'] as $b)
            <tr>
                <td class="tengah">{{ $loop->iteration }}</td>
                <td>{{ $b['nama'] }}</td>
                <td>{{ $b['kelas'] ?? '-' }}</td>
                @foreach($d['pertemuans'] as $p)
                    <td class="tengah">{{ $b['sel'][$p->id] ?? '-' }}</td>
                @endforeach
                <td class="tengah">{{ $b['H'] }}</td>
                <td class="tengah">{{ $b['I'] }}</td>
                <td class="tengah">{{ $b['S'] }}</td>
                <td class="tengah">{{ $b['A'] }}</td>
                <td class="tengah">{{ $b['persen'] !== null ? $b['persen'] . '%' : '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="{{ 8 + $d['pertemuans']->count() }}" class="tengah">Tidak ada data pada periode ini.</td></tr>
        @endforelse
    </tbody>
</table>