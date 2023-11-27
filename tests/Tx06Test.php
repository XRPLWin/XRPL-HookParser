<?php declare(strict_types=1);

namespace XRPLWin\XRPLHookParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLHookParser\TxHookParser;

/***
 * SetHook - hook delete, Hook object deleted
 */
final class Tx06Test extends TestCase
{
  public function testSetHookUpdate()
  {
    $transaction = file_get_contents(__DIR__.'/fixtures/tx06.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);

    # List of all hooks
    
    $hooks = $TxHookParser->hooks();
    $this->assertIsArray($hooks);
    $this->assertEquals([
      'ACD3E29170EB82FFF9F31A067566CD15F3A328F873F34A5D9644519C33D55EB7',
    ], $hooks);
    
    # List of newly created hooks (none here)
    $createdHooks = $TxHookParser->createdHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([], $createdHooks);

    # List of uninstalled hooks
    $createdHooks = $TxHookParser->uninstalledHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([
      'ACD3E29170EB82FFF9F31A067566CD15F3A328F873F34A5D9644519C33D55EB7'
    ], $createdHooks);

    # List of installed hooks
    $createdHooks = $TxHookParser->installedHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([], $createdHooks);

    # List of destroyed hooks
    $destroyedHooks = $TxHookParser->destroyedHooks();
    $this->assertIsArray($destroyedHooks);
    $this->assertEquals([], $destroyedHooks);

    # List of all accounts
    $accounts = $TxHookParser->accounts();
    $this->assertIsArray($accounts);
    $this->assertEquals([
      'rUXeVSNiRKGawHM9x73EmdF25HbZAH8U78',
    ], $accounts);
    
    # Specific account
    $accountHooks = $TxHookParser->accountHooks('rUXeVSNiRKGawHM9x73EmdF25HbZAH8U78');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([
      'ACD3E29170EB82FFF9F31A067566CD15F3A328F873F34A5D9644519C33D55EB7'
    ], $accountHooks);
    
    # Unrelated account
    $accountHooks = $TxHookParser->accountHooks('rQJecEU8BT5NmQvdjEtHRwTC2XLwxgkCpE');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([], $accountHooks);
    
    
    # Specific hook
    $hookAccounts = $TxHookParser->hookAccounts('ACD3E29170EB82FFF9F31A067566CD15F3A328F873F34A5D9644519C33D55EB7');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([
      'rUXeVSNiRKGawHM9x73EmdF25HbZAH8U78'
    ], $hookAccounts);
    
    # Unrelated hook
    $hookAccounts = $TxHookParser->hookAccounts('5EDF6439C47C423EAC99C1061EE2A0CE6A24A58C8E8A66E4B3AF91D76772DC77');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([], $hookAccounts);
  }
}