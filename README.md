HTTP
===========

HTTP Header wrapper.

## Purpose

The objective of this library is to provide a test-ready alternative to PHP's default `header()` interface. Instead of sending headers directly, you can add them to PHP using this library. Additionally, you can easily mock this library in your own projects to fully decouple code from PHP primitives.

## Installation

This module can be easily installed by adding `10up/http` to your `composer.json` file. Then, either autoload your Composer dependencies or manually `include()` the `HTTP.php` bootstrap file.

## Use

Rather than invoking PHP's `header()` method directly, simply call `\TenUp\HTTP\v1_0_0\add()` to add new headers. This allows you to specify a key, a value, and flag whether or not to overwrite existing values.

All headers loaded into the system will be sent to the browser when WordPress invokes it's usual `send_headers` action; there is no other work you need to do on your own.

## Changelog

### 1.0

- First public release