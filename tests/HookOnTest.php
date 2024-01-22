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
    
    //ALL (public) triggers
    $hookon = '0xfffffffffffffffffffffffffffffffffffffff7fffffffffffc1fffffc00a40';
    $triggered = HookOn::decode($hookon);
    $expected = HookOn::MAP;
    //Following are "private" triggers, as documented absent in https://richardah.github.io/xrpl-hookon-calculator
    unset($expected[HookOn::ttNFTOKEN_MINT]);
    unset($expected[HookOn::ttNFTOKEN_BURN]);
    unset($expected[HookOn::ttNFTOKEN_CREATE_OFFER]);
    unset($expected[HookOn::ttNFTOKEN_CANCEL_OFFER]);
    unset($expected[HookOn::ttNFTOKEN_ACCEPT_OFFER]);
    unset($expected[HookOn::ttGENESIS_MINT]);
    unset($expected[HookOn::ttIMPORT]);
    unset($expected[HookOn::ttCLAIM_REWARD]);
    unset($expected[HookOn::ttAMENDMENT]);
    unset($expected[HookOn::ttFEE]);
    unset($expected[HookOn::ttUNL_MODIFY]);
    unset($expected[HookOn::ttEMIT_FAILURE]);
    unset($expected[HookOn::ttUNL_REPORT]);
    unset($expected[HookOn::ttNICKNAME_SET]);
    unset($expected[HookOn::ttSPINAL_TAP]);
    //Remove "public" contract (added later)
    unset($expected[HookOn::ttCONTRACT]);
    $this->assertEquals($expected,$triggered);

    //ALL triggers except TRUST_SET
    $hookon = '0xfffffffffffffffffffffffffffffffffffffff7fffffffffffc1fffffd00a40';
    $triggered = HookOn::decode($hookon);
    $expected = HookOn::MAP;
    //Following are "private" triggers, as documented absent in https://richardah.github.io/xrpl-hookon-calculator
    unset($expected[HookOn::ttNFTOKEN_MINT]);
    unset($expected[HookOn::ttNFTOKEN_BURN]);
    unset($expected[HookOn::ttNFTOKEN_CREATE_OFFER]);
    unset($expected[HookOn::ttNFTOKEN_CANCEL_OFFER]);
    unset($expected[HookOn::ttNFTOKEN_ACCEPT_OFFER]);
    unset($expected[HookOn::ttGENESIS_MINT]);
    unset($expected[HookOn::ttIMPORT]);
    unset($expected[HookOn::ttCLAIM_REWARD]);
    unset($expected[HookOn::ttAMENDMENT]);
    unset($expected[HookOn::ttFEE]);
    unset($expected[HookOn::ttUNL_MODIFY]);
    unset($expected[HookOn::ttEMIT_FAILURE]);
    unset($expected[HookOn::ttUNL_REPORT]);
    unset($expected[HookOn::ttNICKNAME_SET]);
    unset($expected[HookOn::ttSPINAL_TAP]);

    //Remove "public" trust set and contract
    unset($expected[HookOn::ttTRUST_SET]);
    unset($expected[HookOn::ttCONTRACT]);
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