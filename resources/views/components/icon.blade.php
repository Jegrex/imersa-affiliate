@props(['name' => 'grid'])
@php
$paths = [
 'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/>
<rect x="14" y="3" width="7" height="7" rx="1.5"/>
<rect x="3" y="14" width="7" height="7" rx="1.5"/>
<rect x="14" y="14" width="7" height="7" rx="1.5"/>',
 'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>', 'back' => '<path d="M19 12H5m6-6-6 6 6 6"/>',
 'search' => '<circle cx="10.5" cy="10.5" r="6.5"/>
<path d="m16 16 5 5"/>',
 'laptop' => '<rect x="4" y="3" width="16" height="13" rx="2"/>
<path d="M2 20h20l-2-4H4z"/>',
 'phone' => '<rect x="6" y="2" width="12" height="20" rx="3"/>
<path d="M11 18h2"/>',
 'headphones' => '<path d="M4 14v-3a8 8 0 0 1 16 0v3"/>
<rect x="3" y="12" width="4" height="8" rx="2"/>
<rect x="17" y="12" width="4" height="8" rx="2"/>',
 'mouse' => '<rect x="6" y="2" width="12" height="20" rx="6"/>
<path d="M12 2v7"/>',
 'monitor' => '<rect x="2" y="3" width="20" height="14" rx="2"/>
<path d="M12 17v4m-5 0h10"/>',
 'box' => '<path d="m12 2 9 5v10l-9 5-9-5V7zM3 7l9 5 9-5M12 12v10M7 4.8l9 5"/>',
 'folder' => '<path d="M3 6a2 2 0 0 1 2-2h5l2 3h7a2 2 0 0 1 2 2v10H3z"/>',
 'chart' => '<path d="M3 3v18h18M7 16v-5m5 5V7m5 9V4"/>',
 'link' => '<path d="m10 13 4-4m-6 7-1 1a4 4 0 0 1-6-6l5-5a4 4 0 0 1 6 0m0 12a4 4 0 0 0 6 0l5-5a4 4 0 0 0-6-6l-1 1" transform="translate(1 0) scale(.9)"/>',
 'check' => '<path d="m5 12 4 4L19 6"/>', 'close' => '<path d="m6 6 12 12M6 18 18 6"/>',
 'plus' => '<path d="M12 5v14M5 12h14"/>', 'menu' => '<path d="M3 6h18M3 12h18M3 18h18"/>',
 'star' => '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9z"/>',
 'shield' => '<path d="m12 2 8 3v7c0 5-8 10-8 10S4 17 4 12V5z"/>
<path d="m8 11 3 3 5-6"/>',
 'edit' => '<path d="m14 5 5 5M3 21l5-1L21 7l-5-5L3 15z"/>',
 'trash' => '<path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7"/>',
 'logout' => '<path d="M9 3H3v18h6m5-15 6 6-6 6m-7-6h13"/>',
 'info' => '<circle cx="12" cy="12" r="9"/>
<path d="M12 11v6m0-10v1"/>',
 'image' => '<rect x="3" y="3" width="18" height="18" rx="2"/>
<circle cx="8" cy="8" r="1"/>
<path d="m3 17 5-5 4 4 4-7 5 8"/>',
 'review' => '<path d="M21 15a3 3 0 0 1-3 3H8l-5 4V5a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3zM7 7h10M7 12h7"/>',
 'clock' => '<circle cx="12" cy="12" r="9"/>
<path d="M12 7v5l3 2"/>',
];
@endphp
<svg {{ $attributes->class(['icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] ?? $paths['grid'] !!}</svg>
