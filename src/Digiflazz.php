<?php
namespace Hillzacky\Digiflazz;
use Hillzacky\UserAgents;

class Digiflazz extents UserAgent {
  static $username = '';
  static $apikey = '';
  static $secret = '';
  static $hostapi = 'api.digiflazz.com';
  static $host = 'www.digiflazz.com';
  static $ep = 'https://'.self::$hostapi.'/v1';
  static $ua = (new userAgent)->generate();
}

class Http extends Digiflazz {
  static function post($url,$data,$type=true){
    $ch = curl_init();
    $headers = [
      'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
      'Accept-Encoding: gzip, deflate',
      'Accept-Language: en-US,en;q=0.5',
      'Cache-Control: no-cache',
      'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
      'Host: ' . Digiflazz::$host,
      'Referer: https://' . Digiflazz::$host,
      'User-Agent: ' . Digiflazz::$ua,
      'X-MicrosoftAjax: Delta=true'
    ];
    $options = [
      CURLOPT_URL => $url,
      CURLOPT_POST => 1,
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_HTTPHEADER => $headers
    ];
    curl_setopt_array($ch, $options);
    $res = curl_exec ($ch);
    curl_close ($ch);
    return json_decode($res,$type)['data'];
  }
  
  static function webhook($url,$opt,$type=true){
    $ch = curl_init();
    $headers = [
      'X-Digiflazz-Delivery: ' . $opt['delivery'],
      'X-Digiflazz-Event: ' . $opt['event'],
      'X-Hub-Signature: ' . self::sign($opt['signature']),
      'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
      'Accept-Encoding: gzip, deflate',
      'Accept-Language: en-US,en;q=0.5',
      'Cache-Control: no-cache',
      'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
      'Host: ' . Digiflazz::$host,
      'Referer: https://' . Digiflazz::$host,
      'User-Agent: ' . Digiflazz::$ua,
      'X-MicrosoftAjax: Delta=true'
    ];
    $options = [
      CURLOPT_URL => $url,
      CURLOPT_POST => 1,
      CURLOPT_POSTFIELDS => $opt['params'],
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_HTTPHEADER => $headers
    ];
    curl_setopt_array($ch, $options);
    $res = curl_exec ($ch);
    curl_close ($ch);
    return json_decode($res,$type)['data'];
  }

  static function sign($v){
    $u = Digiflazz::$username;
    $a = Digiflazz::$apikey;
    return md5($u.$a.$v);
  }

  static function signature($v){
    $s = Digiflazz::$secret;
    return 'sha1='.hash_hmac('sha1', $v, $s);
  }

  /* $a = create / update */
  static function event($a='create',$v){
    return $a.' '.json_encode($v);
  }
}

class Buyer extends Digiflazz {
  static function cekSaldo(){
    $host = Digiflazz::$ep.'/cek-saldo';
    $data = json_encode([
      "cmd" => "deposit",
      "username" => Digiflazz::$username,
      "sign" => Http::sign("depo")
    ]);
    return $host;
  }
  
  static function daftarHarga(){
    $host = Digiflazz::$ep.'/price-list';
    $data = json_encode([
      "cmd" => "prepaid",
      "username" => Digiflazz::$username,
      "sign" => Http::sign("pricelist")
    ]);
    return $host;
  }
  
  static function deposit($amount,$bank,$name){
    $host = Digiflazz::$ep.'/deposit';
    $data = json_encode([
      "username" => Digiflazz::$username,
      "amount" => (int)$amount,
      "Bank" => $bank,
      "owner_name" => $name,
      "sign" => Http::sign("deposit")
    ]);
    return $host;
  }
  
  static function topup($code,$no,$ref,$msg='',$test=false){
    $host = Digiflazz::$ep.'/transaction';
    $data = json_encode([
      "username" => Digiflazz::$username,
      "buyer_sku_code" => $code,
      "customer_no" => $no,
      "ref_id" => $ref,
      "msg" => $msg,
      "testing" => $test,
      "sign" => Http::sign($ref)
    ]);
    return $host;
  }
  
  static function cekTagihan($code,$no,$ref,$test=false){
    $host = Digiflazz::$ep.'/transaction';
    $data = json_encode([
      "commands" => "inq-pasca",
      "username" => Digiflazz::$username,
      "buyer_sku_code" => $code,
      "customer_no" => $no,
      "ref_id" => $ref,
      "testing" => $test,
      "sign" => Http::sign($ref)
    ]);
    return $host;
  }
  
  static function bayarTagihan($code,$no,$ref,$test=false){
    $host = Digiflazz::$ep.'/transaction';
    $data = json_encode([
      "commands" => "pay-pasca",
      "username" => Digiflazz::$username,
      "buyer_sku_code" => $code,
      "customer_no" => $no,
      "ref_id" => $ref,
      "testing" => $test,
      "sign" => Http::sign($ref)
    ]);
    return $host;
  }
  
  static function cekStatus($code,$no,$ref){
    $host = Digiflazz::$ep.'/transaction';
    $data = json_encode([
      "commands" => "status-pasca",
      "username" => Digiflazz::$username,
      "buyer_sku_code" => $code,
      "customer_no" => $no,
      "ref_id" => $ref,
      "sign" => Http::sign($ref)
    ]);
    return $host;
  }
  
  static function inquiryPln($no){
    $host = Digiflazz::$ep.'/transaction';
    $data = json_encode([
      "commands" => "pln-subscribe",
      "customer_no" => $no
    ]);
    return $host;
  }
}

class Seller extends Digiflazz {
  
  //Prabayar
  static function topup($code,$no,$ref){
    $host = Digiflazz::$ep.'/transaction';
    $data = json_encode([
      "commands" => "topup",
      "username" => Digiflazz::$username,
      "ref_id" => $ref,
      "hp" => $no,
      "pulsa_code" => $code,
      "sign" => Http::sign($ref)
    ]);
    return $host;
  }
  
  static function status($no=0){
    $s = [[
      "status" => 0,
      "deskripsi" => "Sedang Diproses"
     ],[
      "status" => 1,
      "deskripsi" => "Sukses"
     ],[
      "status" => 2,
      "deskripsi" => "Gagal"
     ]];
    return ($no > -1 && $no < 3) ? $s[$no] :
      ["status"=>$no,"deskripsi" => "Tidak Valid"];
  }
  
  //Pascabayar
  static function cekTagihan($code,$no,$ref){
    $host = Digiflazz::$ep.'/transaction';
    $data = json_encode([
      "commands" => "inq-pasca",
      "username" => Digiflazz::$username,
      "code" => $code,
      "hp" => $no,
      "ref_id" => $ref,
      "sign" => Http::sign($ref)
    ]);
    return $host;
  }
  
  static function bayarTagihan($tr){
    $host = Digiflazz::$ep.'/transaction';
    $data = json_encode([
      "commands" => "pay-pasca",
      "username" => Digiflazz::$username,
      "tr_id" => $tr,
      "sign" => Http::sign($tr)
    ]);
    return $host;
  }
  
  static function cekStatus($ref){
    $host = Digiflazz::$ep.'/transaction';
    $data = json_encode([
      "commands" => "checkstatus",
      "username" => Digiflazz::$username,
      "ref_id" => $ref,
      "sign" => Http::sign($ref)
    ]);
    return $host;
  }
}