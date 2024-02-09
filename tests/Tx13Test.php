<?php declare(strict_types=1);

namespace XRPLWin\XRPLHookParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLHookParser\TxHookParser;

/***
 * SetHook
 * In this transaction setHook is executed, modified hook but without changes
 * This parser should register this as unmodified hook.
 */
final class Tx13Test extends TestCase
{
  public function testSetHookExtractDestroyedHook()
  {
    $transaction = file_get_contents(__DIR__.'/fixtures/tx13.json');
    $transaction = \json_decode($transaction);
    $TxHookParser = new TxHookParser($transaction->result);

    # List of installed hooks
    $installedHooks = $TxHookParser->installedHooks();
    $this->assertIsArray($installedHooks);
    $this->assertEquals([], $installedHooks);

    # List of modified hooks
    $modifiedHooks = $TxHookParser->modifiedHooks();

    $this->assertIsArray($modifiedHooks);
    $this->assertEquals([], $modifiedHooks);

    $unmodifiedHooks = $TxHookParser->unmodifiedHooks();
    $this->assertIsArray($unmodifiedHooks);
    $this->assertEquals([
      '8604F7EB191536337C1BF7F9048404FBAD1108F6C7BEBCCB9A07A6FDEDB0A840',
    ], $unmodifiedHooks);
    
    
  }
}