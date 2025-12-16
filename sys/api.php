<?php

class API {
  static function assert_parametre_vsetky(...$params) {
    foreach($params as $param) if(!array_key_exists($param, $_GET)) self::odpoved_chyba_parametre();
  }

  static function nastav_headers() {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1
    header('Pragma: no-cache'); // HTTP 1.0
    header('Expires: 0'); // Proxies
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET');
  }

  static function odpoved_chyba_prihlasenie() : string {
    return self::odpoved_chyba(10, "Nie ste prihlásený");
    
  }

  static function odpoved_chyba_neimplementovane() : string {
    return self::odpoved_chyba(999, "Funkcia nie je implementovaná");
  }

  static function odpoved_chyba_pravo() : string {
    return self::odpoved_chyba(2, "Nemáte na vykonanie akcie právo");
  }

  static function odpoved_chyba_parametre() : string {
    return self::odpoved_chyba(1, "Vstupné parametre chýbajú alebo sú neplatné");
  }

  static function odpoved_chyba(int $kod, string $popis, string $traceId = "") : string {
     return self::odpoved([
       "kod" => $kod,
       "popis" => $popis,
       "traceId" => $traceId
     ]);
  }

  static function odpoved(array $data) : string {
    return json_encode($data);
  }
}