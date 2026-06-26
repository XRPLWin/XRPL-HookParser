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

  /**
   * Test that all ttXXX constants are mapped in MAP array
   */
  public function testMapCompleteness()
  {
    $reflection = new \ReflectionClass(HookOn::class);
    $constants = $reflection->getConstants(\ReflectionClassConstant::IS_PUBLIC);
    
    // Filter to only transaction type constants (tt*)
    $ttConstants = array_filter(
      $constants,
      fn($name) => str_starts_with($name, 'tt'),
      ARRAY_FILTER_USE_KEY
    );
    
    $map = HookOn::MAP;
    
    // Check each ttXXX constant has a corresponding MAP entry
    foreach ($ttConstants as $name => $code) {
      $this->assertArrayHasKey(
        $code,
        $map,
        "Constant $name (value: $code) is missing from MAP array"
      );
      $this->assertEquals(
        $name,
        $map[$code],
        "MAP[$code] should be '$name' but got '{$map[$code]}'"
      );
    }
    
    // Check each MAP entry has a corresponding constant
    foreach ($map as $code => $name) {
      $constantName = "HookOn::$name";
      $this->assertTrue(
        isset($ttConstants[$name]),
        "MAP entry $code => '$name' has no corresponding constant"
      );
      $this->assertEquals(
        $code,
        $ttConstants[$name],
        "Constant $name should have value $code but got {$ttConstants[$name]}"
      );
    }
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
    
    $this->assertEquals([3 => 'ttACCOUNT_SET', 21 => 'ttACCOUNT_DELETE'],$triggered);
    
    //All 256 0-255 minus one (ttHOOK_SET) = 255
    $hookon = '0x0000000000000000000000000000000000000000000000000000000000000000';
    $triggered = HookOn::decode($hookon,false);
    $this->assertEquals(255,count($triggered));

    //All 256 0-255 minus one (ttHOOK_SET) = 255
    $hookon = '0000000000000000000000000000000000000000000000000000000000000000';
    $triggered = HookOn::decode($hookon,false);
    $this->assertEquals(255,count($triggered));

    //All 256 0-255 minus one (ttHOOK_SET) = 255
    $hookon = '0X0000000000000000000000000000000000000000000000000000000000000000';
    $triggered = HookOn::decode($hookon,false);
    $this->assertEquals(255,count($triggered));

    //All 256 0-255 minus two (ttHOOK_SET,ttESCROW_FINISH) = 254
    $hookon = '0x0000000000000000000000000000000000000000000000000000000000000004';
    $triggered = HookOn::decode($hookon,false);
    $this->assertEquals(254,count($triggered));
    
    //ALL (public) triggers as of 10.2025
    $hookon = '0xfffffffffffffffffffffffffffffffffffffff10ffffffffffc1fffbfc00a40';
    $triggered = HookOn::decode($hookon);
    $expected = [
      0 => 'ttPAYMENT',
      1 => 'ttESCROW_CREATE',
      2 => 'ttESCROW_FINISH',
      3 => 'ttACCOUNT_SET',
      4 => 'ttESCROW_CANCEL',
      5 => 'ttREGULAR_KEY_SET',
      7 => 'ttOFFER_CREATE',
      8 => 'ttOFFER_CANCEL',
      10 => 'ttTICKET_CREATE',
      12 => 'ttSIGNER_LIST_SET',
      13 => 'ttPAYCHAN_CREATE',
      14 => 'ttPAYCHAN_FUND',
      15 => 'ttPAYCHAN_CLAIM',
      16 => 'ttCHECK_CREATE',
      17 => 'ttCHECK_CASH',
      18 => 'ttCHECK_CANCEL',
      19 => 'ttDEPOSIT_PREAUTH',
      20 => 'ttTRUST_SET',
      21 => 'ttACCOUNT_DELETE',
      22 => 'ttHOOK_SET',
      30 => 'ttCLAWBACK',
      45 => 'ttURITOKEN_MINT',
      46 => 'ttURITOKEN_BURN',
      47 => 'ttURITOKEN_BUY',
      48 => 'ttURITOKEN_CREATE_SELL_OFFER',
      49 => 'ttURITOKEN_CANCEL_SELL_OFFER',
      92 => 'ttCRON',
      93 => 'ttCRON_SET',
      94 => 'ttREMARKS_SET',
      95 => 'ttREMIT',
      97 => 'ttIMPORT',
      98 => 'ttCLAIM_REWARD',
      99 => 'ttINVOKE',
    ];
    
    $this->assertEquals($expected,$triggered);
  }

  public function testDecodeInvalidShouldThrowError()
  {
    $hookon = '0x000000000000000000000000000000000000000000000000000000003e3ff5be';
    $this->expectException(\Exception::class);
    HookOn::decode($hookon);
  }

  public function testDecodeInvalid()
  {
    $hookon = '0x000000000000000000000000000000000000000000000000000000003e3ff5be';
    $decoded = HookOn::decode($hookon,false);

    $this->assertEquals('ttPAYMENT', $decoded[0]);
    $this->assertEquals('ttCONTRACT', $decoded[9]);
    $this->assertEquals(null, $decoded[255]);
  }

}