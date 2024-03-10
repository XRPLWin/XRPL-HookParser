<?php declare(strict_types=1);

namespace XRPLWin\XRPLHookParser;

use Brick\Math\BigInteger;

/**
 * Transaction HookOn field Parser
 * @see https://xrpl-hooks.readme.io/docs/hookon-field
 * @see https://richardah.github.io/xrpl-hookon-calculator
 * @see https://stackoverflow.com/questions/5301034/how-to-generate-random-64-bit-value-as-decimal-string-in-php/5302533
 */
class HookOn
{
  /**
   * All triggers are ignored.
   * Used as basis for adding (triggering) triggers when encoding triggers to string.
   */
  const DEFAULT_IGNORED = '0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffffbfffff';

  /**
   * TT codes (https://github.com/Xahau/xahaud/blob/dev/hook/tts.h)
   */
  const ttPAYMENT = 0;
  const ttESCROW_CREATE = 1;
  const ttESCROW_FINISH = 2;
  const ttACCOUNT_SET = 3;
  const ttESCROW_CANCEL = 4;
  const ttREGULAR_KEY_SET = 5;
  const ttNICKNAME_SET = 6; //deprecated
  const ttOFFER_CREATE = 7;
  const ttOFFER_CANCEL = 8;
  const ttCONTRACT = 9; //deprecated
  const ttTICKET_CREATE = 10;
  const ttSPINAL_TAP = 11; //deprecated
  const ttSIGNER_LIST_SET = 12;
  const ttPAYCHAN_CREATE = 13;
  const ttPAYCHAN_FUND = 14;
  const ttPAYCHAN_CLAIM = 15;
  const ttCHECK_CREATE = 16;
  const ttCHECK_CASH = 17;
  const ttCHECK_CANCEL = 18;
  const ttDEPOSIT_PREAUTH = 19;
  const ttTRUST_SET = 20;
  const ttACCOUNT_DELETE = 21;
  const ttHOOK_SET = 22;
  const ttNFTOKEN_MINT = 25; //Private
  const ttNFTOKEN_BURN = 26; //Private
  const ttNFTOKEN_CREATE_OFFER = 27; //Private
  const ttNFTOKEN_CANCEL_OFFER = 28; //Private
  const ttNFTOKEN_ACCEPT_OFFER = 29; //Private
  const ttURITOKEN_MINT = 45;
  const ttURITOKEN_BURN = 46;
  const ttURITOKEN_BUY = 47;
  const ttURITOKEN_CREATE_SELL_OFFER = 48;
  const ttURITOKEN_CANCEL_SELL_OFFER = 49;
  const ttREMIT = 95;
  const ttGENESIS_MINT = 96; //Private
  const ttIMPORT = 97; //Private
  const ttCLAIM_REWARD = 98; //Private
  const ttINVOKE = 99;
  const ttAMENDMENT = 100; //Private
  const ttFEE = 101; //Private
  const ttUNL_MODIFY = 102; //Private
  const ttEMIT_FAILURE = 103; //Private
  const ttUNL_REPORT = 104; //Private


  const MAP = [
    0 => 'ttPAYMENT',
    1 => 'ttESCROW_CREATE',
    2 => 'ttESCROW_FINISH',
    3 => 'ttACCOUNT_SET',
    4 => 'ttESCROW_CANCEL',
    5 => 'ttREGULAR_KEY_SET',
    6 => 'ttNICKNAME_SET', //deprecated
    7 => 'ttOFFER_CREATE',
    8 => 'ttOFFER_CANCEL',
    9 => 'ttCONTRACT', //deprecated
    10 => 'ttTICKET_CREATE',
    11 => 'ttSPINAL_TAP', //deprecated
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
    25 => 'ttNFTOKEN_MINT',
    26 => 'ttNFTOKEN_BURN',
    27 => 'ttNFTOKEN_CREATE_OFFER',
    28 => 'ttNFTOKEN_CANCEL_OFFER',
    29 => 'ttNFTOKEN_ACCEPT_OFFER',
    45 => 'ttURITOKEN_MINT',
    46 => 'ttURITOKEN_BURN',
    47 => 'ttURITOKEN_BUY',
    48 => 'ttURITOKEN_CREATE_SELL_OFFER',
    49 => 'ttURITOKEN_CANCEL_SELL_OFFER',
    95 => 'ttREMIT',
    96 => 'ttGENESIS_MINT',
    97 => 'ttIMPORT',
    98 => 'ttCLAIM_REWARD',
    99 => 'ttINVOKE',
    100 => 'ttAMENDMENT',
    101 => 'ttFEE',
    102 => 'ttUNL_MODIFY',
    103 => 'ttEMIT_FAILURE',
    104 => 'ttUNL_REPORT',
  ];

