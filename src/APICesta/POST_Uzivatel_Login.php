<?php

namespace AlfaDesk\API\APICesta;

use AlfaDesk\API\APICesta;
use SYS;

class POST_Uzivatel_Login extends APICesta {

  static public function spusti(\Slim\Psr7\Request $request, \Slim\Psr7\Response $response, array $args) : \Slim\Psr7\Response {
    global $DB;

    $params = (array)json_decode($request->getBody());

    $login = $params["login"] ?? "";
    $heslo = $params["heslo"] ?? "";

    if(!$login || !$heslo) {
      return self::generuj_json_odpoved(\API::odpoved_chyba_parametre(), $response, 500);
    }

    if(SYS::je_prihlaseny()) {
      return self::generuj_json_odpoved(\API::odpoved_chyba(101, "Užívateľ už prihlásený"), $response, 401);
    }

    $uzivatel = $DB->fetchAssociative("SELECT * FROM users WHERE email = ?", [$login]);

    if(!$uzivatel) {
      return self::generuj_json_odpoved(\API::odpoved_chyba(100, "Neplatné prihlasovacie údaje"), $response, 401);
    }

    if(!password_verify($heslo, $uzivatel["heslo"])) {
      return self::generuj_json_odpoved(\API::odpoved_chyba(100, "Neplatné prihlasovacie údaje"), $response, 401);
    }

    unset($uzivatel["heslo"]);

    $_SESSION["prihlaseny"] = true;
    $_SESSION["uzivatel"] = $uzivatel;

    $DB->executeQuery("UPDATE users SET posledne_prihlasenie = NOW() WHERE id = ?", [$uzivatel["id"]]);

    return self::generuj_json_odpoved(\API::odpoved($uzivatel), $response);
  }

}
