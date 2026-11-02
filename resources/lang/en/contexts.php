<?php

declare(strict_types=1);

return [
    'navigation' => [
        'label' => 'Locale contexts',
    ],
    'title' => 'Locale contexts',
    'fields' => [
        'name' => 'Name',
        'all_locales' => 'All active locales',
        'locales' => 'Locales',
        'default' => 'Default locale',
        'status' => 'Status',
    ],
    'helpers' => [
        'name' => 'Lowercase letters, digits, "-" and "_". Used in code, for example UseLocaleContext::with(\'shop\').',
        'all_locales' => 'Include every active locale, including locales activated later.',
        'locales' => 'The global default locale is always included.',
        'default' => 'Leave empty to follow the global default locale.',
    ],
    'values' => [
        'all_locales' => 'All active locales',
        'global_default' => 'Global default (:locale)',
    ],
    'statuses' => [
        'declared' => 'Declared in config',
        'stored' => 'Stored',
        'default_context' => 'Default context',
    ],
    'actions' => [
        'create' => ['label' => 'Create context', 'success' => 'Context created'],
        'edit' => ['label' => 'Edit', 'success' => 'Context updated'],
        'delete' => ['label' => 'Delete', 'success' => 'Context deleted'],
    ],
];
