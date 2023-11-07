<?php declare(strict_types=1);

namespace XRPLWin\XRPLHookParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLHookParser\TxHookParser;

/***
 * SetHook - hook update
 */
final class Tx05Test extends TestCase
{
  public function testSetHookUpdate()
  {
    $transaction = file_get_contents(__DIR__.'/fixtures/tx05.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);

    # List of all hooks
    
    $hooks = $TxHookParser->hooks();
    $this->assertIsArray($hooks);
    $this->assertEquals([
      '012FD32EDF56C26C0C8919E432E15A5F242CC1B31AF814D464891C560465613B',
    ], $hooks);
    
    # List of newly created hooks (none here)
    $createdHooks = $TxHookParser->createdHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([], $createdHooks);
    
    # List of all accounts
    $accounts = $TxHookParser->accounts();
    $this->assertIsArray($accounts);
    $this->assertEquals([
      'raPSFU999HcwpyRojdNh2i96T22gY9fgxL',
    ], $accounts);
    
    # Specific account
    $accountHooks = $TxHookParser->accountHooks('raPSFU999HcwpyRojdNh2i96T22gY9fgxL');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([
      '012FD32EDF56C26C0C8919E432E15A5F242CC1B31AF814D464891C560465613B'
    ], $accountHooks);
    
    # Unrelated account
    $accountHooks = $TxHookParser->accountHooks('rQJecEU8BT5NmQvdjEtHRwTC2XLwxgkCpE');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([], $accountHooks);
    
    
    # Specific hook
    $hookAccounts = $TxHookParser->hookAccounts('012FD32EDF56C26C0C8919E432E15A5F242CC1B31AF814D464891C560465613B');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([
      'raPSFU999HcwpyRojdNh2i96T22gY9fgxL'
    ], $hookAccounts);
    
    # Unrelated hook
    $hookAccounts = $TxHookParser->hookAccounts('5EDF6439C47C423EAC99C1061EE2A0CE6A24A58C8E8A66E4B3AF91D76772DC77');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([], $hookAccounts);
  }
}