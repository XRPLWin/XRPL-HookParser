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
    return;
    //TODO
    # List of newly created hooks
    $createdHooks = $TxHookParser->createdHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([
      'B2B2892A2E738D8C074C5C5B40253BDAB5E4AD3D45113144EAC5E933457AF648'
    ], $createdHooks);
    
    # List of destroyed hook definitions
    $createdHooks = $TxHookParser->destroyedHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([
      '09052AC45C29C226FD15731B0F96F03FF0B714961FC49A62B10897474D6EA03A'
    ], $createdHooks);
    
    
    # List of uninstalled hooks
    $createdHooks = $TxHookParser->uninstalledHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([
      '09052AC45C29C226FD15731B0F96F03FF0B714961FC49A62B10897474D6EA03A'
    ], $createdHooks);
    
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
      'B2B2892A2E738D8C074C5C5B40253BDAB5E4AD3D45113144EAC5E933457AF648',
      '09052AC45C29C226FD15731B0F96F03FF0B714961FC49A62B10897474D6EA03A'
    ], $accountHooks);
    
    # Unrelated account
    $accountHooks = $TxHookParser->accountHooks('rQJecEU8BT5NmQvdjEtHRwTC2XLwxgkCpE');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([], $accountHooks);
    
    # Specific installed hook
    $hookAccounts = $TxHookParser->hookAccounts('B2B2892A2E738D8C074C5C5B40253BDAB5E4AD3D45113144EAC5E933457AF648');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([
      'r223rsyz1cfqPbjmiX6oYu1hFgNwCkWZH'
    ], $hookAccounts);

    # Specific uninstalled hook
    $hookAccounts = $TxHookParser->hookAccounts('09052AC45C29C226FD15731B0F96F03FF0B714961FC49A62B10897474D6EA03A');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([
      'r223rsyz1cfqPbjmiX6oYu1hFgNwCkWZH'
    ], $hookAccounts);
    
    # Unrelated hook
    $hookAccounts = $TxHookParser->hookAccounts('5EDF6439C47C423EAC99C1061EE2A0CE6A24A58C8E8A66E4B3AF91D76772DC77');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([], $hookAccounts);
  }
}