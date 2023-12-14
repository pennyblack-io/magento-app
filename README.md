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

If you are installing this module into your Magento shop, then you can install via composer.

You will need to add the repository into your repositories array in your `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:pennyblack-io/magento-app.git"
    }
  ]
}
```

You will then be able to install the app with:
* `composer require pennyblack/magento-app`

## Development

### Tests & Linting

We use PHPUnit for our unit tests and PHPStan, PHP CodeSniffer and PHP Mess Detector for quality checks.

* Run `composer unit-test` to run the unit test suite.
* Run `composer quality-check` to run the quality check tools.
