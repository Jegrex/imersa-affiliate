@props(['product'])
<article class="product-card">
    <a class="product-picture" href="{{ route('preview.detail', $product['slug']) }}" tabindex="-1" aria-hidden="true">
        @if($product['image'])<img src="{{ asset('images/demo/'.$product['image']) }}" alt="" loading="lazy" width="640" height="480" data-media>
@endif
        <span class="media-fallback" @if($product['image']) hidden @endif>
<x-icon name="image"/>Gambar belum tersedia</span>
    </a>
    <div class="product-card-body">
<span class="eyebrow">{{ $product['brand'] }}</span>
<h3>
<a href="{{ route('preview.detail', $product['slug']) }}">{{ $product['name'] }}</a>
</h3>
        <div class="price-rating">
<span class="{{ $product['price_amount'] === null ? 'muted small' : 'price' }}">{{ $product['price_amount'] === null ? 'Harga tidak tersedia' : 'Rp '.number_format($product['price_amount'], 0, ',', '.') }}</span>
            @if($product['shopee_rating'] !== null)<span class="rating">
<x-icon name="star"/>{{ number_format($product['shopee_rating'], 1) }}</span>
@endif
        </div>
        @if($product['shopee_rating'] === null)<p class="small muted rating-note">Rating belum tersedia</p>
@endif
        <a class="button button-outline full" href="{{ route('preview.detail', $product['slug']) }}">Lihat produk<x-icon name="arrow"/>
</a>
        @if($product['approval'] !== 'approved')<span class="availability">Tautan pembelian belum tersedia</span>
@endif
    </div>
</article>
