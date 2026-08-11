<?php

// Theme configuration schema for the landing theme.
// Keys defined here are editable in Admin → Themes → Configure and exposed to
// every theme view as `$theme_config['key']`.
return [
    // Brand
    ['key' => 'brand_color', 'label' => 'Brand Color', 'type' => 'text', 'default' => '#6366f1'],

    // Hero
    ['key' => 'hero_badge', 'label' => 'Hero Badge', 'type' => 'text', 'default' => 'KayaCMS powered'],
    ['key' => 'hero_headline', 'label' => 'Hero Headline', 'type' => 'text', 'default' => 'Build something people love.'],
    ['key' => 'hero_subheadline', 'label' => 'Hero Subheadline', 'type' => 'textarea', 'default' => 'A modern, headless CMS for teams who want ownership of their content, their data and their stack.'],
    ['key' => 'hero_cta_text', 'label' => 'Hero CTA Label', 'type' => 'text', 'default' => 'Get Started'],
    ['key' => 'hero_cta_url', 'label' => 'Hero CTA URL', 'type' => 'text', 'default' => '/#features'],

    // Features
    ['key' => 'features_title', 'label' => 'Features Section Title', 'type' => 'text', 'default' => 'Everything your content needs'],
    ['key' => 'features_intro', 'label' => 'Features Section Intro', 'type' => 'textarea', 'default' => 'One line per feature, formatted as: Icon|Title|Description'],
    ['key' => 'features', 'label' => 'Features (Icon|Title|Description per line)', 'type' => 'textarea', 'default' => "🚀|Fast by default|Pages render in milliseconds with built-in caching.\n🔒|Secure from day one|Role-based access, JWT auth and GDPR tooling.\n🧩|Modular core|Content, media, menus, taxonomy and themes out of the box.\n🌍|Built multilingual|Locale-aware routing, translations and hreflang support.\n📦|Your data, your stack|SQLite out of the box, portable and easy to deploy.\n🎨|Themable|Swap landing, blog or custom themes with one click."],

    // CTA
    ['key' => 'cta_title', 'label' => 'CTA Title', 'type' => 'text', 'default' => 'Ready when you are.'],
    ['key' => 'cta_text', 'label' => 'CTA Text', 'type' => 'textarea', 'default' => 'Install, configure and start publishing in minutes.'],
    ['key' => 'cta_button_text', 'label' => 'CTA Button Label', 'type' => 'text', 'default' => 'Start Now'],
    ['key' => 'cta_button_url', 'label' => 'CTA Button URL', 'type' => 'text', 'default' => '/admin'],

    // Blog section on homepage
    ['key' => 'show_articles', 'label' => 'Show Latest Articles', 'type' => 'toggle', 'default' => '1'],
    ['key' => 'articles_title', 'label' => 'Articles Section Title', 'type' => 'text', 'default' => 'Latest from the blog'],

    // Footer
    ['key' => 'footer_text', 'label' => 'Footer Text', 'type' => 'textarea', 'default' => ''],
    ['key' => 'footer_email', 'label' => 'Contact Email', 'type' => 'text', 'default' => ''],
];