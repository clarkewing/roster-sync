<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel Console Dusk Paths
    |--------------------------------------------------------------------------
    |
    | Here you may configure the name of screenshots and logs directory as you wish.
    */
    'paths' => [
        'screenshots' => storage_path('laravel-console-dusk/screenshots'),
        'log'         => storage_path('laravel-console-dusk/log'),
        'source'      => storage_path('laravel-console-dusk/source'),
        'downloads'   => storage_path('laravel-console-dusk/downloads'),
    ],

    /*
    | --------------------------------------------------------------------------
    | Headless Mode
    | --------------------------------------------------------------------------
    |
    | When false it will show a Chrome window while running. Within production
    | it will be forced to run in headless mode.
    */
    'headless' => true,

    /*
    | --------------------------------------------------------------------------
    | Driver Configuration
    | --------------------------------------------------------------------------
    |
    | Here you may pass options to the browser driver being automated.
    |
    | A list of available Chromium command line switches is available at
    | https://peter.sh/experiments/chromium-command-line-switches/
    */
    'driver' => [
        'chrome' => [
            'options' => [
                '--disable-gpu',
                '--disable-blink-features=AutomationControlled',
                'window-size=1920,1080',
                'user-agent=Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
            ],
        ],
    ]
];
