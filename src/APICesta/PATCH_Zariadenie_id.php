<?php

namespace AlfaDesk\API\APICesta;

use SYS;
use AlfaDesk\API\APICesta;
use AlfaDesk\API\Zariadenie;

class PATCH_Zariadenie_id extends APICesta {
  static public function spusti(\Slim\Psr7\Request $request, \Slim\Psr7\Response $response, array $args) : \Slim\Psr7\Response {
    global $DB, $LOG;

    if(!SYS::je_prihlaseny()) return self::generuj_json_odpoved(\API::odpoved_chyba_prihlasenie(), $response, 401);

    $id = $args["id"] ?? 0;
    if(!$id || !is_numeric($id) || $id <= 0) {
      return self::generuj_json_odpoved(\API::odpoved_chyba_parametre(), $response, 500);
    }

    $zariadenie = Zariadenie::NacitajPodlaID((int)$id);
    if(!$zariadenie) {
      return self::generuj_json_odpoved(\API::odpoved_chyba(404, "Zariadenie so zadaným ID neexistuje"), $response, 404);
    }

    try {
      $params = (array)json_decode($request->getBody());
    } catch(\Exception $e) {
      $LOG->error("Chyba pri parsovaní JSON tela požiadavky");
      throw $e;
    }

    $DB->executeQuery("UPDATE devices SET
      nazov = ?,
      nh_predpoklad = ?,
      nh_priemer = ?
      WHERE id = ?", [
        $params["nazov"] ?? $zariadenie->nazov,
        $params["nh_predpoklad"] ?? $zariadenie->nh_predpoklad,
        $params["nh_priemer"] ?? $zariadenie->nh_priemer,
        $zariadenie->id
      ]);

    return self::generuj_json_odpoved(\API::odpoved($zariadenie->Obnov()->DoArray()), $response);
  }
}
