<?php declare(strict_types=1);

namespace XRPLWin\XRPLHookParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLHookParser\TxHookParser;

/***
 * TxHookParser::toParams
 */
final class ToParamsTest extends TestCase
{
  public function testNoHookParameters()
  {
    $hookDefinition = \json_decode('{"LedgerEntryType":"HookDefinition","NewFields":{}}');
    $this->assertEquals([], TxHookParser::toParams($hookDefinition));
  }

  public function testEmptyHookParameters()
  {
    $hookDefinition = \json_decode('{"LedgerEntryType":"HookDefinition","NewFields":{"HookParameters":[]}}');
    $this->assertEquals([], TxHookParser::toParams($hookDefinition));
  }

  public function testHookParametersWithValues()
  {
    $hookDefinition = \json_decode('{"LedgerEntryType":"HookDefinition","NewFields":{"HookParameters":[
      {"HookParameter":{"HookParameterName":"61646D696E","HookParameterValue":"0396E8C67D736A05A854B1A193F3CC925AC7CED87F25A9F1B3F1E1263F953B2E24"}},
      {"HookParameter":{"HookParameterName":"4443","HookParameterValue":"02"}}
    ]}}');
    $this->assertEquals([
      '61646D696E' => '0396E8C67D736A05A854B1A193F3CC925AC7CED87F25A9F1B3F1E1263F953B2E24',
      '4443' => '02',
    ], TxHookParser::toParams($hookDefinition));
  }

  /**
   * rippled omits HookParameterValue when parameter value is empty
   * (eg. xahau-testnet hook 6053CCB80EFEFE2B2A9E58126B2FDC43A640E7C360B9280712D496E321310737)
   */
  public function testHookParametersWithoutValue()
  {
    $hookDefinition = \json_decode('{"LedgerEntryType":"HookDefinition","NewFields":{"HookParameters":[
      {"HookParameter":{"HookParameterName":"424D"}},
      {"HookParameter":{"HookParameterName":"534D","HookParameterValue":"01"}},
      {"HookParameter":{"HookParameterName":"4443"}}
    ]}}');
    $this->assertEquals([
      '424D' => '',
      '534D' => '01',
      '4443' => '',
    ], TxHookParser::toParams($hookDefinition));
  }
}
