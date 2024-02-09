<?php declare(strict_types=1);

namespace XRPLWin\XRPLHookParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLHookParser\TxHookParser;

/***
 * SetHook
 * Position tests.
 */
final class Tx16Test extends TestCase
{
  public function testSetHookPositions()
  {
    $transaction = file_get_contents(__DIR__.'/fixtures/tx16.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);
    //dd($TxHookParser);
    # List of all hooks
    $hooks = $TxHookParser->hooks();
    $this->assertIsArray($hooks);
    $this->assertEquals([
      '4512D7BABEF201C779E76B2FEECB0D655E088426B5769F0C6796A1E97FD82D91',
    ], $hooks);
    
    # List of all accounts that are affected by hooks in transaction
    $accounts = $TxHookParser->accounts();
    $this->assertIsArray($accounts);
    $this->assertEquals([
      'rMUyMTJMNWfMY6ZAar929gGSCBZi6A45mb',
    ], $accounts);
    
    # List of newly created hooks
    $createdHooks = $TxHookParser->createdHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([], $createdHooks);
    
    # List of newly created hooks (detailed)
    $createdHooksDetailed = $TxHookParser->createdHooksDetailed();
    $this->assertIsArray($createdHooksDetailed);
    $this->assertEquals([], $createdHooksDetailed);
    
    # List of newly destroyed hooks
    $destroyedHooks = $TxHookParser->destroyedHooks();
    $this->assertIsArray($destroyedHooks);
    $this->assertEquals([], $destroyedHooks);
    
    # List of installed hooks
    $installedHooks = $TxHookParser->installedHooks();
    $this->assertIsArray($installedHooks);
    $this->assertEquals([
      '4512D7BABEF201C779E76B2FEECB0D655E088426B5769F0C6796A1E97FD82D91',
    ], $installedHooks);
    
    # List of uninstalled hooks
    $uninstalledHooks = $TxHookParser->uninstalledHooks();
    $this->assertIsArray($uninstalledHooks);
    $this->assertEquals([], $uninstalledHooks);
    
    # Uninstalled hooks stats
    $uninstalledHooksStats = $TxHookParser->uninstalledHooksStats();
    $this->assertIsArray($uninstalledHooksStats);
    $this->assertEquals([], $uninstalledHooksStats);
    
    # List of modified hooks
    # In this case no modified hooks even tho they are present in position 0
    # Both metadata are the same, this is internally unmodified hook
    $modifiedHooks = $TxHookParser->modifiedHooks();
    $this->assertIsArray($modifiedHooks);
    $this->assertEquals([], $modifiedHooks);
    
    # List of hooks by account
    # In this case this account have two hooks with of same hash, one added one unmodified
    $accountHooks = $TxHookParser->accountHooks('rMUyMTJMNWfMY6ZAar929gGSCBZi6A45mb');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([
      '4512D7BABEF201C779E76B2FEECB0D655E088426B5769F0C6796A1E97FD82D91',
      '4512D7BABEF201C779E76B2FEECB0D655E088426B5769F0C6796A1E97FD82D91',
      '4512D7BABEF201C779E76B2FEECB0D655E088426B5769F0C6796A1E97FD82D91',
    ], $accountHooks);
    # List of accounts by hook
    $hookAccounts = $TxHookParser->hookAccounts('4512D7BABEF201C779E76B2FEECB0D655E088426B5769F0C6796A1E97FD82D91');
    
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([
      'rMUyMTJMNWfMY6ZAar929gGSCBZi6A45mb',
    ], $hookAccounts);
    
    $this->assertFalse($TxHookParser->isHookCreated('4512D7BABEF201C779E76B2FEECB0D655E088426B5769F0C6796A1E97FD82D91'));
    
    # List of hooks with positions:

    //INSTALLED POSITIONS
    $installedHooksPos = $TxHookParser->installedHooksPos();
    $this->assertIsArray($installedHooksPos);
    $this->assertEquals([
      ['4512D7BABEF201C779E76B2FEECB0D655E088426B5769F0C6796A1E97FD82D91',3],
    ], $installedHooksPos);

  }
}