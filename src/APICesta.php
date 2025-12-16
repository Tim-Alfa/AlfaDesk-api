<?php

namespace AlfaDesk\API;

use Slim\Psr7\Request;
use Slim\Psr7\Response;

class APICesta {

  protected static function generuj_json_odpoved(string $json_string, Response $response, int $resp_code = 200) : Response {
    $response->getBody()->write($json_string);
    return $response->withHeader('Content-Type', 'application/json')->withStatus($resp_code);
  }

  static public function spusti(Request $request, Response $response, array $args) : Response {
    return self::generuj_json_odpoved(\API::odpoved_chyba_neimplementovane(), $response);
  }

}