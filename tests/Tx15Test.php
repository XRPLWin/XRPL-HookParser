<?php declare(strict_types=1);

namespace XRPLWin\XRPLHookParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLHookParser\TxHookParser;

/***
 * SetHook
 * Previously one hook
 * Finally two hooks but they are exactly the same hash and contents.
 * This is modify (pos0) and install (pos1) action for same hook hash
 */
final class Tx15Test extends TestCase
{
  public function testSetHookExtractDoubleHookOneInstalled()
  {
    $transaction = file_get_contents(__DIR__.'/fixtures/tx15.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);

    # List of all hooks
    $hooks = $TxHookParser->hooks();
    $this->assertIsArray($hooks);
    $this->assertEquals([
      'ACD3E29170EB82FFF9F31A067566CD15F3A328F873F34A5D9644519C33D55EB7',
    ], $hooks);

    # List of all accounts that are affected by hooks in transaction
    $accounts = $TxHookParser->accounts();
    $this->assertIsArray($accounts);
    $this->assertEquals([
      'rU52Rrh1K1X7Muvx4PnpjRq8nACrfHXAy6',
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
      'ACD3E29170EB82FFF9F31A067566CD15F3A328F873F34A5D9644519C33D55EB7',
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
    $accountHooks = $TxHookParser->accountHooks('rU52Rrh1K1X7Muvx4PnpjRq8nACrfHXAy6');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([
      'ACD3E29170EB82FFF9F31A067566CD15F3A328F873F34A5D9644519C33D55EB7',
      'ACD3E29170EB82FFF9F31A067566CD15F3A328F873F34A5D9644519C33D55EB7',
    ], $accountHooks);
    
    # List of accounts by hook
    $hookAccounts = $TxHookParser->hookAccounts('ACD3E29170EB82FFF9F31A067566CD15F3A328F873F34A5D9644519C33D55EB7');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([
      'rU52Rrh1K1X7Muvx4PnpjRq8nACrfHXAy6',
    ], $hookAccounts);

    $this->assertFalse($TxHookParser->isHookCreated('ACD3E29170EB82FFF9F31A067566CD15F3A328F873F34A5D9644519C33D55EB7'));
    
    
  }
}