  public static function normalize(string $hookonvalue): string
  {
    $hookonvalue = \strtoupper($hookonvalue);
    if(\str_starts_with($hookonvalue,'0X')) {
      $hookonvalue = \substr($hookonvalue,2);
    }
    return $hookonvalue;
  }

  public static function normalizedTo0x(string $hookonvalue): string
  {
    return '0x'.\strtolower($hookonvalue);
  }

  /**
   * @param string $hookonvalue - 0xfff... or 0xFFF... or FFF... or fff..
   * @return array list of triggers
   */
  public static function decode(string $hookonvalue, bool $throwOnInvalid = true): array
  {
    $triggers = [];
    $v = BigInteger::of(self::bchexdec(self::normalize($hookonvalue)));
    for ($n = 0; $n < 256; $n++) {
      $trigger = (string)$v->and(1); //Bitwise AND assignment
      $trigger = ($trigger == '1') ? true:false;
      if($n != 22) $trigger = !$trigger; //ttHOOK_SET is flipped (prevents accidental account bricking)
      if($trigger) {
        if(!isset(self::MAP[$n])) {
          if($throwOnInvalid)
            throw new \Exception('Invalid hookon value for hookon: "'.$hookonvalue.'" on position '.$n.' see https://github.com/Xahau/xahaud/blob/dev/hook/tts.h');
          else 
            $triggers[$n] = null;
        } else {
          $triggers[$n] = self::MAP[$n];
        }
      }
      $v = $v->shiftedRight(1);
    }
    return $triggers;
  }

  /**
   * Takes triggers and returns encoded HookOn value.
   * @param array $triggers
   * @return string 0xFFF...
   */
  public static function encode(array $triggers = []): string
  {
    $hookon = self::normalize(self::DEFAULT_IGNORED);
    foreach($triggers as $trigger) {
      $hookon = self::trigger($hookon,$trigger);
    }
    return self::normalizedTo0x($hookon);
  }

  public static function trigger(string $hookonvalue, int $trigger): string
  {
    $v = self::normalize($hookonvalue);
    $v = BigInteger::of(self::bchexdec($v)); //OK
    $v = $v->xor((1 << $trigger)); //Bitwise XOR assignment
    return self::base_convert_arbitrary((string)$v,10,16); //eg ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffbfffff
  }

  /**
   * Base convert implementation in large numbers represented as string.
   * @return string
   */
  public static function base_convert_arbitrary(string $number, int $fromBase, int $toBase): string
  {
    $digits = '0123456789abcdefghijklmnopqrstuvwxyz';
    $length = strlen($number);
    $result = '';

    $nibbles = array();
    for ($i = 0; $i < $length; ++$i) {
      $nibbles[$i] = strpos($digits, $number[$i]);
    }

    do {
      $value = 0;
      $newlen = 0;
      for ($i = 0; $i < $length; ++$i) {
        $value = $value * $fromBase + $nibbles[$i];
        if ($value >= $toBase) {
          $nibbles[$newlen++] = (int)($value / $toBase);
          $value %= $toBase;
        }
        else if ($newlen > 0) {
          $nibbles[$newlen++] = 0;
        }
      }
      $length = $newlen;
      $result = $digits[$value].$result;
    }
    while ($newlen != 0);
    return $result;
  }

  public static function bchexdec(string $hex): string
  {
    $dec = 0;
    $len = strlen($hex);
    for ($i = 1; $i <= $len; $i++) {
      $dec = bcadd((string)$dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
    }
    return $dec;
  }
}