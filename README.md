Silex Bundle
============

This bundle provides integration for the [Silex micro-framework](http://silex.sensiolabs.org/)
in Symfony 7 applications.

Silex was deprecated in 2018 and is no longer maintained. This bundle depends on a forked
version of Silex that has been updated to work with PHP 8 and Symfony 7.

Installation
------------
```bash
$ composer require bangpound/silex-bundle
```

Usage
-----
In `config/silex` directory, create a file named `app.php` with the following contents:

```php
<?php

use PDO;
use Silex\Application;

/** @var $app Application */

// Pimple service container methods are available on the $app variable.
// For example, to register a service:
$app['db'] = fn() => new PDO('sqlite::memory:');
$app->extend('db', function (PDO $db) {
    $db->exec('CREATE TABLE foo (id INTEGER PRIMARY KEY, name TEXT)');
    return $db;
});

// Controllers and routes are supported the same way as in Silex.
$app->get('/hello/{name}', function (string $name, Application $app) {
    $pdo = $app['db'];
    $pdo->prepare('INSERT INTO foo (name) VALUES (?)')->execute([$name]);
    return 'Hello '. $name;
});
```

Then in `config/packages/silex.yaml`, set:

```yaml
silex:
  files:
    - '%kernel.project_dir%/config/silex/app.php'
```

That's it! You can now use Silex in your Symfony 7 application and pretend it's 2014 again.

Notes
-----
* While the contents of `app.php` might look like they are globally scoped, they are actually executed in the
  context of the `SilexBundle::boot` method. This means that the `$app` variable is not available in the global
  scope, and other global variables must be explicitly imported.
* Symfony services are not automatically available in the Silex application. You can pass them to Silex using a
  [service locator](https://symfony.com/doc/current/service_container/service_subscribers_locators.html#defining-a-service-locator).
  See the `services.php` file for guidance.
* Generally you do not want to use original Silex providers directly. In most scenarios, you want to use Symfony
  services instead. Create new providers that wrap Symfony services as needed.
