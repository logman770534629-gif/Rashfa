@props(['whatsapp' => null, 'instagram' => null, 'x' => null])
@php
    // كل رابط يمر عبر safe_url(): يُضاف https:// تلقائياً إن نُسي، ويُرفض أي بروتوكول خطير. الرابط الفارغ/غير الصالح لا يُطبع أبداً.
    $igUrl = safe_url(setting('social.instagram') ?: $instagram) ?: 'https://instagram.com/';
    $xUrl = safe_url(setting('social.x') ?: $x) ?: 'https://x.com/';
    $waDigits = preg_replace('/\D/', '', (string) (setting('contact.whatsapp') ?: $whatsapp));
    $waLink = $waDigits ? 'https://wa.me/' . $waDigits : 'https://wa.me/';
@endphp
<a href="{{ $igUrl }}" target="_blank" rel="noopener noreferrer" aria-label="{{ ui('instagram') }}" data-social="instagram">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4.1"/><circle cx="17.4" cy="6.6" r=".9" fill="currentColor" stroke="none"/></svg>
</a>
<a href="{{ $xUrl }}" target="_blank" rel="noopener noreferrer" aria-label="{{ ui('x') }}" data-social="x">
    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
</a>
<a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" aria-label="{{ ui('whatsapp') }}" data-social="whatsapp">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.2 20.8l1.5-4.6A8.9 8.9 0 1 1 8 19.5l-4.8 1.3z"/><path d="M9.2 8.4c-.3.6-.3 1.5.4 2.8.9 1.6 2.2 2.8 3.8 3.5 1 .4 1.7.2 2.2-.3l.4-.8-1.8-1-.9.6c-.9-.4-1.8-1.2-2.3-2.2l.6-.9-1-1.8-.6.1z" fill="currentColor" stroke="none"/></svg>
</a>
