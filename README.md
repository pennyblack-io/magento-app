# Pennyblack Magento app

This app has been developed against the 2.4.x version of [Magento Open Source](https://github.com/magento/magento2).

## Prerequisites

* PHP >=7.4
* Composer

## Installation

### Module development

* Create an `auth.json` file in the root and configure it with the keys found in the `Magento Account` Lastpass entry.
* `composer install`
* If you are using Guzzle 6.x then you will need some additional packages.
  1. You can check your version of Guzzle using `composer info guzzlehttp/guzzle | grep version`
  2. If this version is 6.x then run `composer require php-http/guzzle6-adapter guzzlehttp/psr7`

### Magento

If you are installing this module into your Magento shop, then you can install via composer.
* `composer require pennyblack/magento-app`

## Development

### Tests & Linting

We use PHPUnit for our unit tests and PHPStan, PHP CodeSniffer and PHP Mess Detector for quality checks.

* Run `composer unit-test` to run the unit test suite.
* Run `composer quality-check` to run the quality check tools.
