<?php

if (! function_exists('localized_url')) {
    /**
     * Generate a URL with locale prefix.
     *
     * @param string      $path   The path (e.g. /content/hello)
     * @param string|null $locale Locale code, null for current detected locale
     */
    function localized_url(string $path, ?string $locale = null): string
    {
        $settingModel = new \Setting\Models\SettingModel();
        $defaultLocale = $settingModel->getSetting('site_default_locale', 'tr');
        $activeLocales = array_filter(array_map('trim', explode(',', $settingModel->getSetting('site_active_locales', $defaultLocale))));

        if ($locale === null) {
            $uri = service('uri');
            $segments = $uri->getSegments();
            $locale = (!empty($segments[0]) && in_array($segments[0], $activeLocales, true)) ? $segments[0] : $defaultLocale;
        }

        $base = rtrim(base_url('/'), '/');
        $prefix = $locale === $defaultLocale ? '' : '/' . $locale;
        $path = '/' . ltrim($path, '/');
        return $base . $prefix . $path;
    }
}

if (! function_exists('current_locale')) {
    function current_locale(): string
    {
        $settingModel = new \Setting\Models\SettingModel();
        $defaultLocale = $settingModel->getSetting('site_default_locale', 'tr');
        $activeLocales = array_filter(array_map('trim', explode(',', $settingModel->getSetting('site_active_locales', $defaultLocale))));

        $uri = service('uri');
        $segments = $uri->getSegments();
        return (!empty($segments[0]) && in_array($segments[0], $activeLocales, true)) ? $segments[0] : $defaultLocale;
    }
}

if (! function_exists('locale_flag')) {
    function locale_flag(string $loc): string
    {
        $flags = ['tr' => '🇹🇷', 'en' => '🇬🇧', 'de' => '🇩🇪', 'fr' => '🇫🇷', 'es' => '🇪🇸'];
        return $flags[$loc] ?? '🌐';
    }
}
