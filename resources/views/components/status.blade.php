@props(['value'])
@php($labels = ['active' => 'Aktif', 'draft' => 'Draft', 'approved' => 'Disetujui', 'pending' => 'Menunggu', 'rejected' => 'Ditolak', 'hidden' => 'Disembunyikan'])
<span class="badge badge-{{ $value }}">{{ $labels[$value] ?? $value }}</span>
