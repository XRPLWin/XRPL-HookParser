<?php declare(strict_types=1);

namespace XRPLWin\XRPLHookParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLHookParser\TxHookParser;

/***
 * Invoke
 * In this sample hook invoker and target are present.
 * @see https://docs.xahau.network/features/transaction-types/invoke
 */
final class Tx02Test extends TestCase
{
  public function testInvoke()
  {
    $transaction = file_get_contents(__DIR__.'/fixtures/tx02.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);

    # List of all hooks
    
    $hooks = $TxHookParser->hooks();
    $this->assertIsArray($hooks);
    $this->assertEquals([
      '5EDF6439C47C423EAC99C1061EE2A0CE6A24A58C8E8A66E4B3AF91D76772DC77',
    ], $hooks);
    
    # List of newly created hooks
    $createdHooks = $TxHookParser->createdHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([], $createdHooks);
    
    # List of all accounts
    $accounts = $TxHookParser->accounts();
    $this->assertIsArray($accounts);
    $this->assertEquals([
      'rwyypATD1dQxDbdQjMvrqnsHr2cQw5rjMh',
      'r223rsyz1cfqPbjmiX6oYu1hFgNwCkWZH',
    ], $accounts);
    
    # Specific account
    $accountHooks = $TxHookParser->accountHooks('rwyypATD1dQxDbdQjMvrqnsHr2cQw5rjMh');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([
      '5EDF6439C47C423EAC99C1061EE2A0CE6A24A58C8E8A66E4B3AF91D76772DC77'
    ], $accountHooks);
    
    # Specific hook
    $hookAccounts = $TxHookParser->hookAccounts('5EDF6439C47C423EAC99C1061EE2A0CE6A24A58C8E8A66E4B3AF91D76772DC77');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([
      'rwyypATD1dQxDbdQjMvrqnsHr2cQw5rjMh',
      'r223rsyz1cfqPbjmiX6oYu1hFgNwCkWZH',
    ], $hookAccounts);
  }
}