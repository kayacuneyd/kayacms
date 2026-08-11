<?php

// Theme configuration schema for the corporate theme (K&Z Hukuk style).
// Fields here are editable in Admin → Themes → Configure and exposed to every
// theme view as `$theme_config['key']`. `repeater` fields resolve to arrays.
return [
    // Brand
    ['key' => 'brand_color', 'label' => 'Accent Color', 'type' => 'text', 'default' => '#de252a'],

    // Hero slider slides (repeater)
    ['key' => 'hero', 'label' => 'Hero Slides (repeater)', 'type' => 'repeater', 'default' => [], 'fields' => [
        ['name' => 'headline', 'label' => 'Headline', 'type' => 'text', 'default' => ''],
        ['name' => 'image', 'label' => 'Background Image URL', 'type' => 'text', 'default' => ''],
        ['name' => 'icon', 'label' => 'Icon URL', 'type' => 'text', 'default' => ''],
        ['name' => 'name', 'label' => 'Pagination Label', 'type' => 'text', 'default' => ''],
        ['name' => 'desc', 'label' => 'Pagination Description', 'type' => 'textarea', 'default' => ''],
    ]],

    // Intro (Kaplan & Zorer Hukuk Bürosu)
    ['key' => 'intro_title', 'label' => 'Intro Title', 'type' => 'text', 'default' => 'Kaplan & Zorer Hukuk Bürosu'],
    ['key' => 'intro_text', 'label' => 'Intro Text', 'type' => 'textarea', 'default' => ''],
    ['key' => 'intro_image', 'label' => 'Intro Image URL', 'type' => 'text', 'default' => ''],
    ['key' => 'vertical_text', 'label' => 'Vertical Side Text', 'type' => 'text', 'default' => 'K&Z'],

    // Practice areas slider (repeater)
    ['key' => 'practice_title', 'label' => 'Practice Areas Title', 'type' => 'text', 'default' => ''],
    ['key' => 'practice', 'label' => 'Practice Areas (repeater)', 'type' => 'repeater', 'default' => [], 'fields' => [
        ['name' => 'icon', 'label' => 'Icon URL', 'type' => 'text', 'default' => ''],
        ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => ''],
        ['name' => 'desc', 'label' => 'Description', 'type' => 'textarea', 'default' => ''],
    ]],

    // Testimonials slider (repeater)
    ['key' => 'references_title', 'label' => 'References Title', 'type' => 'text', 'default' => 'Referanslarımız'],
    ['key' => 'references', 'label' => 'References (repeater)', 'type' => 'repeater', 'default' => [], 'fields' => [
        ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'default' => ''],
        ['name' => 'quote', 'label' => 'Quote', 'type' => 'textarea', 'default' => ''],
    ]],

    // Team page (repeater)
    ['key' => 'team_title', 'label' => 'Team Page Title', 'type' => 'text', 'default' => 'Takımımız'],
    ['key' => 'team', 'label' => 'Team Members (repeater)', 'type' => 'repeater', 'default' => [], 'fields' => [
        ['name' => 'photo', 'label' => 'Photo URL', 'type' => 'text', 'default' => ''],
        ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'default' => ''],
        ['name' => 'email', 'label' => 'Email', 'type' => 'text', 'default' => ''],
        ['name' => 'linkedin', 'label' => 'LinkedIn URL', 'type' => 'text', 'default' => ''],
    ]],

    // About page values (repeater)
    ['key' => 'about_title', 'label' => 'About Page Title', 'type' => 'text', 'default' => 'Hakkımızda'],
    ['key' => 'about', 'label' => 'About Values (repeater)', 'type' => 'repeater', 'default' => [], 'fields' => [
        ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => ''],
        ['name' => 'desc', 'label' => 'Description', 'type' => 'textarea', 'default' => ''],
    ]],

    // CTA
    ['key' => 'cta_kicker', 'label' => 'CTA Kicker', 'type' => 'text', 'default' => 'Size Nasıl Yardımcı Olabiliriz?'],
    ['key' => 'cta_title', 'label' => 'CTA Title', 'type' => 'textarea', 'default' => ''],
    ['key' => 'cta_phone', 'label' => 'CTA Phone Label', 'type' => 'text', 'default' => 'Danışmanlık Talep Et'],
    ['key' => 'cta_phone_url', 'label' => 'CTA Phone URL', 'type' => 'text', 'default' => 'tel:+15551234567'],
    ['key' => 'cta_btn_text', 'label' => 'CTA Button Label', 'type' => 'text', 'default' => 'Hemen Randevu Al'],
    ['key' => 'cta_btn_url', 'label' => 'CTA Button URL', 'type' => 'text', 'default' => '/iletisim'],

    // Blog section
    ['key' => 'show_blog', 'label' => 'Show Blog Section', 'type' => 'toggle', 'default' => '1'],

    // Footer
    ['key' => 'footer_phone', 'label' => 'Footer Phone', 'type' => 'text', 'default' => ''],
    ['key' => 'footer_fax', 'label' => 'Footer Fax', 'type' => 'text', 'default' => ''],
    ['key' => 'footer_address', 'label' => 'Footer Address', 'type' => 'textarea', 'default' => ''],
    ['key' => 'footer_email', 'label' => 'Footer Email', 'type' => 'text', 'default' => ''],
];