# ![PHP Canada Post API](https://rawgit.com/thejacer87/php-canadapost-api/master/php-canadapost-api-logo.jpg "PHP Canada Post API") PHP Canada Post API

This library is aimed at wrapping the Canada Post API into a simple to use PHP Library. Currently expected to start with the Rating API and build from there. Feel free to contribute. Heavily inspired by the [PHP UPS API](https://github.com/gabrielbull/php-ups-api). Initial funding provided by [Acro Media Inc](https://www.acromedia.com/).

## Table Of Contents

1. [Requirements](#requirements)
1. [Installation](#installation)
1. [License](#license-section)

<a name="requirements"></a>
## Requirements

This library uses PHP 5.5.9+.

To use the Canada Post API, you have to [get you API info from Canada Post](https://www.canadapost.ca/cpotools/apps/drc/home). For every request,
you will have to provide the username, password, your Canada Post business number.

<a name="installation"></a>
## Installation

It is recommended that you install the PHP Canada Post API library [through composer](http://getcomposer.org/). To do so,
run the Composer command to install the latest stable version of PHP Canada Post API:

```shell
composer require thejacer87/canadapost-api
```

If not using composer, you must also include these libraries: [Guzzle](https://github.com/guzzle/guzzle), [Guzzle Promises](https://github.com/guzzle/promises), [Guzzle PSR7](https://github.com/guzzle/psr7), [PHP-Fig PSR Log](https://github.com/php-fig/log), and [PHP-Fig HTTP Message](https://github.com/php-fig/http-message).


<a name="license-section"></a>
## License

PHP Canada Post API is licensed under [The MIT License (MIT)](LICENSE).
