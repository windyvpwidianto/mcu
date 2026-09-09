<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default LDAP Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the LDAP connections below you wish
    | to use as your default connection for all LDAP operations. Of
    | course you may add as many connections you'd like below.
    |
    */

    'default' => env('LDAP_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | LDAP Connections
    |--------------------------------------------------------------------------
    |
    | Below you may configure each LDAP connection your application requires
    | access to. Be sure to include a valid base DN - otherwise you may
    | not receive any results when performing LDAP search operations.
    |
    */

'connections' => [
    'default' => [
        'hosts'    => [env('LDAP_HOST', 'archimining.local')],
        'base_dn'  => env('LDAP_BASE_DN', 'DC=archimining,DC=local'),
        'username' => env('LDAP_USERNAME', null),
        'password' => env('LDAP_PASSWORD', null),
        'port'     => env('LDAP_PORT', 636),
        'use_ssl'  => env('LDAP_SSL', true),
        'use_tls'  => env('LDAP_TLS', false),
        
        // Opsi ini setara dengan InsecureSkipVerify
        'options' => [
            // Jika LDAP_TLS_REQCERT di-set ke 'never', PHP akan mengabaikan sertifikat SSL (berguna untuk Self-Signed)
            LDAP_OPT_X_TLS_REQUIRE_CERT => env('LDAP_TLS_REQCERT', 'hard') === 'never' ? LDAP_OPT_X_TLS_NEVER : LDAP_OPT_X_TLS_HARD,
        ],
    ],
],


    /*
    |--------------------------------------------------------------------------
    | LDAP Logging
    |--------------------------------------------------------------------------
    |
    | When LDAP logging is enabled, all LDAP search and authentication
    | operations are logged using the default application logging
    | driver. This can assist in debugging issues and more.
    |
    */

    'logging' => [
        'enabled' => env('LDAP_LOGGING', true),
        'channel' => env('LOG_CHANNEL', 'stack'),
        'level' => env('LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | LDAP Cache
    |--------------------------------------------------------------------------
    |
    | LDAP caching enables the ability of caching search results using the
    | query builder. This is great for running expensive operations that
    | may take many seconds to complete, such as a pagination request.
    |
    */

    'cache' => [
        'enabled' => env('LDAP_CACHE', false),
        'driver' => env('CACHE_DRIVER', 'file'),
    ],

];
