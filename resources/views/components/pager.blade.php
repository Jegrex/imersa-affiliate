@props(['listing'])
<nav class="pagination" aria-label="Navigasi halaman">
<span class="muted small">{{ $listing->total() ? $listing->firstItem().'–'.$listing->lastItem() : '0' }} dari {{ $listing->total() }} produk</span>
<div class="inline-actions">
@if($listing->onFirstPage())<span class="button button-quiet disabled" aria-disabled="true">Sebelumnya</span>
@else<a class="button button-quiet" href="{{ $listing->previousPageUrl() }}">Sebelumnya</a>
@endif
<span class="page-number" aria-current="page">{{ $listing->currentPage() }}</span>
@if($listing->hasMorePages())<a class="button button-quiet" href="{{ $listing->nextPageUrl() }}">Berikutnya</a>
@else<span class="button button-quiet disabled" aria-disabled="true">Berikutnya</span>
@endif
</div>
</nav>
