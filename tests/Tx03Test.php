<?php declare(strict_types=1);

namespace XRPLWin\XRPLHookParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLHookParser\TxHookParser;

/***
 * Payment + hook execution
 */
final class Tx03Test extends TestCase
{
  public function testPayment()
  {
    $transaction = file_get_contents(__DIR__.'/fixtures/tx03.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);

    # List of all hooks
    
    $hooks = $TxHookParser->hooks();
    $this->assertIsArray($hooks);
    $this->assertEquals([
      '2C6A87CBCB2D2E08686458113D9176B1ED1EBF66ABCCBAB234045E24C5EAE303',
    ], $hooks);
    
    # List of newly created hooks
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
      '2C6A87CBCB2D2E08686458113D9176B1ED1EBF66ABCCBAB234045E24C5EAE303'
    ], $accountHooks);

    # Unrelated account
    $accountHooks = $TxHookParser->accountHooks('rQJecEU8BT5NmQvdjEtHRwTC2XLwxgkCpE');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([], $accountHooks);

    
    # Specific hook
    $hookAccounts = $TxHookParser->hookAccounts('2C6A87CBCB2D2E08686458113D9176B1ED1EBF66ABCCBAB234045E24C5EAE303');
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