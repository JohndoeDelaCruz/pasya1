<div
    id="pasya-page-loader"
    class="@isset($contentOnly) @if($contentOnly) pasya-page-loader-content @endif @endisset"
    role="status"
    aria-live="polite"
    aria-hidden="true"
>
    <div class="pasya-page-loader-mark" aria-hidden="true">
        <img
            src="{{ asset('images/PASYA.png') }}"
            alt=""
            class="pasya-page-loader-logo"
        >
    </div>
    <span class="sr-only">Loading PASYA page</span>
</div>
