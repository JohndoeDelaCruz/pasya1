<div
    id="pasya-page-loader"
    class="@isset($contentOnly) @if($contentOnly) pasya-page-loader-content @endif @endisset"
    role="status"
    aria-live="polite"
    aria-hidden="true"
>
    <div class="pasya-page-loader-mark" aria-hidden="true">
        <svg class="pasya-page-loader-ring" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="50" cy="50" r="44" stroke="#dcfce7" stroke-width="6"/>
            <circle cx="50" cy="50" r="44" stroke="#16a34a" stroke-width="6" stroke-linecap="round"
                stroke-dasharray="276" stroke-dashoffset="207"/>
        </svg>
        <img
            src="{{ asset('images/PASYA.png') }}"
            alt=""
            class="pasya-page-loader-logo"
        >
    </div>
    <p class="pasya-page-loader-label" aria-hidden="true">PASYA</p>
    <span class="sr-only">Loading PASYA page</span>
</div>
