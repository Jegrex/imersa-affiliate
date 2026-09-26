@extends('layouts.preview')
@section('content')
<section class="container section">
    <x-empty title="Filter belum dapat diterapkan" description="Periksa format pencarian, kategori, halaman, atau tanggal yang digunakan.">
        <div class="notice notice-danger" role="alert"><ul>
            @foreach($validationErrors->keys() as $field)
                <li>Isian {{ $field }} tidak valid.</li>
            @endforeach
        </ul></div>
        <a class="button" href="{{ url()->current() }}">Reset filter</a>
    </x-empty>
</section>
@endsection
