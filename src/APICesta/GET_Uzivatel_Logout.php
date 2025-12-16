<?php

namespace AlfaDesk\API\APICesta;

use SYS;
use AlfaDesk\API\APICesta;

class GET_Uzivatel_Logout extends APICesta {

  static public function spusti(\Slim\Psr7\Request $request, \Slim\Psr7\Response $response, array $args) : \Slim\Psr7\Response {

    if(!SYS::je_prihlaseny()) {
      return self::generuj_json_odpoved(\API::odpoved_chyba(102, "Užívateľ nie je prihlásený"), $response, 401);
    }

    $_SESSION["prihlaseny"] = false;
    unset($_SESSION["uzivatel"]);

    return self::generuj_json_odpoved(\API::odpoved([]), $response);
  }

}