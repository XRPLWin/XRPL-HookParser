<?php declare(strict_types=1);

namespace XRPLWin\XRPLHookParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLHookParser\TxHookParser;

/***
 * SetHook - clear namespace
 */
final class Tx07Test extends TestCase
{
  public function testSetHookNamespaceClear()
  {
    $transaction = file_get_contents(__DIR__.'/fixtures/tx07.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);

    # List of all hooks
    
    $hooks = $TxHookParser->hooks();
    $this->assertIsArray($hooks);
    $this->assertEquals([
      'B1F39E63D27603F1A2E7E804E92514FAC721F353D849B0787288F5026809AD84',
    ], $hooks);
    
    # List of newly created hooks (none here)
    $createdHooks = $TxHookParser->createdHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([], $createdHooks);
    
    # List of uninstalled hooks (none here)
    $createdHooks = $TxHookParser->uninstalledHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([], $createdHooks);
    
    # List of all accounts
    $accounts = $TxHookParser->accounts();
    $this->assertIsArray($accounts);
    $this->assertEquals([
      'rG1QQv2nh2gr7RCZ1P8YYcBUKCCN633jCn',
    ], $accounts);
    
    # Specific account
    $accountHooks = $TxHookParser->accountHooks('rG1QQv2nh2gr7RCZ1P8YYcBUKCCN633jCn');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([
      'B1F39E63D27603F1A2E7E804E92514FAC721F353D849B0787288F5026809AD84'
    ], $accountHooks);
    
    # Unrelated account
    $accountHooks = $TxHookParser->accountHooks('rQJecEU8BT5NmQvdjEtHRwTC2XLwxgkCpE');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([], $accountHooks);
    
    # Specific hook
    $hookAccounts = $TxHookParser->hookAccounts('B1F39E63D27603F1A2E7E804E92514FAC721F353D849B0787288F5026809AD84');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([
      'rG1QQv2nh2gr7RCZ1P8YYcBUKCCN633jCn'
    ], $hookAccounts);
    
    # Unrelated hook
    $hookAccounts = $TxHookParser->hookAccounts('5EDF6439C47C423EAC99C1061EE2A0CE6A24A58C8E8A66E4B3AF91D76772DC77');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([], $hookAccounts);
  }
}