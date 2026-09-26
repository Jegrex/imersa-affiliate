@props(['title' => 'Belum ada data', 'description' => 'Data akan muncul di sini setelah tersedia.'])
<div class="empty-state">
<span class="empty-symbol">
<x-icon name="search"/>
</span>
<h3>{{ $title }}</h3>
<p>{{ $description }}</p>{{ $slot }}</div>
