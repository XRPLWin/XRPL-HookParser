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
  const ttCONTRACT = 9; //deprecated (removed from xahaud)
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
  const ttCLAWBACK = 30;

  //AMM suite
  const ttAMM_CLAWBACK = 31;
  const ttAMM_CREATE = 35;
  const ttAMM_DEPOSIT = 36;
  const ttAMM_WITHDRAW = 37;
  const ttAMM_VOTE = 38;
  const ttAMM_BID = 39;
  const ttAMM_DELETE = 40;

  const ttURITOKEN_MINT = 45;
  const ttURITOKEN_BURN = 46;
  const ttURITOKEN_BUY = 47;
  const ttURITOKEN_CREATE_SELL_OFFER = 48;
  const ttURITOKEN_CANCEL_SELL_OFFER = 49;
  
  //XChain suite
  const ttXCHAIN_CREATE_CLAIM_ID = 50;
  const ttXCHAIN_COMMIT = 51;
  const ttXCHAIN_CLAIM = 52;
  const ttXCHAIN_ACCOUNT_CREATE_COMMIT = 53;
  const ttXCHAIN_ADD_CLAIM_ATTESTATION = 54;
  const ttXCHAIN_ADD_ACCOUNT_CREATE_ATTESTATION = 55;
  const ttXCHAIN_MODIFY_BRIDGE = 56;
  const ttXCHAIN_CREATE_BRIDGE = 57;

  //DID/Oracle/MPToken/Credential suite
  const ttDID_SET = 58;
  const ttDID_DELETE = 59;
  const ttORACLE_SET = 60;
  const ttORACLE_DELETE = 61;
  const ttLEDGER_STATE_FIX = 62;
  const ttMPTOKEN_ISSUANCE_CREATE = 63;
  const ttMPTOKEN_ISSUANCE_DESTROY = 64;
  const ttMPTOKEN_ISSUANCE_SET = 65;
  const ttMPTOKEN_AUTHORIZE = 66;
  const ttCREDENTIAL_CREATE = 67;
  const ttCREDENTIAL_ACCEPT = 68;
  const ttCREDENTIAL_DELETE = 69;
  const ttNFTOKEN_MODIFY = 70;
  const ttPERMISSIONED_DOMAIN_SET = 71;
  const ttPERMISSIONED_DOMAIN_DELETE = 72;

  const ttCRON = 92;
  const ttCRON_SET = 93;
  const ttREMARKS_SET = 94;
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
    30 => 'ttCLAWBACK',
    31 => 'ttAMM_CLAWBACK',
    35 => 'ttAMM_CREATE',
    36 => 'ttAMM_DEPOSIT',
    37 => 'ttAMM_WITHDRAW',
    38 => 'ttAMM_VOTE',
    39 => 'ttAMM_BID',
    40 => 'ttAMM_DELETE',
    45 => 'ttURITOKEN_MINT',
    46 => 'ttURITOKEN_BURN',
    47 => 'ttURITOKEN_BUY',
    48 => 'ttURITOKEN_CREATE_SELL_OFFER',
    49 => 'ttURITOKEN_CANCEL_SELL_OFFER',
    50 => 'ttXCHAIN_CREATE_CLAIM_ID',
    51 => 'ttXCHAIN_COMMIT',
    52 => 'ttXCHAIN_CLAIM',
    53 => 'ttXCHAIN_ACCOUNT_CREATE_COMMIT',
    54 => 'ttXCHAIN_ADD_CLAIM_ATTESTATION',
    55 => 'ttXCHAIN_ADD_ACCOUNT_CREATE_ATTESTATION',
    56 => 'ttXCHAIN_MODIFY_BRIDGE',
    57 => 'ttXCHAIN_CREATE_BRIDGE',
    58 => 'ttDID_SET',
    59 => 'ttDID_DELETE',
    60 => 'ttORACLE_SET',
    61 => 'ttORACLE_DELETE',
    62 => 'ttLEDGER_STATE_FIX',
    63 => 'ttMPTOKEN_ISSUANCE_CREATE',
    64 => 'ttMPTOKEN_ISSUANCE_DESTROY',
    65 => 'ttMPTOKEN_ISSUANCE_SET',
    66 => 'ttMPTOKEN_AUTHORIZE',
    67 => 'ttCREDENTIAL_CREATE',
    68 => 'ttCREDENTIAL_ACCEPT',
    69 => 'ttCREDENTIAL_DELETE',
    70 => 'ttNFTOKEN_MODIFY',
    71 => 'ttPERMISSIONED_DOMAIN_SET',
    72 => 'ttPERMISSIONED_DOMAIN_DELETE',
    92 => 'ttCRON',
    93 => 'ttCRON_SET',
    94 => 'ttREMARKS_SET',
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