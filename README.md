# Warcry.ru

Built on Plasticode (kapxapot/plasticode).

Live: https://warcry.ru

## Installation

See [Plasticode Boilerplate](https://github.com/kapxapot/plasticode-boilerplate) README.

## Plasticode updates

If the project is configured to work with the latest version of **Plasticode**, it updates it from GitHub, not from Packagist. In order to update the local library from GitHub run this command in the project's root folder:

`composer update kapxapot/plasticode`

That will update the `composer.lock` file that should be committed.

## DB migrations

In the process of development there can be new DB migrations. To execute them run the following in the projects' root folder:

`vendor\bin\phinx migrate`
