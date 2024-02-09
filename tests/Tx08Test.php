<?php declare(strict_types=1);

namespace XRPLWin\XRPLHookParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLHookParser\TxHookParser;

/***
 * EmitFailure - emit failure, extract hook from emitDetails
 */
final class Tx08Test extends TestCase
{
  public function testEmitFailureExtractHook()
  {
    $transaction = file_get_contents(__DIR__.'/fixtures/tx08.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);

    # List of all hooks
    
    $hooks = $TxHookParser->hooks();
    $this->assertIsArray($hooks);
    $this->assertEquals([
      'C17685012D17B83C1FD911DD6505F3047477E7F145DF09BFAF89CB7A436DD9BB',
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
      'rrrrrrrrrrrrrrrrrrrrrhoLvTp',
    ], $accounts);
    
    # Specific account
    $accountHooks = $TxHookParser->accountHooks('rrrrrrrrrrrrrrrrrrrrrhoLvTp');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([
      'C17685012D17B83C1FD911DD6505F3047477E7F145DF09BFAF89CB7A436DD9BB'
    ], $accountHooks);
    
    # Unrelated account
    $accountHooks = $TxHookParser->accountHooks('rQJecEU8BT5NmQvdjEtHRwTC2XLwxgkCpE');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([], $accountHooks);
    
    # Specific hook
    $hookAccounts = $TxHookParser->hookAccounts('C17685012D17B83C1FD911DD6505F3047477E7F145DF09BFAF89CB7A436DD9BB');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([
      'rrrrrrrrrrrrrrrrrrrrrhoLvTp'
    ], $hookAccounts);
    
    # Unrelated hook
    $hookAccounts = $TxHookParser->hookAccounts('5EDF6439C47C423EAC99C1061EE2A0CE6A24A58C8E8A66E4B3AF91D76772DC77');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([], $hookAccounts);
  }

  public function testEmitFailurePositions()
  {
    $transaction = file_get_contents(__DIR__.'/fixtures/tx08.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);
    
    $uninstalledHooksPos = $TxHookParser->uninstalledHooksPos();
    $this->assertIsArray($uninstalledHooksPos);
    $this->assertEquals([], $uninstalledHooksPos);

    $installedHooksPos = $TxHookParser->installedHooksPos();
    $this->assertIsArray($installedHooksPos);
    $this->assertEquals([], $installedHooksPos);

    $modifiedHooksPos = $TxHookParser->modifiedHooksPos();
    $this->assertIsArray($modifiedHooksPos);
    $this->assertEquals([], $modifiedHooksPos);
    
    $unmodifiedHooksPos = $TxHookParser->unmodifiedHooksPos();
    $this->assertIsArray($unmodifiedHooksPos);
    $this->assertEquals([], $unmodifiedHooksPos);
  }
}