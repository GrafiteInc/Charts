![Grafite Charts](GrafiteCharts-banner.png)

**Charts** - A handy little ChartJS package for Laravel apps.

[![PHP Package Tests](https://github.com/GrafiteInc/Charts/workflows/PHP%20Package%20Tests/badge.svg)](https://github.com/grafiteinc/charts/actions)
[![Maintainability](https://api.codeclimate.com/v1/badges/1f78223f1c3e16b59229/maintainability)](https://codeclimate.com/github/GrafiteInc/Charts/maintainability)
[![Packagist](https://img.shields.io/packagist/dt/grafite/charts.svg)](https://packagist.org/packages/grafite/charts)
[![license](https://img.shields.io/github/license/mashape/apistatus.svg)](https://packagist.org/packages/grafite/charts)

The Grafite Charts package is a tool for integrating ChartJS charts into a Laravel application. It's based off `consoletvs/charts` version 6.x.

##### Author(s):
* [Matt Lantz](https://github.com/mlantz) ([@mattylantz](http://twitter.com/mattylantz), mattlantz at gmail dot com)

## Requirements

1. PHP 7.3+|8.0+

## Compatibility and Support

| Laravel Version | Package Tag | Supported |
|-----------------|-------------|-----------|
| ^7.x - ^11.x | 2.x | yes |
| ^7.x - ^8.x | 1.x | no |

## Built From consoletvs/charts and Why

Èrik Campobadal Forés created an awesome package for Laravel charts. Version 7.0 appeared to switch to a whole new chart library and it appears he will be primarily maintaining the 7.x branch. This is inspired by his 6.x version of the package and makes various adjustments including reducing it to only ChartJS support.

### Installation

Start a new Laravel project:
```php
composer create-project laravel/laravel your-project-name
```

Then run the following to add Grafite Charts
```php
composer require "grafite/charts"
```

## Documentation

[https://docs.grafite.ca/utilities/charts](https://docs.grafite.ca/utilities/charts)

## License
Grafite Charts is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

### Bug Reporting and Feature Requests
Please add as many details as possible regarding submission of issues and feature requests

### Disclaimer
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
