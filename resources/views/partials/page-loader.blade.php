<div
    id="pasya-page-loader"
    class="@isset($contentOnly) @if($contentOnly) pasya-page-loader-content @endif @endisset"
    role="status"
    aria-live="polite"
    aria-hidden="true"
>
    <div class="pasya-page-loader-mark" aria-hidden="true">
        <div id="pasya-page-loader-animation" class="pasya-page-loader-animation"></div>
        <img
            src="{{ asset('images/PASYA.png') }}"
            alt=""
            class="pasya-page-loader-fallback"
        >
    </div>
    <span class="sr-only">Loading PASYA page</span>
</div>
