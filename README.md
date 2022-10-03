# Laravel Nova tool for running Artisan & Shell commands

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stepanenko3/nova-command-runner.svg?style=flat-square)](https://packagist.org/packages/stepanenko3/nova-command-runner)
[![Total Downloads](https://img.shields.io/packagist/dt/stepanenko3/nova-command-runner.svg?style=flat-square)](https://packagist.org/packages/stepanenko3/nova-command-runner)
[![License](https://poser.pugx.org/stepanenko3/nova-command-runner/license)](https://packagist.org/packages/stepanenko3/nova-command-runner)

![screenshot of the command runner tool](screenshots/tool.png)

## Description

This [Nova](https://nova.laravel.com) tool lets you run artisan and bash commands directly from nova.

> This is an extended version of the original package [Nova Command Runner](https://github.com/guratr/nova-command-runner) by [guratr](https://github.com/guratr)

## Requirements

- `php: >=8.0`
- `laravel/nova: ^4.0`

## Features

- ⌨️ Run predefined artisan and shell commands
- 📝 Run custom artisan and shell commands
- 📄 Use variables while running commands
- 🙋‍♂️ Prompt the user to specify optional flags while running commands
- 📄 Use predefined values for variables using a select box or prompt the user to enter a value for the variable.
- 📖 Keep track of command run history
- 💾 No database changes required. Everything is managed from a single config file.
- 🕔 Queue long running commands
- ✅ Support command progressbar
- 📱 Responsive
- 🕶 Dark mode

## Installation

You can install the nova tool in to a Laravel app that uses [Nova](https://nova.laravel.com) via composer:

```bash
composer require stepanenko3/nova-command-runner
```

Next up, you must register the tool with Nova. This is typically done in the `tools` method of the `NovaServiceProvider`.

```php
// in app/Providers/NovaServiceProvder.php

// ...

public function tools()
{
    return [
        // ...
        new \Stepanenko3\NovaCommandRunner\CommandRunnerTool,
    ];
}
```

Publish the config file:

``` bash
php artisan vendor:publish --provider="Stepanenko3\NovaCommandRunner\ToolServiceProvider"
```

Add your commands to `config/nova-command-runner.php`

## Usage

Click on the `"Command Runner"` menu item in your Nova app to see the tool.

## Configuration

All the configuration is managed from a single configuration file located in `config/nova-command-runner.php`

### Adding Commands

All the commands which needs to be easily accessible should be defined in the `commands` array
in the configuration file.

#### Command Options

- run : command to run (E.g. route:cache)
- type : button class (primary, secondary, success, danger, warning, info, light, dark, link)
- group: Group name (optional)
- variables : Array of variables used in the command(optional)
- command_type : Type of the command.(artisan or bash. Default artisan)
- flags : Array of optional flags for the command(optional)
- output_size: crops output of the given command to the specified number of lines
- timeout: updates the timeout limit for the given queued command

#### Examples

```php
'commands' => [

    // Basic command
    'Clear Cache' => [
        'run' => 'cache:clear', 
        'type' => 'danger', 
        'group' => 'Cache',
    ],
     
    // Bash command
    'Disk Usage' => [
        'run' => 'df -h', 
        'type' => 'danger', 
        'group' => 'Statistics',
        'command_type' => 'bash'
    ],

    // Command with variable   
    'Clear Cache' => [
        'run' => 'cache:forget {cache key}', 
        'type' => 'danger', 
        'group' => 'Cache'
    ],

    // Command with advanced variable customization
    'Clear Cache' => [
        'run' => 'cache:forget {cache key}', 
        'type' => 'danger', 
        'group' => 'Cache',
        'variables' => [
            [
                'label' =>  'cache key' // This needs to match with variable defined in the command,
                'field' => 'select' // Allowed values (text,number,tel,select,date,email,password),
                'options' => [
                    'blog-cache' => 'Clear Blog Cache', 
                    'app-cache' => 'Clear Application Cache'
                ],
                'placeholder' => 'Select An Option'
            ]
        ]
    ],

    // Command with flags
    'Run Migrations' => [
        'run' => 'migrate --force', 
        'type' => 'danger', 
        'group' => 'Migration',
    ],

    // Command with optional flags
    'Run Migrations' => [
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
    'Run Migrations' => [
        'run' => 'migrate --force', 
        'type' => 'danger', 
        'group' => 'Migration',

        // You can also add html for help text.
        'help' => 'This is a destructive operation. Proceed only if you really know what you are doing.'
    ],

    // Queueing commands
    'Clear Cache' => [
        'run' => 'cache:clear --should-queue',
        'type' => 'danger',
        'group' => 'Cache',
    ],

    // Queueing commands on custom queue and connection
    'Clear Cache' => [
        'run' => 'cache:clear --should-queue --cr-queue=high --cr-connection=database',
        'type' => 'danger',
        'group' => 'Cache',
    ],
],
```

### Command with Progress bar

Create Artisan command with progress bar. For example:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stepanenko3\NovaCommandRunner\Console\HasNovaProgressBar;

class LongCommand extends Command
{
    use HasNovaProgressBar;

    protected $signature = 'command:long';

    public function handle()
    {
        $bar = $this->createProgressBar(10);

        foreach (range(1, 10) as $i) {
            sleep(1);

            $bar->advance();
        }

        $bar->finish();

        return Command::SUCCESS;
    }
}
```

Add your command to config commands list

```php
'commands' => [
    ...

    // Queueing long-running commands with custom timeout limit and output size
    'Long Command' => [ 
        'run' => 'command:long --should-queue',  // '--should-queue' is a required option to display the progressbar

        'type' => 'primary', 
        'group' => 'Application', 
        'timeout' => 120, // Updates the timeout limit for the given queued command
        'output_size' => 3, // The number of last lines to be displayed in the output
    ],
]
```

### Other Customizations

```php
// Limit the command run history to latest 10 runs
'history'  => 10,

// Tool name displayed in the navigation menu
'navigation_label' => 'Command Runner',

// Any additional info to display on the tool page. Can contain string and html.
'help' => '',

// Groups whose commands should not be running simultaneously, ['*'] to apply this globally to all commands
'unique_command_groups' => [],

// Allow running of custom artisan and bash(shell) commands
'custom_commands' => ['artisan','bash'],

// Allow running of custom artisan commands only(disable custom bash(shell) commands)
'custom_commands' => ['artisan'],

// Allow running of custom bash(shell) commands only(disable custom artisan commands)
'custom_commands' => ['bash'],

// Disable running of custom commands.
'custom_commands' => [],

'without_overlapping' => [
    // Blocks running commands simultaneously under the given groups. Use '*' for block all groups
    'groups' => [],

    // Blocks running commands simultaneously. Use '*' for block all groups
    'commands' => [],
],
```

### Screenshots

![screenshot of the command runner tool](screenshots/tool.png)

![screenshot of the command runner tool](screenshots/dark-mode.png)

![screenshot of the command runner tool](screenshots/mobile.png)

![screenshot of the command runner tool](screenshots/confirmation.png)

![screenshot of the command runner tool](screenshots/variable-input.png)

![screenshot of the command runner tool](screenshots/select-variable.png)

![screenshot of the command runner tool](screenshots/command-with-optional-flag.png)

## Credits

- [Artem Stepanenko](https://github.com/stepanenko3)
- [Srinath Reddy Dudi](https://github.com/srinathreddydudi)
- [guratr](https://github.com/guratr)

## Contributing

Thank you for considering contributing to this package! Please create a pull request with your contributions with detailed explanation of the changes you are proposing.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).
