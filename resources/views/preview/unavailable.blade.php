@extends('layouts.preview')
@section('content')
<section class="container section">
    <x-empty title="Halaman belum tersedia" description="Produk atau kategori mungkin belum diterbitkan, sudah disembunyikan, atau tidak ditemukan.">
        <a class="button" href="{{ $page === 'detail' ? route('preview.catalog') : route('preview.products') }}">{{ $page === 'detail' ? 'Kembali ke katalog' : 'Kembali ke produk' }}</a>
    </x-empty>
</section>
@endsection
