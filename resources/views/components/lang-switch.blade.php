@props(['variant' => 'nav'])
@php
    $current = app()->getLocale();
    $target = $current === 'ar' ? 'en' : 'ar';
@endphp
<a href="{{ route('lang.switch', $target) }}" class="lang-switch lang-switch--{{ $variant }}" data-lang-switch data-locale="{{ $target }}" hreflang="{{ $target }}" lang="{{ $target }}"
   aria-label="{{ ui('lang_switch_aria') }}" title="{{ config('rashfa.locales.' . $target . '.name') }}">
    <span class="ls-ring" aria-hidden="true"></span>
    <span class="ls-orb" aria-hidden="true">
        <span class="ls-face ls-front">{{ config('rashfa.locales.' . $target . '.short') }}</span>
        <span class="ls-face ls-back">{{ config('rashfa.locales.' . $current . '.short') }}</span>
    </span>
</a>
