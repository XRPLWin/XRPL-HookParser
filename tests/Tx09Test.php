<?php declare(strict_types=1);

namespace XRPLWin\XRPLHookParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLHookParser\TxHookParser;

/***
 * SetHook
 * - in this transaction: 1 created/installed hook, 1 destroyed/uninstalled hook
 */
final class Tx09Test extends TestCase
{
  public function testSetHookExtractDestroyedHook()
  {
    $transaction = file_get_contents(__DIR__.'/fixtures/tx09.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);

    # List of all hooks
    
    $hooks = $TxHookParser->hooks();
    $this->assertIsArray($hooks);
    $this->assertEquals([
      '09052AC45C29C226FD15731B0F96F03FF0B714961FC49A62B10897474D6EA03A',
      'B2B2892A2E738D8C074C5C5B40253BDAB5E4AD3D45113144EAC5E933457AF648',
      'DCA4B765D3E1372B10CC641940E4C053C23862C32E9D838D4EE98853A05C9202',
    ], $hooks);
   
    # List of newly created hooks
    $createdHooks = $TxHookParser->createdHooks();
    //dd($TxHookParser,$createdHooks);
    $this->assertIsArray($createdHooks);
    $this->assertEquals([
      'B2B2892A2E738D8C074C5C5B40253BDAB5E4AD3D45113144EAC5E933457AF648'
    ], $createdHooks);
    
    # List of destroyed hook definitions
    $destroyedHooks = $TxHookParser->destroyedHooks();
   
    $this->assertIsArray($destroyedHooks);
    $this->assertEquals([
      '09052AC45C29C226FD15731B0F96F03FF0B714961FC49A62B10897474D6EA03A'
    ], $destroyedHooks);
    
    # List of uninstalled hooks
    $uninstalledHooks = $TxHookParser->uninstalledHooks();
    
    $this->assertIsArray($uninstalledHooks);
    $this->assertEquals([
      '09052AC45C29C226FD15731B0F96F03FF0B714961FC49A62B10897474D6EA03A'
    ], $uninstalledHooks);

    # List of modified hooks
    $modifiedHooks = $TxHookParser->modifiedHooks();
    $this->assertIsArray($modifiedHooks);
    $this->assertEquals([
      'DCA4B765D3E1372B10CC641940E4C053C23862C32E9D838D4EE98853A05C9202',
    ], $modifiedHooks);
    
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
      '09052AC45C29C226FD15731B0F96F03FF0B714961FC49A62B10897474D6EA03A',
      'DCA4B765D3E1372B10CC641940E4C053C23862C32E9D838D4EE98853A05C9202',
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