# About PortaOne ESPF event handling library with PSR-14 dispatch

## Purpose

This package intended to simplify PortaBilling event handling. Build for composer with PSR-4 autoload and follow PSR-14 Event Dispatcher architecture. It takes PSR-7 Request object as input to create Event, therefore may be integrated to any PSR-based framework. It seems PSR-11 (container) - friendly and a good for autowire, but does not support PSR-11 directly.

It is decoupled from all depenencies, requiring standart PSR interfaces instead of exact implementations. It uses:
- [PSR-7](https://www.php-fig.org/psr/psr-7) standard object Request used
- [PSR-14](https://www.php-fig.org/psr/psr-14) Event Dispatcher, provides specific Event, and ListenerProvider classes, and also a very generic Dispatcher implementation to avoide useless dependencies

## Usage
Try to read and setup [example](https://github.com/portabilling/psr14-events/tree/master/example). More rich docs coming soon!

Please, reference to [PortaBilling supported event types](https://docs.portaone.com/docs/mr105-provisioning-via-webhooks?topic=supported-event-types) for event type names and variables for each type.

## Installation
In the Composer storage. Just add proper require section:
```
    "require": {
        "porta/psr14-events": "^0.1"
    }
```
Please, review the [changelog](https://github.com/portabilling/psr14-events/blob/master/CHANGELOG.MD) before to change used version.

## Dependencies
Composer dependencies:
- php: ^7.4|^8.0|^8.1|^8.2|^8.3
- psr/event-dispatcher: ^1.0.0
- psr/http-message: ^2.0

## Testing
Tested with PHPUnit 9.6 on php 7.4 to 8.3, 100% coverage, which does not mean everything will work as intended.

Current testing and compatibility status check on [Github flows](https://github.com/portabilling/psr14-events/actions/workflows/Compatibility.yml)

To test, install in dev mode and use `composer test` command from library root for run all standard modular tests

Manualy tested and used with PortaBilling release MR100.