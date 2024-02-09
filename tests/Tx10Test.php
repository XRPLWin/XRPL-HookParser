<?php declare(strict_types=1);

namespace XRPLWin\XRPLHookParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLHookParser\TxHookParser;

/***
 * SetHook
 * - in this transaction: 2 created/installed hooks (new Hook node)
 */
final class Tx10Test extends TestCase
{
  public function testSetHookCreated()
  {
    $transaction = file_get_contents(__DIR__.'/fixtures/tx10.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);

    # List of all hooks
    
    $hooks = $TxHookParser->hooks();
    $this->assertIsArray($hooks);
    $this->assertEquals([
      'B8A38F9E5D7249C14D45838687E28AF0A615EF5EB868B3D367D33B050CBA7FF0',
      '0E277C6F5DE7E8CA7482FCAA381CB77E93D8D595796B4ED79B5489C9A28A9DDD',
    ], $hooks);
    
    # List of newly created hook definitions
    $createdHooks = $TxHookParser->createdHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([
      'B8A38F9E5D7249C14D45838687E28AF0A615EF5EB868B3D367D33B050CBA7FF0',
      '0E277C6F5DE7E8CA7482FCAA381CB77E93D8D595796B4ED79B5489C9A28A9DDD',
    ], $createdHooks);

    # List of destroyed hook definitions
    $createdHooks = $TxHookParser->destroyedHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([], $createdHooks);

    # List of installed hooks
    $createdHooks = $TxHookParser->installedHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([
      '0E277C6F5DE7E8CA7482FCAA381CB77E93D8D595796B4ED79B5489C9A28A9DDD',
      'B8A38F9E5D7249C14D45838687E28AF0A615EF5EB868B3D367D33B050CBA7FF0',
    ], $createdHooks);

    # List of uninstalled hooks
    $uninstalledHooks = $TxHookParser->uninstalledHooks();
    $this->assertIsArray($uninstalledHooks);
    $this->assertEquals([], $uninstalledHooks);

    # List of modified hooks
    $modifiedHooks = $TxHookParser->modifiedHooks();
    $this->assertIsArray($modifiedHooks);
    $this->assertEquals([], $modifiedHooks);
    
    # List of all accounts
    $accounts = $TxHookParser->accounts();
    $this->assertIsArray($accounts);
    $this->assertEquals([
      'r223rsyz1cfqPbjmiX6oYu1hFgNwCkWZH',
    ], $accounts);
    
    # Specific account
    $accountHooks = $TxHookParser->accountHooks('r223rsyz1cfqPbjmiX6oYu1hFgNwCkWZH');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([
      '0E277C6F5DE7E8CA7482FCAA381CB77E93D8D595796B4ED79B5489C9A28A9DDD',
      'B8A38F9E5D7249C14D45838687E28AF0A615EF5EB868B3D367D33B050CBA7FF0',
    ], $accountHooks);

    # Unrelated account
    $accountHooks = $TxHookParser->accountHooks('rQJecEU8BT5NmQvdjEtHRwTC2XLwxgkCpE');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([], $accountHooks);
    
    # Specific installed hook 1
    $hookAccounts = $TxHookParser->hookAccounts('B8A38F9E5D7249C14D45838687E28AF0A615EF5EB868B3D367D33B050CBA7FF0');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([
      'r223rsyz1cfqPbjmiX6oYu1hFgNwCkWZH'
    ], $hookAccounts);

    # Specific installed hook 2
    $hookAccounts = $TxHookParser->hookAccounts('0E277C6F5DE7E8CA7482FCAA381CB77E93D8D595796B4ED79B5489C9A28A9DDD');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([
      'r223rsyz1cfqPbjmiX6oYu1hFgNwCkWZH'
    ], $hookAccounts);
    
    # Unrelated hook
    $hookAccounts = $TxHookParser->hookAccounts('5EDF6439C47C423EAC99C1061EE2A0CE6A24A58C8E8A66E4B3AF91D76772DC77');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([], $hookAccounts);
  }

  public function testSetHookPositions()
  {
    $transaction = file_get_contents(__DIR__.'/fixtures/tx10.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);
    
    $uninstalledHooksPos = $TxHookParser->uninstalledHooksPos();
    $this->assertIsArray($uninstalledHooksPos);
    $this->assertEquals([], $uninstalledHooksPos);

    $installedHooksPos = $TxHookParser->installedHooksPos();
    $this->assertIsArray($installedHooksPos);
    $this->assertEquals([
      ['0E277C6F5DE7E8CA7482FCAA381CB77E93D8D595796B4ED79B5489C9A28A9DDD',0],
      ['B8A38F9E5D7249C14D45838687E28AF0A615EF5EB868B3D367D33B050CBA7FF0',1]
    ], $installedHooksPos);

    $modifiedHooksPos = $TxHookParser->modifiedHooksPos();
    $this->assertIsArray($modifiedHooksPos);
    $this->assertEquals([], $modifiedHooksPos);
    
    $unmodifiedHooksPos = $TxHookParser->unmodifiedHooksPos();
    $this->assertIsArray($unmodifiedHooksPos);
    $this->assertEquals([], $unmodifiedHooksPos);
  }
}