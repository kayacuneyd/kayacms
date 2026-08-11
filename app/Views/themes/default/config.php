<?php

// Theme configuration schema for the default theme.
// Keys defined here are editable in Admin → Themes → Configure and exposed to
// every theme view as `$theme_config['key']`.
return [
    ['key' => 'brand_color', 'label' => 'Brand Color', 'type' => 'text', 'default' => '#2563eb'],
    ['key' => 'show_search', 'label' => 'Show Search Bar', 'type' => 'toggle', 'default' => '1'],
    ['key' => 'show_featured', 'label' => 'Show Featured (home)', 'type' => 'toggle', 'default' => '1'],
    ['key' => 'footer_text', 'label' => 'Footer Text', 'type' => 'textarea', 'default' => ''],
    ['key' => 'header_layout', 'label' => 'Header Layout', 'type' => 'select', 'options' => ['boxed', 'full'], 'default' => 'boxed'],
];