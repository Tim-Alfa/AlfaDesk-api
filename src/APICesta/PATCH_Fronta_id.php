<?php

namespace AlfaDesk\API\APICesta;

use SYS;
use AlfaDesk\API\APICesta;
use AlfaDesk\API\Fronta;

class PATCH_Fronta_id extends APICesta {
  static public function spusti(\Slim\Psr7\Request $request, \Slim\Psr7\Response $response, array $args) : \Slim\Psr7\Response {
    global $DB, $LOG;

    if(!SYS::je_prihlaseny()) return self::generuj_json_odpoved(\API::odpoved_chyba_prihlasenie(), $response, 401);

    $id = $args["id"] ?? 0;
    if(!$id || !is_numeric($id) || $id <= 0) {
      return self::generuj_json_odpoved(\API::odpoved_chyba_parametre(), $response, 500);
    }

    $fronta = Fronta::NacitajPodlaID((int)$id);
    if(!$fronta) {
      return self::generuj_json_odpoved(\API::odpoved_chyba(404, "Fronta so zadaným ID neexistuje"), $response, 404);
    }

    try {
      $params = (array)json_decode($request->getBody());
    } catch(\Exception $e) {
      $LOG->error("Chyba pri parsovaní JSON tela požiadavky");
      throw $e;
    }

    $DB->executeQuery("UPDATE queues SET
      nazov = ?
      WHERE id = ?", [
        $params["nazov"] ?? $fronta->nazov,
        $fronta->id
      ]);

    return self::generuj_json_odpoved(\API::odpoved($fronta->Obnov()->DoArray()), $response);
  }
}
