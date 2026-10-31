@props(['whatsapp' => null, 'instagram' => null, 'x' => null, 'website' => '#'])
@php($siteUrl = safe_url(setting('contact.website') ?: $website))
<div id="sunbird-intro" class="sunbird" data-sunbird>
    <div class="sb-lang"><x-lang-switch variant="intro" /></div>
    <div class="sb-stage" data-sb-stage>
      <div class="sb-inner">
        <div class="sb-center">
            <div class="sb-halo" aria-hidden="true" data-sb-halo></div>
            <span class="sb-ring" aria-hidden="true"></span>
            <figure class="sb-logo" data-sb-logo>
                <img src="{{ setting_image('sunbird_logo', 'media/rashfa/sunbird-logo.png') }}" alt="{{ setting('sunbird_logo_alt', 'سن بيرد Sun Bird') }}" width="640" height="640" fetchpriority="high"
                     onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                <span class="sb-logo-fallback">{{ setting('sunbird_name', 'Sun Bird') }}</span>
            </figure>
            <p class="sb-tagline" data-sb-tagline>{{ setting('sunbird_tagline', 'انتعاش الطبيعة في كل رشفة') }}</p>
            <button type="button" class="sb-cta" data-sb-cta>
                <span>{{ setting('sunbird_cta', 'اكتشف منتجاتنا') }}</span>
            </button>
        </div>

        <nav class="sb-social" aria-label="{{ ui('contact_us') }}" data-sb-social>
            <div class="sb-icons">
                <x-social-links :whatsapp="$whatsapp" :instagram="$instagram" :x="$x" />
            </div>
            <div class="sb-links">
            @if($siteUrl !== '')
                <a class="sb-site" href="{{ $siteUrl }}" @if(preg_match('#^https?://#i', $siteUrl)) target="_blank" rel="noopener noreferrer" @endif>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18a14 14 0 0 1 0-18z"/></svg>
                    <span>{{ setting('sunbird_site_label', 'زيارة موقعنا') }}</span>
                </a>
            @endif
            <x-map-button variant="intro" class="sb-map" />
            </div>
        </nav>
      </div>
    </div>

    <span class="sb-wave-lead" aria-hidden="true" data-sb-wave-lead></span>
    <span class="sb-wave" aria-hidden="true" data-sb-wave></span>
</div>
