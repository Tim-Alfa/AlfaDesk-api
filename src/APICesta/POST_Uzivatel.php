<?php

namespace AlfaDesk\API\APICesta;

use SYS;
use AlfaDesk\API\APICesta;
use AlfaDesk\API\Uzivatel;

class POST_Uzivatel extends APICesta {
  static public function spusti(\Slim\Psr7\Request $request, \Slim\Psr7\Response $response, array $args) : \Slim\Psr7\Response {
    global $DB, $LOG;

    if(!SYS::je_prihlaseny()) return self::generuj_json_odpoved(\API::odpoved_chyba_prihlasenie(), $response, 401);

    $prihlaseny = $_SESSION["uzivatel"];
    if($prihlaseny["rola"] !== "admin") {
      return self::generuj_json_odpoved(\API::odpoved_chyba(403, "Nemáte oprávnenie na správu užívateľov"), $response, 403);
    }

    try {
      $params = (array)json_decode($request->getBody());
    } catch(\Exception $e) {
      $LOG->error("Chyba pri parsovaní JSON tela požiadavky");
      throw $e;
    }

    $meno = $params["meno"] ?? "";
    $priezvisko = $params["priezvisko"] ?? "";
    $cele_meno = $params["cele_meno"] ?? "";
    $osobne_cislo = $params["osobne_cislo"] ?? "";
    $email = $params["email"] ?? "";
    $telefonne_cislo = $params["telefonne_cislo"] ?? "";
    $pozicia = $params["pozicia"] ?? "";
    $rola = $params["rola"] ?? "uzivatel";
    $aktivny = $params["aktivny"] ?? true;
    $heslo = $params["heslo"] ?? "";

    if(!$email || !$heslo) {
      return self::generuj_json_odpoved(\API::odpoved_chyba_parametre(), $response, 500);
    }

    try {
      $DB->insert("users", [
        "meno" => $meno,
        "priezvisko" => $priezvisko,
        "cele_meno" => $cele_meno,
        "osobne_cislo" => $osobne_cislo,
        "email" => $email,
        "telefonne_cislo" => $telefonne_cislo,
        "pozicia" => $pozicia,
        "rola" => $rola,
        "aktivny" => $aktivny ? 1 : 0,
        "heslo" => password_hash($heslo, PASSWORD_DEFAULT)
      ]);
    } catch(\Exception $e) {
      $LOG->error("Chyba pri vkladaní nového užívateľa do databázy: ".$e->getMessage());
      return self::generuj_json_odpoved(\API::odpoved_chyba(999, $e->getMessage()), $response, 500);
    }

    $uzivatel = Uzivatel::NacitajPodlaID((int)$DB->lastInsertId());
    return self::generuj_json_odpoved(\API::odpoved($uzivatel->DoArray()), $response);
  }
}
