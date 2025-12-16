<?php

namespace AlfaDesk\API\APICesta;

use SYS;
use AlfaDesk\API\APICesta;
use AlfaDesk\API\Zariadenie;

class POST_Zariadenie extends APICesta {
  static public function spusti(\Slim\Psr7\Request $request, \Slim\Psr7\Response $response, array $args) : \Slim\Psr7\Response {
    global $DB, $LOG;

    if(!SYS::je_prihlaseny()) return self::generuj_json_odpoved(\API::odpoved_chyba_prihlasenie(), $response, 401);

    try {
      $params = (array)json_decode($request->getBody());
    } catch(\Exception $e) {
      $LOG->error("Chyba pri parsovaní JSON tela požiadavky");
      throw $e;
    }

    $nazov = $params["nazov"] ?? "";
    $nh_predpoklad = $params["nh_predpoklad"] ?? 0;
    $nh_priemer = $params["nh_priemer"] ?? 0;

    if(!$nazov) {
      return self::generuj_json_odpoved(\API::odpoved_chyba_parametre(), $response, 500);
    }

    try {
      $DB->insert("devices", [
        "nazov" => $nazov,
        "nh_predpoklad" => $nh_predpoklad,
        "nh_priemer" => $nh_priemer
      ]);
    } catch(\Exception $e) {
      $LOG->error("Chyba pri vkladaní nového zariadenia do databázy: ".$e->getMessage());
      return self::generuj_json_odpoved(\API::odpoved_chyba(999, $e->getMessage()), $response, 500);
    }

    $zariadenie = Zariadenie::NacitajPodlaID((int)$DB->lastInsertId());
    return self::generuj_json_odpoved(\API::odpoved($zariadenie->DoArray()), $response);
  }
}
