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
# List of hooks by account*
$accountHooks = $TxHookParser->accountHooks('raddress...');
# List of accounts by hook
$hookAccounts = $TxHookParser->hookAccounts('5EDF6...2DC77');

# List of newly created hooks (new HookDefinition created)
$createdHooks = $TxHookParser->createdHooks();
# List of newly created hooks - detailed
$createdHooksDetails = $TxHookParser->createdHooksDetailed();
# Array key-value parameters contained in created hook
$hookInitialParams = TxHookParser::toParams($createdHooksDetails['5EDF6...2DC77']);
# Check if specific hook is created
$isCreated = $TxHookParser->isHookCreated('5EDF6...2DC77');

# List of destroyed hooks (HookDefinition deleted)
$destroyedHooks = $TxHookParser->destroyedHooks();
# Check if specific hook is destroyed
$isDestroyed = $TxHookParser->isHookDestroyed('5EDF6...2DC77');

# List of uninstalled hooks* (eg. SetHook transaction)
$uninstalledHooks = $TxHookParser->uninstalledHooks();
# List of uninstalled hooks with positions*
$uninstalledHooksPos = $TxHookParser->uninstalledHooksPos();
# List of uninstalled hooks with num uninstalls
$uninstalledHooksStats = $TxHookParser->uninstalledHooksStats();
# List of installed hooks* (eg. SetHook transaction)
$installedHooks = $TxHookParser->installedHooks();
# List of installed hooks with positions*
$installedHooksPos = $TxHookParser->installedHooksPos();
# List of installed hooks with num installs
$installedHooksStats = $TxHookParser->installedHooksStats();
# List of modified hooks*
$modifiedHooks = $TxHookParser->modifiedHooks();
# List of modified hooks with positions*
$modifiedHooksPos = $TxHookParser->modifiedHooksPos();
# List of unmodified hooks*
$unmodifiedHooks = $TxHookParser->unmodifiedHooks();
# List of unmodified hooks with positions*
$unmodifiedHooksPos = $TxHookParser->unmodifiedHooksPos();
# Manual data lookup (lookup any combination of mapped data)
$lookup = $TxHookParser->lookup('raddress...','Hook','installed');


# * Methods that can return multiple same hook hashes, for example if
#   account has same hook on two or more positions installed, it is 
#   important to differentiate hook "instance" on account vs hook 
#   create and destroy actions. List of hooks always returns unique hashes.
```

### HookOn/HookCanEmit field
See https://richardah.github.io/xrpl-hookon-calculator/ for reference.

Decode HookOn or HookCanEmit String
```PHP
use XRPLWin\XRPLHookParser\HookOn;

$triggered = HookOn::decode('0xfffffffffffffffffffffffffffffffffffffff7fffffffffffc1fffffc00a40'); //array
/*
$triggered = array:26 [
  0 => "ttPAYMENT"
  1 => "ttESCROW_CREATE"
  ...
]
*/
//No triggers in this sample:
HookOn::decode('0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffffbfffff');
//Works without prefix:
HookOn::decode('ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffbfffff');
//Works with uppercase and lowercase:
HookOn::decode('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFBFFFFF');
HookOn::decode('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFBFFFFF');
HookOn::decode('0XFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFBFFFFF');
```

Encode HookOn or HookCanEmit String
```PHP
use XRPLWin\XRPLHookParser\HookOn;

HookOn::encode([HookOn::ttACCOUNT_DELETE]);
//= (string)'0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffff9fffff'

HookOn::encode([]);
//= (string)'0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffffbfffff'

HookOn::encode([HookOn::ttACCOUNT_DELETE,HookOn::ttACCOUNT_SET]);
//= (string)'0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffff9ffff7'
```

## What is new in version 2.x
Version 2.x brings overhaul of hook detection code and now
positions are tracked, which in return allows new methods
listed below. For each installed, modified and uninstalled
hook on account it is possible to get position in which change
was occurred. Modified Hooks method now does not return hooks
which was reinstalled but contents are not changed (Flag=0 is normalized),
those are now available in `unmodifiedHooks` and `unmodifiedHooksPos` methods.

- Added `uninstalledHooksPos()` method
- Added `installedHooksPos()` method
- Changed `modifiedHooks()` method
- Added `modifiedHooksPos()` method
- Added `unmodifiedHooks()` method
- Added `unmodifiedHooksPos()` method

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
