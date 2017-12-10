# Scrutiny

Scrutiny helps your Laravel 5.1+ project ensure that its current server environment is configured and running as planned.

## Problem

Have you ever been in the situation where you've moved servers and forgotten to:

1. Get your queue running?
2. Add the cron job to run your schedule?
3. Install an obscure program that your reporting uses once a month?
4. Enable a PHP extension that you need for an API?

This is the scenario Scrutiny was built to address – use the availability monitor you have setup 
(like pingdom) to also monitor other important aspects of your environment.

This means your availability monitor notifies you of any problems with your server environment setup
instead of waiting for your clients or customers to tell you something is wrong.

## Installation

To install through composer, add the following to your `composer.json` file:

```json
{
    "require": {
        "brightmachine/scrutiny": "~1.0"
    }
}
```

And then run `composer install` from the terminal.

### Quick Installation

The installation instructions can be simplified using the following:

    composer require "brightmachine/scrutiny=~1.0"

### Add the Service Provider

Open `config/app.php` and the scrutiny service provider to :

```php
<?php
return [
    // …

    'providers' => [
        // …
        Scrutiny\ServiceProvider::class,
    ],
	
	// …
];
```

You are all setup – next step it to add your probes!

## How it works

1. In `AppServiceProvider::boot()`, configure the probes to check for all the things your environment needs in order to run 
2. Set up an `uptime check` in Pingdom to alert you if any of the probes fail to pass 

----

## How to configure the different probes

```php
<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider 
{
    public function boot()
    {
        // …
        $this->configureScrutinyProbes();
    }
    
    public function register()
    {
    }
    
    protected function configureScrutinyProbes()
    {
        \Scrutiny\ProbeManager::configure()
            ->connectsToDatabase()
            ->executableIsInstalled('composer.phar')
            ->queueIsRunning(30, 'high')
            ->queueIsRunning(60, 'low')
            ;
    }
}

```

----

## What probes are available

All probes are added through `\Scrutiny\ProbeManager` and calls can be chained:

```php
<?php
\Scrutiny\ProbeManager::configure()->scheduleIsRunning()->queueIsRunning();
```

The following probes are available via `\Scrutiny\ProbeManager::configure()`:

### Available Disk Space

Ensure that you always have space available.

It works by finding the disk related to a given folder and checking its usage. 

```php
public availableDiskSpace( number $minPercentage [, string $diskFolder = null ] ): \Scrutiny\ProbeManager
```

- `$minPercentage` is the minimum amount of disk space that should be available 
- `$diskFolder` the folder used to find the disk. Defaults to the disk storing your laravel app.

### Callback

If your use-case isn't supported out-of-the-box you can write your own custom probe.

When a probe is checked, 3 outcomes are possible:

1. **Skipped** – if a `\Scrutiny\ProbeSkippedException` exception is thrown
2. **Failed** – if any other exception is thrown
3. **Passed** – if no exception is thrown    

```php
public callback( string $probeName , callable $callback ): \Scrutiny\ProbeManager
```

- `$probeName` the name of the probe used to report the results of the check 
- `$callback` the callback that runs your custom check 

### Connects to Database

Check that you're able to connect to one of your databases configured on `config/database.php`. 

```php
public connectsToDatabase([ string $connectionName = null ]): \Scrutiny\ProbeManager
```

- `$connectionName` is the name of your database connection from `config/database.php`

### Connects To Http(s)

This probe checks that a given URL will return a 2xx response.

_NB: Redirects will not be followed – only the first response will be considered._ 

```php
public connectsToHttp( string $url [, array $params = array(), string $verb = 'GET' ] ): \Scrutiny\ProbeManager
```

- `$url` the URL to check, which can contain a username and password, e.g. `https://user@pass:example.com` 
- `$params` an array of URL parameters to add to the request
- `$verb` either `GET` or `POST`


### Executable is Installed

This probe will search your path, and your current `vendor/bin` looking for a particular executable. 

```php
public executableIsInstalled( string $executableName ): \Scrutiny\ProbeManager
```

- `$executableName` the name of the executable to find 

### PHP Extension is Loaded

Check that a particular PHP extension is loaded.

```php
public phpExtensionLoaded( string $extensionName ): \Scrutiny\ProbeManager
```

- `$extensionName` the name of the PHP extension to check 

### Queue is Running

This probe checks that your laravel queue is running.

```php
public queueIsRunning( [ int $maxHandleTime = 300, $queue = null, $connection = null ] ): \Scrutiny\ProbeManager
```

- `$maxHandleTime` the maximum time in seconds that you give a job to run on the given queue 
- `$queue` if you run multiple queues on the same connection, this is the name of the queue to check
- `$connection` if you run multiple connections, this is the one to check as configured in `config/queue.php`

### Schedule is Running

Make sure that the artisan schedule is being run. 

```php
public scheduleIsRunning(): \Scrutiny\ProbeManager
```

----

## How to configure pingdom

Configure a new check in pingdom with the following setting:

1. Add an `uptime check` in pingdom to hit `https://yourdomain.com/~scrutiny/check-probes` where yourdomain.com is your production domain
2. Scrutiny will return an HTTP status of `590 Some Tests Failed` when something is awry – this is a custom code 


----

## Contributing

Any contribution is received with humility and gratitude.

Thank you if you're considering contributing an improvement to this project.

**Process**:

1. Fork, change, create pull-request
2. Tell us why/how your PR will benefit the project 
3. We may ask you for clarification, but we'll quickly let you know whether or not it's likely your change will be merged

😘 Xx