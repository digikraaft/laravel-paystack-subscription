# Introduction
A simple, fluent interface to [Paystack's](https://paystack.com/>) subscription billing services. It takes the pain of implementing subscription management off you.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)


This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.


## Installation

You can install the package via composer:

```bash
composer require digikraaft/laravel-paystack-subscription
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Digikraaft\Paystack\PaystackSubscritpionServiceProvider" --tag="migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Digikraaft\PaystackSubscritpion\PaystackSubscritpionServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

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

- [Tim Oladoyinbo](https://github.com/timoladoyinbo)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
