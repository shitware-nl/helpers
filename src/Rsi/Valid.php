<?php

namespace Rsi;

/**
 *  Validators.
 */
class Valid{

  const IBAN_FORMATS = [ //www/fred/tool/iban.php
    'AL' => '\\d{8}\\w{16}', //Albania
    'AD' => '\\d{8}\\w{12}', //Andorra
    'AT' => '\\d{16}', //Austria
    'AZ' => '\\w{4}\\d{20}', //Azerbaijan
    'BH' => '[A-Z]{4}\\w{14}', //Bahrain
    'BE' => '\\d{12}', //Belgium
    'BA' => '\\d{16}', //Bosnia and Herzegovina
    'BR' => '\\d{23}[A-Z]{1}\\w{1}', //Brazil
    'BG' => '[A-Z]{4}\\d{6}\\w{8}', //Bulgaria
    'CR' => '\\d{17}', //Costa Rica
    'HR' => '\\d{17}', //Croatia
    'CY' => '\\d{8}\\w{16}', //Cyprus
    'CZ' => '\\d{20}', //Czech Republic
    'DK' => '\\d{14}', //Denmark
    'DO' => '[A-Z]{4}\\d{20}', //Dominican Republic
    'TL' => '\\d{19}', //East Timor
    'EE' => '\\d{16}', //Estonia
    'FO' => '\\d{14}', //Faroe Islands
    'FI' => '\\d{14}', //Finland
    'FR' => '\\d{10}\\w{11}\\d{2}', //France
    'GE' => '\\w{2}\\d{16}', //Georgia
    'DE' => '\\d{18}', //Germany
    'GI' => '[A-Z]{4}\\w{15}', //Gibraltar
    'GR' => '\\d{7}\\w{16}', //Greece
    'GL' => '\\d{14}', //Greenland
    'GT' => '\\w{4}\\w{20}', //Guatemala
    'HU' => '\\d{24}', //Hungary
    'IS' => '\\d{22}', //Iceland
    'IE' => '\\w{4}\\d{14}', //Ireland
    'IL' => '\\d{19}', //Israel
    'IT' => '[A-Z]{1}\\d{10}\\w{12}', //Italy
    'JO' => '[A-Z]{4}\\d{22}', //Jordan
    'KZ' => '\\d{3}\\w{13}', //Kazakhstan
    'XK' => '\\d{4}\\d{10}\\d{2}', //Kosovo
    'KW' => '[A-Z]{4}\\w{22}', //Kuwait
    'LV' => '[A-Z]{4}\\w{13}', //Latvia
    'LB' => '\\d{4}\\w{20}', //Lebanon
    'LI' => '\\d{5}\\w{12}', //Liechtenstein
    'LT' => '\\d{16}', //Lithuania
    'LU' => '\\d{3}\\w{13}', //Luxembourg
    'MK' => '\\d{3}\\w{10}\\d{2}', //Macedonia
    'MT' => '[A-Z]{4}\\d{5}\\w{18}', //Malta
    'MR' => '\\d{23}', //Mauritania
    'MU' => '[A-Z]{4}\\d{19}[A-Z]{3}', //Mauritius
    'MC' => '\\d{10}\\w{11}\\d{2}', //Monaco
    'MD' => '\\w{2}\\w{18}', //Moldova
    'ME' => '\\d{18}', //Montenegro
    'NL' => '[A-Z]{4}\\d{10}', //Netherlands
    'NO' => '\\d{11}', //Norway
    'PK' => '\\w{4}\\d{16}', //Pakistan
    'PS' => '\\w{4}\\d{21}', //Palestinian territories
    'PL' => '\\d{24}', //Poland
    'PT' => '\\d{21}', //Portugal
    'QA' => '[A-Z]{4}\\w{21}', //Qatar
    'RO' => '[A-Z]{4}\\w{16}', //Romania
    'SM' => '[A-Z]{1}\\d{10}\\w{12}', //San Marino
    'SA' => '\\d{2}\\w{18}', //Saudi Arabia
    'RS' => '\\d{18}', //Serbia
    'SK' => '\\d{20}', //Slovakia
    'SI' => '\\d{15}', //Slovenia
    'ES' => '\\d{20}', //Spain
    'SE' => '\\d{20}', //Sweden
    'CH' => '\\d{5}\\w{12}', //Switzerland
    'TN' => '\\d{20}', //Tunisia
    'TR' => '\\d{5}\\w{17}', //Turkey
    'AE' => '\\d{3}\\d{16}', //United Arab Emirates
    'GB' => '[A-Z]{4}\\d{14}', //United Kingdom
    'VG' => '\\w{4}\\d{16}', //Virgin Islands, British
  ];
  /**
   *  Check if an International Bank Account Number (IBAN) is valid.
   *  @param string $value  IBAN (may have extra formatting characters).
   *  @return bool  True when valid.
   */
  public static function iban($value){
    $value = strtoupper(preg_replace('/[\\W_]/','',$value));
    if(!preg_match('/^[A-Z]{2}(0[2-9]|[1-8]\d|9[0-8])' . Record::get(self::IBAN_FORMATS,substr($value,0,2),'\\w{10,30}') . '$/',$value)) return false;
    $value = substr($value,4) . substr($value,0,4);
    for($c = 'A'; $c <= 'Z'; $c++) $value = str_replace($c,ord($c) - 55,$value);
    return bcmod($value,97) == 1;
  }
  /**
   *  Check if an European VAT number is valid.
   *  @param string $value  VAT number (may have extra formatting characters).
   *  @param array $info  Returns extra info related to a valid VAT number.
   *  @return bool  True when valid. If the VAT number could not be checked online this will always be true, $info then holds an
   *    error message.
   */
  public static function vatNo($value,&$info = null){
    $value = strtoupper(preg_replace('/\\W/','',$value));
    try{
      $soap = @new \SoapClient('http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl',['exceptions' => 1]);
      $info = (array)$soap->checkVat(['countryCode' => substr($value,0,2),'vatNumber' => substr($value,2)]);
      return $info['valid'];
    }
    catch(\SoapFault $e){
      $info = ['error' => $e->faultstring];
    }
    return true; //benefit of doubt
  }

}