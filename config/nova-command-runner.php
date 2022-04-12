<?php

return [
    'commands' => [
        // Basic command
        'Clear Cache' => [ 'run' => 'cache:clear', 'type' => 'primary', 'group' => 'Cache', ],
        // Bash command
        'Disk Usage' => [ 'run' => 'df -h', 'type' => 'danger', 'group' => 'Statistics', 'command_type' => 'bash' ],
        // Command with variable
        'Forget Cache' => [ 'run' => 'cache:forget {cache key}', 'type' => 'warning', 'group' => 'Cache' ],
        // Command with advanced variable customization
        'Forget Cache variable' => [
            'run' => 'cache:forget {cache key}',
            'type' => 'success',
            'group' => 'Cache',
            'variables' => [
                [
                    'label' =>  'cache key', // This needs to match with variable defined in the command
                    'field' => 'select', // Allowed values (text,number,tel,select,date,email,password)
                    'options' => [
                        'blog-cache' => 'Clear Blog Cache',
                        'app-cache' => 'Clear Application Cache'
                    ],
                    'placeholder' => 'Select An Option'
                ]
            ]
        ],
        // Command with flags
        'Run Migrations' => [ 'run' => 'migrate --force', 'type' => 'info', 'group' => 'Migration' ],
        // Command with optional flags
        'Run Migrations(Optional)' => [
            'run' => 'migrate',
            'type' => 'danger',
            'group' => 'Migration',
            'flags' => [
                // These optional flags will be prompted as a checkbox for the user
                // And will be appended to the command if the user checks the checkbox
                '--force' => 'Force running in production'
            ]
        ],
        // Command with help text
        'Run Migrations(Help)' => [
            'run' => 'migrate --force',
            'type' => 'danger',
            'group' => 'Migration',
            // You can also add html for help text.
            'help' => 'This is a destructive operation. Proceed only if you really know what you are doing.'
        ],

        // Queueing commands
        'Clear Cache' => [ 'run' => 'cache:clear --should-queue', 'type' => 'danger', 'group' => 'Cache' ],

        // Queueing commands with custom connection and queue
        'Clear Cache' => [ 'run' => 'cache:clear --should-queue --cr-connection=database --cr-queue=high', 'type' => 'danger', 'group' => 'Cache' ],
    ],

    // Limit the command run history to latest 10 runs
    'history'  => 10,
    // Tool name displayed in the navigation menu
    'navigation_label' => 'Command Runner',
    // Any additional info to display on the tool page. Can contain string and html.
    'help' => '',
    // Allow running of custom artisan and bash(shell) commands
    'custom_commands' => ['artisan','bash'],

    // Allow running of custom artisan commands only(disable custom bash(shell) commands)
//    'custom_commands' => ['artisan'],
    // Allow running of custom bash(shell) commands only(disable custom artisan commands)
//    'custom_commands' => ['bash'],
    // Disable running of custom commands.
//    'custom_commands' => [],
];
