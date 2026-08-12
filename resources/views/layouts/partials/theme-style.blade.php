{{--
    Overrides the --color-primary-* CSS variables (default: "Blue", see
    resources/css/app.css) with whichever preset the admin picked in
    Settings > General > Theme Color. Must be included AFTER @vite(...)'s
    compiled app.css <link> so this wins the cascade - same selectors,
    later in the document.

    $themeColor is provided by the View::composer in AppServiceProvider,
    same pattern as $siteName/$siteLogo.
--}}
@php
    $themes = config('themes.presets');
    $preset = $themes[$themeColor ?? config('themes.default')] ?? $themes[config('themes.default')];
@endphp
<style>
    :root {
        @foreach($preset['light'] as $shade => $hex)
        --color-primary-{{ $shade }}: {{ $hex }};
        @endforeach
    }
    .dark {
        @foreach($preset['dark'] as $shade => $hex)
        --color-primary-{{ $shade }}: {{ $hex }};
        @endforeach
    }
</style>
