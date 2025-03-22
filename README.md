# StanScript

A WIP PHPStan to TypeScript transformer.

## Status

🍳 **Still cooking!**

Expect everything to break.

## Basic dev setup

1. Clone the repository.
2. swap `composer-dev.json` with `composer.json`.
3. `composer install`
4. swap `composer.json` with `composer-dev.json` back.

In your test project, you can install a local version of StanScript by adding:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "/path/to/stanscript"
        }
    ],
}
```

to your `composer.json` file and then running `composer require djfhe/stanscript @dev --dev`.

To run this phpstan extension you need to add it to your phpstan.neon file. This should be a standalone config file, something like `phpstan-extractor.neon` and only contain a basic configuration.
You can add additional extensions providing type informations. There is no need to add a phpstan level, since we are only interested in phpstans analyses and not its rules.

For example the phpstan extension in my test (laravel) project looks like this:

```neon
includes:
    - ./vendor/larastan/larastan/extension.neon
    - ./vendor/djfhe/stanscript/phpstan-extractor.neon

parameters:
    tmpDir: .cache/phpstan-extractor

    paths:
        - ./app
        - ./config
        - ./bootstrap
        - ./database
        - ./routes

    excludePaths:
        - ./bootstrap/cache
```

You can then run phpstan with the following command:

```bash
vendor/bin/phpstan analyse -c phpstan-extractor.neon
```

and pipe the output into a typescript declaration file:

```bash
vendor/bin/phpstan analyse -c phpstan-extractor.neon > types.d.ts
```
