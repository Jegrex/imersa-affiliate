@extends('layouts.preview')
@section('content')
<x-admin-heading title="Analitik klik" description="Pahami produk yang menarik perhatian pengunjung."/>
<form method="get" class="panel admin-filters">
<label class="field">Dari tanggal<input name="from" type="date" value="{{ $from }}" required>
</label>
<label class="field">Sampai tanggal<input name="to" type="date" value="{{ $to }}" required>
</label>
<button type="submit" class="button">Terapkan periode</button>
<a class="text-link" href="{{ route('preview.analytics') }}">Reset</a>
@if($empty)<input type="hidden" name="state" value="empty">
@endif<span class="small muted">Maksimal 366 hari · tanggal contoh dalam UTC</span>
</form>
@if($rangeError)<div class="notice notice-danger" role="alert">
<x-icon name="info"/>
<p>Periode harus berurutan dan tidak boleh melebihi 366 hari. Ubah rentang tanggal untuk melihat laporan.</p>
</div>
@endif
<div class="metric-grid">
<x-metric label="Total klik" :value="number_format($totalClicks, 0, ',', '.')" note="Sesuai periode terpilih" icon="link"/>
<x-metric label="Produk mendapat klik" :value="$ranking->where('period_clicks', '>', 0)->count()" note="Bukan jumlah pembelian" icon="box"/>
<x-metric label="Dengan video" :value="$empty ? 0 : $products->whereNotNull('video_url')->count()" note="Inventaris saat ini, di luar filter periode" icon="monitor"/>
<x-metric label="Tanpa video" :value="$empty ? 0 : $products->whereNull('video_url')->count()" note="Video bersifat opsional" icon="image"/>
</div>
<section class="panel">
<div class="section-heading">
<div>
<h2>Aktivitas klik</h2>
<p class="small muted">{{ $from }} sampai {{ $to }} · data contoh</p>
</div>
<span class="chart-legend">
<i>
</i>Klik</span>
</div>
@if($totalClicks === 0)<x-empty title="Belum ada klik pada periode ini" description="Pilih periode lain. Data contoh tersedia pada 1–24 September 2026."/>
@else<div class="bar-chart" role="img" aria-label="Grafik klik harian. Nilai tiap tanggal tersedia di tabel rincian di bawah.">
@foreach($chart->sortKeys() as $date => $count)<div class="chart-column">
<span>{{ $count }}</span>
<div style="--bar-height: {{ max(2, (int) round($count / max(1, $chart->max()) * 100)) }}%">
</div>
<small>{{ substr($date, 8) }}</small>
</div>
@endforeach</div>
<details class="chart-data">
<summary>Lihat rincian harian</summary>
<div class="table-scroll">
<table>
<thead>
<tr>
<th>Tanggal (UTC)</th>
<th class="numeric">Klik</th>
</tr>
</thead>
<tbody>
@foreach($chart->sortKeys() as $date => $count)<tr>
<td>{{ $date }}</td>
<td class="numeric">{{ $count }}</td>
</tr>
@endforeach</tbody>
</table>
</div>
</details>
@endif</section>
<section class="panel analytics-ranking">
<h2>Peringkat produk</h2>
<p class="small muted">Klik dalam periode terpilih. Klik tidak membuktikan pembelian.</p>
@if($totalClicks === 0)<p class="muted">Belum ada produk untuk diperingkatkan.</p>
@else<div class="table-scroll">
<table>
<thead>
<tr>
<th>Peringkat</th>
<th>Produk</th>
<th>Kategori</th>
<th class="numeric">Jumlah klik</th>
</tr>
</thead>
<tbody>
@foreach($ranking->where('period_clicks', '>', 0) as $item)<tr>
<td>
<span class="rank-index">{{ $loop->iteration }}</span>
</td>
<td>
<a class="text-link" href="{{ route('preview.products.edit', $item['id']) }}">{{ $item['name'] }}</a>
</td>
<td>{{ $item['category']['name'] }}</td>
<td class="numeric">{{ $item['period_clicks'] }}</td>
</tr>
@endforeach</tbody>
</table>
</div>
@endif</section>
@endsection
