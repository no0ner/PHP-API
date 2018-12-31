# Paladins API (PHP)
## About
This PHP package came from the need to have a well documented, functional package to help communicate with the Paladins developer API. This package is built using Laravel 5 components and has not been tested outside of that environment.

## Installation
```sh
$ composer require paladinsdev/php-api
```

## Usage
There are two ways of using the class, however only 1 recommended. The singleton method is used on [Paladins Ninja](https://paladins.ninja) and handles hundreds of thousands a matches a day.

### Recommended (Singleton)
```php
<?php

use PaladinsDev\PHP\PaladinsAPI;

class YourClass
{
  public function getSomePlayer()
  {
    $playerDetails = PaladinsAPI::getInstance('YourDevId', 'YourDevAuthKey')->getPlayer('SomePlayer');
  }
}
```

#### Using This Method (Laravel)
Laravel makes it very easy to use singletons using the `resolve()` helper. Open up the `app.php` file in the `bootstrap` folder at the root of your project directory and add the following piece of code.

```php
$app->singleton('PaladinsAPI', function($app) {
  return PaladinsDev\PHP\PaladinsAPI::getInstance(env('PALADINS_DEVID'), env('PALADINS_AUTHKEY'));
});
```
This assumes you have two environment variables `PALADINS_DEVID` and `PALADINS_AUTHKEY`. You can edit this however you like. You use it by calling `resolve('PaladinsAPI')` and then use it just like a normal instance of a class.

### Not Recommended
*This is not recommended as Hi-Rez / Evil Mojo only allows so many sessions a day and the more sessions you create, the quicker you'll get to reaching that limit. Caching **should** be able to handle this method...but it's still not recommended.*

```php
<?php

use PaladinsDev\PHP\PaladinsAPI;

class YourClass
{
  private $api;
  
  public function __construct()
  {
    $this->api = new PaladinsAPI('YourDevId', 'YourDevAuthKey');
  }
  
  public function getSomePlayer()
  {
    $playerDetails = $this->api->getPlayer('SomePlayer');
  }
}
```
