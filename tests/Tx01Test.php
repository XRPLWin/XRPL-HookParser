<?php declare(strict_types=1);

namespace XRPLWin\XRPLHookParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLHookParser\TxHookParser;

/***
 * EnableAmendment
 */
final class Tx01Test extends TestCase
{
  public function testEnableAmendment()
  {
    $transaction = file_get_contents(__DIR__.'/fixtures/tx01.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);

    # List of all hooks
    $hooks = $TxHookParser->hooks();
    $this->assertIsArray($hooks);
    $this->assertEquals([
      '610F33B8EBF7EC795F822A454FB852156AEFE50BE0CB8326338A81CD74801864',
      '5EDF6439C47C423EAC99C1061EE2A0CE6A24A58C8E8A66E4B3AF91D76772DC77'
    ], $hooks);

    # List of newly created hooks
    $createdHooks = $TxHookParser->createdHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([
      '610F33B8EBF7EC795F822A454FB852156AEFE50BE0CB8326338A81CD74801864',
      '5EDF6439C47C423EAC99C1061EE2A0CE6A24A58C8E8A66E4B3AF91D76772DC77'
    ], $createdHooks);
    
    # List of all accounts
    $accounts = $TxHookParser->accounts();
    $this->assertIsArray($accounts);
    $this->assertEquals([
      'rwyypATD1dQxDbdQjMvrqnsHr2cQw5rjMh',
      'rHsh4MNWJKXN2YGtSf95aEzFYzMqwGiBve',
      'rHb9CJAWyB4rj91VRWn96DkukG4bwdtyTh',
      'r6QZ6zfK37ZSec5hWiQDtbTxUaU2NWG3F',
      'r4FRPZbLnyuVeGiSi1Ap6uaaPvPXYZh1XN'
    ], $accounts);

    # Specific account
    $accountHooks = $TxHookParser->accountHooks('rHb9CJAWyB4rj91VRWn96DkukG4bwdtyTh');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([
      '5EDF6439C47C423EAC99C1061EE2A0CE6A24A58C8E8A66E4B3AF91D76772DC77',
      '610F33B8EBF7EC795F822A454FB852156AEFE50BE0CB8326338A81CD74801864'
    ], $accountHooks);

    # Specific hook
    $hookAccounts = $TxHookParser->hookAccounts('5EDF6439C47C423EAC99C1061EE2A0CE6A24A58C8E8A66E4B3AF91D76772DC77');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([
      'rwyypATD1dQxDbdQjMvrqnsHr2cQw5rjMh',
      'rHsh4MNWJKXN2YGtSf95aEzFYzMqwGiBve',
      'rHb9CJAWyB4rj91VRWn96DkukG4bwdtyTh',
      'r6QZ6zfK37ZSec5hWiQDtbTxUaU2NWG3F',
      'r4FRPZbLnyuVeGiSi1Ap6uaaPvPXYZh1XN',
    ], $hookAccounts);
  }

  public function testEnableAmendmentCreatedHooks()
  {
    $transaction = file_get_contents(__DIR__.'/fixtures/tx01.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);

    $created = $TxHookParser->createdHooksDetailed();
    $this->assertIsArray($created);
    $this->assertEquals(2,count($created));
    $this->assertEquals('HookDefinition',$created['610F33B8EBF7EC795F822A454FB852156AEFE50BE0CB8326338A81CD74801864']->LedgerEntryType);
  }
}