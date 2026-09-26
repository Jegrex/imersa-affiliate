@props(['eyebrow' => 'WORKSPACE IMERSA', 'title', 'description' => ''])
<div class="admin-heading">
<div>
<span class="eyebrow">{{ $eyebrow }}</span>
<h1>{{ $title }}</h1>
@if($description)<p>{{ $description }}</p>
@endif</div>
<div class="inline-actions">{{ $slot }}</div>
</div>
