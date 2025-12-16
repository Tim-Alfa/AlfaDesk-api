<?php

namespace AlfaDesk\API\APICesta;

use SYS;
use AlfaDesk\API\APICesta;
use AlfaDesk\API\Uzivatel;

class GET_Uzivatel_id extends APICesta {
  static public function spusti(\Slim\Psr7\Request $request, \Slim\Psr7\Response $response, array $args) : \Slim\Psr7\Response {
    global $DB, $LOG;

    if(!SYS::je_prihlaseny()) return self::generuj_json_odpoved(\API::odpoved_chyba_prihlasenie(), $response, 401);

    $prihlaseny = $_SESSION["uzivatel"];
    if($prihlaseny["rola"] !== "admin") {
      return self::generuj_json_odpoved(\API::odpoved_chyba(403, "Nemáte oprávnenie na správu užívateľov"), $response, 403);
    }

    $id = $args["id"] ?? 0;
    if(!$id || !is_numeric($id) || $id <= 0) {
      return self::generuj_json_odpoved(\API::odpoved_chyba_parametre(), $response, 500);
    }

    $uzivatel = Uzivatel::NacitajPodlaID((int)$id);
    if(!$uzivatel) {
      return self::generuj_json_odpoved(\API::odpoved_chyba(404, "Užívateľ so zadaným ID neexistuje"), $response, 404);
    }

    return self::generuj_json_odpoved(\API::odpoved($uzivatel->DoArray()), $response);
  }
}
