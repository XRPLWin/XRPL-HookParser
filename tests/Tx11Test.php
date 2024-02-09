<?php declare(strict_types=1);

namespace XRPLWin\XRPLHookParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLHookParser\TxHookParser;

/***
 * SetHook
 * - in this transaction: Replaced 2 hooks with 2 new, both destroyed and created
 */
final class Tx11Test extends TestCase
{
  public function testSetHookCreated()
  {
    $transaction = file_get_contents(__DIR__.'/fixtures/tx11.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);

    # List of all hooks
    
    $hooks = $TxHookParser->hooks();
    $this->assertIsArray($hooks);
    $this->assertEquals([
      'B8A38F9E5D7249C14D45838687E28AF0A615EF5EB868B3D367D33B050CBA7FF0',
      '09052AC45C29C226FD15731B0F96F03FF0B714961FC49A62B10897474D6EA03A',
      'DCA4B765D3E1372B10CC641940E4C053C23862C32E9D838D4EE98853A05C9202',
      '0E277C6F5DE7E8CA7482FCAA381CB77E93D8D595796B4ED79B5489C9A28A9DDD',
    ], $hooks);

    # List of newly created hook definitions
    $createdHooks = $TxHookParser->createdHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([
      '09052AC45C29C226FD15731B0F96F03FF0B714961FC49A62B10897474D6EA03A',
      'DCA4B765D3E1372B10CC641940E4C053C23862C32E9D838D4EE98853A05C9202',
    ], $createdHooks);
    

    # List of destroyed hook definitions
    $destroyedHooks = $TxHookParser->destroyedHooks();
    $this->assertIsArray($destroyedHooks);
    $this->assertEquals([
      'B8A38F9E5D7249C14D45838687E28AF0A615EF5EB868B3D367D33B050CBA7FF0',
      '0E277C6F5DE7E8CA7482FCAA381CB77E93D8D595796B4ED79B5489C9A28A9DDD'
    ], $destroyedHooks);
    

    # List of installed hooks
    $createdHooks = $TxHookParser->installedHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([
      'DCA4B765D3E1372B10CC641940E4C053C23862C32E9D838D4EE98853A05C9202',
      '09052AC45C29C226FD15731B0F96F03FF0B714961FC49A62B10897474D6EA03A'
    ], $createdHooks);

    # List of uninstalled hooks
    $createdHooks = $TxHookParser->uninstalledHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([
      '0E277C6F5DE7E8CA7482FCAA381CB77E93D8D595796B4ED79B5489C9A28A9DDD',
      'B8A38F9E5D7249C14D45838687E28AF0A615EF5EB868B3D367D33B050CBA7FF0'
    ], $createdHooks);

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
      'DCA4B765D3E1372B10CC641940E4C053C23862C32E9D838D4EE98853A05C9202',
      '09052AC45C29C226FD15731B0F96F03FF0B714961FC49A62B10897474D6EA03A',
      '0E277C6F5DE7E8CA7482FCAA381CB77E93D8D595796B4ED79B5489C9A28A9DDD',
      'B8A38F9E5D7249C14D45838687E28AF0A615EF5EB868B3D367D33B050CBA7FF0'
    ], $accountHooks);

    # Unrelated account
    $accountHooks = $TxHookParser->accountHooks('rQJecEU8BT5NmQvdjEtHRwTC2XLwxgkCpE');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([], $accountHooks);
    
    # Specific hook 1
    $hookAccounts = $TxHookParser->hookAccounts('B8A38F9E5D7249C14D45838687E28AF0A615EF5EB868B3D367D33B050CBA7FF0');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([
      'r223rsyz1cfqPbjmiX6oYu1hFgNwCkWZH'
    ], $hookAccounts);

    # Specific hook 2
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
    $transaction = file_get_contents(__DIR__.'/fixtures/tx11.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);
    
    $uninstalledHooksPos = $TxHookParser->uninstalledHooksPos();
    $this->assertIsArray($uninstalledHooksPos);
    $this->assertEquals([
      ['0E277C6F5DE7E8CA7482FCAA381CB77E93D8D595796B4ED79B5489C9A28A9DDD',0],
      ['B8A38F9E5D7249C14D45838687E28AF0A615EF5EB868B3D367D33B050CBA7FF0',1]
    ], $uninstalledHooksPos);

    $installedHooksPos = $TxHookParser->installedHooksPos();
    $this->assertIsArray($installedHooksPos);
    $this->assertEquals([
      ['DCA4B765D3E1372B10CC641940E4C053C23862C32E9D838D4EE98853A05C9202',0],
      ['09052AC45C29C226FD15731B0F96F03FF0B714961FC49A62B10897474D6EA03A',1]
    ], $installedHooksPos);

    $modifiedHooksPos = $TxHookParser->modifiedHooksPos();
    $this->assertIsArray($modifiedHooksPos);
    $this->assertEquals([], $modifiedHooksPos);
    
    $unmodifiedHooksPos = $TxHookParser->unmodifiedHooksPos();
    $this->assertIsArray($unmodifiedHooksPos);
    $this->assertEquals([], $unmodifiedHooksPos);
  }
}