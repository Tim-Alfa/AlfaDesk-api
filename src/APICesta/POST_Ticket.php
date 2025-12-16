<?php

namespace AlfaDesk\API\APICesta;

use DateTime;
use SYS;
use AlfaDesk\API\APICesta;
use AlfaDesk\API\Ticket;

class POST_Ticket extends APICesta {
  static public function spusti(\Slim\Psr7\Request $request, \Slim\Psr7\Response $response, array $args) : \Slim\Psr7\Response {
    global $DB, $LOG;

    if(!SYS::je_prihlaseny()) return self::generuj_json_odpoved(\API::odpoved_chyba_prihlasenie(), $response, 401);

    try {
      $params = (array)json_decode($request->getBody());
    } catch(\Exception $e) {
      $LOG->error("Chyba pri parsovaní JSON tela požiadavky");
      throw $e;
    }

    $fronta_id = $params["fronta"] ?? 0;
    $zariadenie_id = $params["zariadenie"] ?? 0;
    $zariadenie_nazov = $params["zariadenie_nazov"] ?? "";
    $umiestnenie = $params["umiestnenie"] ?? "";
    $popis = $params["popis"] ?? "";
    $odstranenie_predpoklad = isset($params["odstranenie_predpoklad"]) ? new DateTime($params["odstranenie_predpoklad"]) : null;
    $odstranenie_skutocne = isset($params["odstranenie_skutocne"]) ? new DateTime($params["odstranenie_skutocne"]) : null;
    $zaciatok_riesenia = isset($params["zaciatok_riesenia"]) ? new DateTime($params["zaciatok_riesenia"]) : null;
    $koniec_riesenia = isset($params["koniec_riesenia"]) ? new DateTime($params["koniec_riesenia"]) : null;
    $vyriesene = $params["vyriesene"] ?? false;
    $nh_predpoklad = $params["nh_predpoklad"] ?? 0;
    $nh_skutocne = $params["nh_skutocne"] ?? 0;
    $vyriesil_uzivatel_id = $params["vyriesil"] ?? null;
    $popis_riesenia = $params["popis_riesenia"] ?? "";

    if(!$fronta_id || !$popis ||!$zariadenie_id) {
      return self::generuj_json_odpoved(\API::odpoved_chyba_parametre(), $response, 500);
    }

    try {
      $DB->insert("tickets", [
        "fronta_id" => $fronta_id,
        "zariadenie_id" => $zariadenie_id,
        "zariadenie_nazov" => $zariadenie_nazov,
        "umiestnenie" => $umiestnenie,
        "popis" => $popis,
        "odstranenie_predpoklad" => $odstranenie_predpoklad ? $odstranenie_predpoklad->format("Y-m-d H:i:s") : null,
        "odstranenie_skutocne" => $odstranenie_skutocne ? $odstranenie_skutocne->format("Y-m-d H:i:s") : null,
        "zaciatok_riesenia" => $zaciatok_riesenia ? $zaciatok_riesenia->format("Y-m-d H:i:s") : null,
        "koniec_riesenia" => $koniec_riesenia ? $koniec_riesenia->format("Y-m-d H:i:s") : null,
        "vyriesene" => $vyriesene ? 1 : 0,
        "nh_predpoklad" => $nh_predpoklad,
        "nh_skutocne" => $nh_skutocne,
        "vyriesil_id" => $vyriesil_uzivatel_id,
        "popis_riesenia" => $popis_riesenia,
        "vytvoril_id" => $_SESSION["uzivatel"]["id"],
      ]);
    } catch(\Exception $e) {
      $LOG->error("Chyba pri vkladaní nového ticketu do databázy: ".$e->getMessage());
      return self::generuj_json_odpoved(\API::odpoved_chyba(999, $e->getMessage()), $response, 500);
    }

    $ticket = Ticket::NacitajPodlaID((int)$DB->lastInsertId());
    return self::generuj_json_odpoved(\API::odpoved($ticket->DoArray()), $response);
  }
}