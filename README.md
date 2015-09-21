[Propel2](http://propelorm.org) behavior that makes model classes virtually extend a [Eloquent](http://laravel.com/docs/5.1/eloquent) model class

Installation
============

Add `MarcosHoo\PropelEloquent` as a requirement to composer.json:

```javascript
{
    "require": {
        "marcoshoo/propel-eloquent": "dev-master"
    }
}
```

Update your packages with `composer update` or install with `composer install`.

Configuration
=============

Add the ``eloquent`` behavior in schema.xml:

```XML
    .
    .
    .
    <behavior name="eloquent" />
  </table>
```

Regenerate your propel models:

```sh
php artisan propel:model:build
```