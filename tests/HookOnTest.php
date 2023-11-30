<?php declare(strict_types=1);

namespace XRPLWin\XRPLHookParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLHookParser\HookOn;

/***
 * HookOnTest
 */
final class HookOnTest extends TestCase
{
  public function testNormalize()
  {
    $this->assertEquals(
                          'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF',
      HookOn::normalize('0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff')
    );
    $this->assertEquals(
                        'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF',
      HookOn::normalize('ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff')
    );
    $this->assertEquals(
                          'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF',
      HookOn::normalize('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF')
    );
    $this->assertEquals(
                          'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF',
      HookOn::normalize('0XFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF')
    );
  }

  public function testEncode()
  {
    $this->assertEquals(
      '0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffffbfffff',
      HookOn::encode()
    );

    $this->assertEquals(
      '0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffffbfffff',
      HookOn::encode([])
    );
    $this->assertEquals(
      '0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffff9fffff',
      HookOn::encode([HookOn::ttACCOUNT_DELETE])
    );
    $this->assertEquals(
      '0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffff9ffff7',
      HookOn::encode([HookOn::ttACCOUNT_DELETE,HookOn::ttACCOUNT_SET])
    );
    $this->assertEquals( //reversed
      '0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffff9ffff7',
      HookOn::encode([HookOn::ttACCOUNT_SET,HookOn::ttACCOUNT_DELETE])
    );
    $this->assertEquals(
      '0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffff9ffff1',
      HookOn::encode([HookOn::ttACCOUNT_SET,HookOn::ttACCOUNT_DELETE,HookOn::ttESCROW_CREATE,HookOn::ttESCROW_FINISH])
    );
    $this->assertEquals(
      '0xffffffffffffffffffffffffffffffffffffffffffffffffffffdfffffbfffff',
      HookOn::encode([HookOn::ttURITOKEN_MINT])
    );
  }

  public function testDecode()
  {
    $hookon = '0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffffbfffff';
    $triggered = HookOn::decode($hookon);
    $this->assertEquals([],$triggered);

    $hookon = '0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff';
    $triggered = HookOn::decode($hookon);
    $this->assertEquals([22 => 'ttHOOK_SET'],$triggered);

    $hookon = 'ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff';
    $triggered = HookOn::decode($hookon);
    $this->assertEquals([22 => 'ttHOOK_SET'],$triggered);

    $hookon = 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF';
    $triggered = HookOn::decode($hookon);
    $this->assertEquals([22 => 'ttHOOK_SET'],$triggered);

    $hookon = '0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF';
    $triggered = HookOn::decode($hookon);
    $this->assertEquals([22 => 'ttHOOK_SET'],$triggered);

    $hookon = '0XFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF';
    $triggered = HookOn::decode($hookon);
    $this->assertEquals([22 => 'ttHOOK_SET'],$triggered);

    $hookon = '0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffff9ffff7';
    $triggered = HookOn::decode($hookon);
    $this->assertEquals([21 => 'ttACCOUNT_DELETE', 3 => 'ttACCOUNT_SET'],$triggered);

    //ALL triggers
    $hookon = '0xfffffffffffffffffffffffffffffffffffffff7fffffffffffc1fffffc00a40';
    $triggered = HookOn::decode($hookon);
    $this->assertEquals(HookOn::MAP,$triggered);

    //ALL triggers except TRUST_SET
    $hookon = '0xfffffffffffffffffffffffffffffffffffffff7fffffffffffc1fffffd00a40';
    $triggered = HookOn::decode($hookon);
    $expected = HookOn::MAP;
    unset($expected[HookOn::ttTRUST_SET]);
    $this->assertEquals($expected,$triggered);
  }

  /*public function testDecodeInvalid()
  {
    $hookon = '0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffff9ffffz';
    $this->expectException(\Exception::class);
    HookOn::decode($hookon); //This should throw error
  }*/
  

}