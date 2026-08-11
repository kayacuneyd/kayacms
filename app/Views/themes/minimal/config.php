<?php

// Theme configuration schema for the minimal theme.
// Keys defined here are editable in Admin → Themes → Configure and exposed to
// every theme view as `$theme_config['key']`.
return [
    ['key' => 'container_width', 'label' => 'Container Width', 'type' => 'select', 'options' => ['narrow', 'full'], 'default' => 'narrow'],
    ['key' => 'show_author', 'label' => 'Show Author', 'type' => 'toggle', 'default' => '1'],
    ['key' => 'footer_text', 'label' => 'Footer Text', 'type' => 'textarea', 'default' => ''],
];
