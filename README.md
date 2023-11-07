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

$tx = (object)[ // Full transaction, containing Account, Destination, meta, ...)
    "Account": "rA...",
    "Amount": "100300000",
    "Destination": "rD....",
    "Fee": "10000",
    ...
    "meta" => [ ... ],
    ...
];

$TxHookParser = new TxHookParser($transaction->result);

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
# List of account uninstalled hooks (SetHook transaction)
$createdHooks = $TxHookParser->uninstalledHooks();
# List of account installed hooks (SetHook transaction)
$createdHooks = $TxHookParser->uninstalledHooks();
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