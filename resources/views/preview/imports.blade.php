@extends('layouts.preview')
@section('content')
<x-admin-heading title="Review impor" description="Tinjau sumber data sebelum menjadi bagian dari katalog.">
<span class="badge badge-pending">Menunggu keputusan PO</span>
</x-admin-heading>
<div class="notice notice-warning">
<x-icon name="shield"/>
<div>
<strong>Normalisasi belum dapat dijalankan.</strong>
<p>Mekanisme penentuan lingkup elektronik untuk data impor masih menunggu keputusan Product Owner. Data mentah tidak otomatis diterbitkan menjadi produk.</p>
</div>
</div>
<section class="panel">
<div class="section-heading">
<div>
<h2>Batch data contoh</h2>
<p class="small muted">Contoh struktur review, bukan hasil unggahan atau validasi nyata.</p>
</div>
<button class="button button-outline" disabled>Registrasi CSV belum terhubung</button>
</div>
<div class="table-scroll">
<table>
<thead>
<tr>
<th>Nama batch</th>
<th>Sumber</th>
<th>Baris</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<tr>
<td>
<strong>CONTOH-KATALOG-001</strong>
</td>
<td>Fixture frontend</td>
<td>2</td>
<td>
<x-status value="pending"/>
</td>
</tr>
</tbody>
</table>
</div>
<details class="import-detail" open>
<summary>Rincian batch dan bukti mentah</summary>
<dl class="metadata-grid">
<div>
<dt>Checksum</dt>
<dd>Belum dihitung — ditentukan server dari file asli</dd>
</div>
<div>
<dt>Asal data</dt>
<dd>Contoh buatan untuk peninjauan antarmuka</dd>
</div>
</dl>
<div class="table-scroll">
<table>
<thead>
<tr>
<th>Baris</th>
<th>Data mentah</th>
<th>Kualitas data</th>
<th>Pencocokan identitas</th>
</tr>
</thead>
<tbody>
<tr>
<td>1</td>
<td>
<code>{{ '<p>Nama produk contoh</p>' }}</code>
</td>
<td>Belum divalidasi</td>
<td>Belum dicocokkan</td>
</tr>
<tr>
<td>2</td>
<td>
<code>{{ '<p>Nama serupa, sumber berbeda</p>' }}</code>
</td>
<td>Perlu ditinjau</td>
<td>Ambigu — keputusan manual</td>
</tr>
</tbody>
</table>
</div>
<div class="form-actions">
<button class="button button-outline" data-feedback-title="Pencocokan manual" data-feedback-message="Pada integrasi impor, Admin meninjau identitas produk dan asal data. Nama yang sama tidak otomatis digabung. Kontrak rinci menunggu keputusan tim.">Tinjau pencocokan contoh</button>
<button class="button" disabled>Normalisasi terkunci</button>
</div>
</details>
</section>
@endsection
