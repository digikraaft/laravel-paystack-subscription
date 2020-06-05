# Introduction
A simple interface to [Paystack's](https://paystack.com/>) subscription billing services. It takes off the pain of implementing subscription management yourself.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/digikraaft/laravel-paystack-subscription.svg?style=flat-square)](https://packagist.org/packages/digikraaft/laravel-paystack-subscription)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/digikraaft/laravel-paystack-subscription/run-tests?label=tests)](https://github.com/digikraaft/laravel-paystack-subscription/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/digikraaft/laravel-paystack-subscription.svg?style=flat-square)](https://packagist.org/packages/digikraaft/laravel-paystack-subscription)


This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.


## Installation

You can install the package via composer:

```bash
composer require digikraaft/laravel-paystack-subscription
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Digikraaft\Skeleton\SkeletonServiceProvider" --tag="migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Digikraaft\Skeleton\SkeletonServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

``` php
$skeleton = new Digikraaft\Skeleton();
echo $skeleton->echoPhrase('Hello, Digikraaft!');
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email dev@digitalkraaft.com instead of using the issue tracker.

## Credits

- [Tim Oladoyinbo](https://github.com/timoladoyino)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
