@props(['variant' => 'pill'])
@php
    // الرابط من لوحة التحكم: المحتوى ← التواصل ← رابط موقع المصنع (contact.map_url)
    $mapHref = factory_map_url();
    $mapLabel = (string) setting('contact.map_label', 'موقع المصنع على الخريطة');
@endphp
@if($mapHref !== '')
    <a {{ $attributes->class(['map-btn', 'map-btn--' . $variant]) }}
       href="{{ $mapHref }}" target="_blank" rel="noopener noreferrer"
       aria-label="{{ $mapLabel }} — {{ ui('factory_map_aria') }}" data-map-btn>
        <svg class="map-btn-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 21s-7-6.2-7-11.2A7 7 0 0 1 19 9.8C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.8" r="2.6"/></svg>
        <span class="map-btn-label">{{ $mapLabel }}</span>
        <svg class="map-btn-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 17 17 7"/><path d="M8 7h9v9"/></svg>
    </a>
@endif
