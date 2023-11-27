![main workflow](https://github.com/XRPLWin/XRPL-HookParser/actions/workflows/main.yml/badge.svg)
[![GitHub license](https://img.shields.io/github/license/XRPLWin/XRPL-HookParser)](https://github.com/XRPLWin/XRPL-HookParser/blob/main/LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/xrplwin/xrpl-hookparser.svg?style=flat)](https://packagist.org/packages/xrplwin/xrpl-hookparser)

# HookParser
Transaction Hook parser for hook-enabled XRPL/Xahau networks.

This package takes single transaction, parses metadata and provides info about HookHash-es and accounts.


## Requirements
- PHP 8.1 or higher
- [Composer](https://getcomposer.org/)

## Installation
```
composer require xrplwin/xrpl-hookparser
```

## Usage

### Transaction parser

```PHP
use XRPLWin\XRPLHookParser\TxHookParser;

$tx = (object)[ // Full transaction, containing Account, Destination, meta, ...
    "Account": "rA...",
    "Amount": "100300000",
    "Destination": "rD....",
    "Fee": "10000",
    ...
    "meta" => [ ... ],
    ...
];

$TxHookParser = new TxHookParser($tx);

// All examples below return array:

# List of all hooks in transaction
$hooks = $TxHookParser->hooks();
# List of all accounts that are affected by hooks in transaction
$accounts = $TxHookParser->accounts();
# List of hooks by account
$accountHooks = $TxHookParser->accountHooks('raddress...');
# List of accounts by hook
$hookAccounts = $TxHookParser->hookAccounts('5EDF6...2DC77');
# List of newly created hooks (new HookDefinition created)
$createdHooks = $TxHookParser->createdHooks();
# List of destroyed hooks (HookDefinition deleted)
$createdHooks = $TxHookParser->destroyedHooks();
# List of account uninstalled hooks (eg. SetHook transaction)
$uninstalledHooks = $TxHookParser->uninstalledHooks();
# List of account installed hooks (eg. SetHook transaction)
$installedHooks = $TxHookParser->installedHooks();
```

## Running tests
Run all tests in "tests" directory.
```
composer test
```
or
```
./vendor/bin/phpunit --testdox
```

## References

https://docs.xahau.network/
