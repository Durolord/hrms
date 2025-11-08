<?php

return [
    'shield_resource' => [
        'should_register_navigation' => true,
        'slug' => 'roles',
        'navigation_sort' => -1,
        'navigation_badge' => true,
        'navigation_group' => true,
        'is_globally_searchable' => false,
        'show_model_path' => true,
        'is_scoped_to_tenant' => true,
        'cluster' => null,
    ],
    'tenant_model' => null,
    'auth_provider_model' => [
        'fqcn' => 'App\\Models\\User',
    ],
    'super_admin' => [
        'enabled' => true,
        'name' => 'Admin',
        'define_via_gate' => false,
        'intercept_gate' => 'before',
    ],
    'panel_user' => [
        'enabled' => true,
        'name' => 'panel_user',
    ],
    'permission_prefixes' => [
        'resource' => [
            'view',
            'view_any',
        ],
        'page' => 'page',
        'widget' => 'widget',
    ],
    'entities' => [
        'pages' => true,
        'widgets' => true,
        'resources' => true,
        'custom_permissions' => false,
    ],
    'generator' => [
        'option' => 'policies_and_permissions',
        'policy_directory' => 'Policies/test',
        'policy_namespace' => 'Policies/test',
    ],
    'exclude' => [
        'enabled' => false,
        'pages' => [
            'Dashboard',
        ],
        'widgets' => [
        ],
        'resources' => [],
    ],
    'discovery' => [
        'discover_all_resources' => false,
        'discover_all_widgets' => true,
        'discover_all_pages' => true,
    ],
    'register_role_policy' => [
        'enabled' => true,
    ],
];
