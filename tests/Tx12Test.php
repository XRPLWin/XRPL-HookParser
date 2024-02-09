<?php declare(strict_types=1);

namespace XRPLWin\XRPLHookParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLHookParser\TxHookParser;

/***
 * SetHook
 */
final class Tx12Test extends TestCase
{
  public function testSetHookCreated()
  {
    $transaction = file_get_contents(__DIR__.'/fixtures/tx12.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);

    # List of all hooks
    
    $hooks = $TxHookParser->hooks();
    $this->assertIsArray($hooks);
    $this->assertEquals([
      '264EE7E3319FC0096B65EDF5BFD70264DA8A38387887EC13BEF343F0F581A9D5',
    ], $hooks);

    # List of newly created hook definitions
    $createdHooks = $TxHookParser->createdHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([
      '264EE7E3319FC0096B65EDF5BFD70264DA8A38387887EC13BEF343F0F581A9D5',
    ], $createdHooks);
    

    # List of destroyed hook definitions
    $destroyedHooks = $TxHookParser->destroyedHooks();
    $this->assertIsArray($destroyedHooks);
    $this->assertEquals([], $destroyedHooks);
    

    # List of installed hooks
    $createdHooks = $TxHookParser->installedHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([
      '264EE7E3319FC0096B65EDF5BFD70264DA8A38387887EC13BEF343F0F581A9D5'
    ], $createdHooks);

    # List of uninstalled hooks
    $createdHooks = $TxHookParser->uninstalledHooks();
    $this->assertIsArray($createdHooks);
    $this->assertEquals([], $createdHooks);

    # List of modified hooks
    $modifiedHooks = $TxHookParser->modifiedHooks();
    $this->assertIsArray($modifiedHooks);
    $this->assertEquals([], $modifiedHooks);
    
    # List of all accounts
    $accounts = $TxHookParser->accounts();
    $this->assertIsArray($accounts);
    $this->assertEquals([
      'rHsJp9mP32tPwNyDjjyAggZ34hzMtvhUNy',
    ], $accounts);
    
    # Specific account
    $accountHooks = $TxHookParser->accountHooks('rHsJp9mP32tPwNyDjjyAggZ34hzMtvhUNy');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([
      '264EE7E3319FC0096B65EDF5BFD70264DA8A38387887EC13BEF343F0F581A9D5',
    ], $accountHooks);

    # Unrelated account
    $accountHooks = $TxHookParser->accountHooks('rQJecEU8BT5NmQvdjEtHRwTC2XLwxgkCpE');
    $this->assertIsArray($accountHooks);
    $this->assertEquals([], $accountHooks);
    
    # Specific hook 1
    $hookAccounts = $TxHookParser->hookAccounts('264EE7E3319FC0096B65EDF5BFD70264DA8A38387887EC13BEF343F0F581A9D5');
    $this->assertIsArray($hookAccounts);
    $this->assertEquals([
      'rHsJp9mP32tPwNyDjjyAggZ34hzMtvhUNy'
    ], $hookAccounts);

    $this->assertEquals(1,count($TxHookParser->createdHooks()));

    $createdHooksDetailed = $TxHookParser->createdHooksDetailed();
    $this->assertEquals(1,count($createdHooksDetailed));

    $params = TxHookParser::toParams($createdHooksDetailed['264EE7E3319FC0096B65EDF5BFD70264DA8A38387887EC13BEF343F0F581A9D5']);
    $this->assertIsArray($params);
    $this->assertNotEmpty($params); //one initial param defined in hook install
    $this->assertEquals(['61646D696E' => '0396E8C67D736A05A854B1A193F3CC925AC7CED87F25A9F1B3F1E1263F953B2E24'],$params);
  }

  public function testSetHookPositions()
  {
    $transaction = file_get_contents(__DIR__.'/fixtures/tx12.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);
    
    $uninstalledHooksPos = $TxHookParser->uninstalledHooksPos();
    $this->assertIsArray($uninstalledHooksPos);
    $this->assertEquals([], $uninstalledHooksPos);

    $installedHooksPos = $TxHookParser->installedHooksPos();
    $this->assertIsArray($installedHooksPos);
    $this->assertEquals([
      ['264EE7E3319FC0096B65EDF5BFD70264DA8A38387887EC13BEF343F0F581A9D5',0]
    ], $installedHooksPos);

    $modifiedHooksPos = $TxHookParser->modifiedHooksPos();
    $this->assertIsArray($modifiedHooksPos);
    $this->assertEquals([], $modifiedHooksPos);
    
    $unmodifiedHooksPos = $TxHookParser->unmodifiedHooksPos();
    $this->assertIsArray($unmodifiedHooksPos);
    $this->assertEquals([], $unmodifiedHooksPos);
  }
}