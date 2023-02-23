# Pennyblack Magento app

This app has been developed against the 2.4.x version of [Magento Open Source](https://github.com/magento/magento2).

## Prerequisites

* PHP >=7.4
* Composer

## Installation

### Module development

* Create an `auth.json` file in the root and configure it with the keys found in the `Magento Account` Lastpass entry.
* `composer install`

### Magento

If you are installing this module into your Magento shop, then you can install via composer:

* `composer require pennyblack/pennyblack`

## Development

### Tests & Linting

We use PHPStan, PHP CodeSniffer and PHP Mess Detector for quality checks.

* Run `composer quality-check` to run the quality check tools.