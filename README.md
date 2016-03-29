Composer Asset Plugin
=====================

**Composer plugin for bower/npm assets**

[![Latest Stable Version](https://poser.pugx.org/hiqdev/composer-asset-plugin/v/stable)](https://packagist.org/packages/hiqdev/composer-asset-plugin)
[![Total Downloads](https://poser.pugx.org/hiqdev/composer-asset-plugin/downloads)](https://packagist.org/packages/hiqdev/composer-asset-plugin)
[![Build Status](https://img.shields.io/travis/hiqdev/composer-asset-plugin.svg)](https://travis-ci.org/hiqdev/composer-asset-plugin)
[![Scrutinizer Code Coverage](https://img.shields.io/scrutinizer/coverage/g/hiqdev/composer-asset-plugin.svg)](https://scrutinizer-ci.com/g/hiqdev/composer-asset-plugin/)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/hiqdev/composer-asset-plugin.svg)](https://scrutinizer-ci.com/g/hiqdev/composer-asset-plugin/)
[![Dependency Status](https://www.versioneye.com/php/hiqdev:composer-asset-plugin/dev-master/badge.svg)](https://www.versioneye.com/php/hiqdev:composer-asset-plugin/dev-master)

This [Composer](https://getcomposer.org/) plugin installs [bower](http://bower.io/)
and [npm](https://npmjs.com/) dependencies using native npm and native or
[PHP bower](http://bowerphp.org/).

## Basic Usage

In your `composer.json`:

```json
"extra": {
    "bower-dependencies": {
        "jquery": "*"
    },
    "bower-devDependencies": {
        "qunit": "*"
    },
    "npm-dependencies": {
        "grunt": "0.4.*"
    }
}
```

## License

This project is released under the terms of the BSD-3-Clause [license](LICENSE).
Read more [here](http://choosealicense.com/licenses/bsd-3-clause).

Copyright © 2015-2016, HiQDev (http://hiqdev.com/)

## Acknowledgments

This package shares the idea with [koala-framework/composer-extra-assets](https://github.com/koala-framework/composer-extra-assets).
