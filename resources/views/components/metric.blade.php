@props(['label', 'value', 'icon' => 'chart', 'note' => ''])
<article class="metric">
<div>
<span>{{ $label }}</span>
<x-icon :name="$icon"/>
</div>
<strong>{{ $value }}</strong>
@if($note)<p>{{ $note }}</p>
@endif</article>
