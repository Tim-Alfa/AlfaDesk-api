<?php

namespace AlfaDesk\API\APICesta;

use DateTime;
use SYS;
use AlfaDesk\API\APICesta;
use AlfaDesk\API\Ticket;

class PATCH_Ticket_id extends APICesta {
  static public function spusti(\Slim\Psr7\Request $request, \Slim\Psr7\Response $response, array $args) : \Slim\Psr7\Response {
    global $DB, $LOG;

    if(!SYS::je_prihlaseny()) return self::generuj_json_odpoved(\API::odpoved_chyba_prihlasenie(), $response, 401);

    $id = $args["id"] ?? 0;
    if(!$id || !is_numeric($id) || $id <= 0) {
      return self::generuj_json_odpoved(\API::odpoved_chyba_parametre(), $response, 500);
    }

    $ticket = Ticket::NacitajPodlaID((int)$id);
    if(!$ticket) {
      return self::generuj_json_odpoved(\API::odpoved_chyba(404, "Ticket so zadaným ID neexistuje"), $response, 404);
    }

    $prihlaseny = $_SESSION["uzivatel"];
    if($ticket->vytvoril->id !== $prihlaseny["id"] && $prihlaseny["rola"] === "uzivatel") {
      return self::generuj_json_odpoved(\API::odpoved_chyba(403, "Nemáte oprávnenie upravovať tento ticket"), $response, 403);
    }

    try {
      $params = (array)json_decode($request->getBody());
    } catch(\Exception $e) {
      $LOG->error("Chyba pri parsovaní JSON tela požiadavky");
      throw $e;
    }

    $DB->executeQuery("UPDATE tickets SET
      fronta_id = ?,
      zariadenie_id = ?,
      zariadenie_nazov = ?,
      umiestnenie = ?,
      popis = ?,
      odstranenie_predpoklad = ?,
      odstranenie_skutocne = ?,
      zaciatok_riesenia = ?,
      koniec_riesenia = ?,
      vyriesene = ?,
      nh_predpoklad = ?,
      nh_skutocne = ?,
      vyriesil_id = ?,
      popis_riesenia = ?
      WHERE id = ?", [
        $params["fronta"] ?? $ticket->fronta->id,
        $params["zariadenie"] ?? ($ticket->zariadenie ? $ticket->zariadenie->id : null),
        $params["zariadenie_nazov"] ?? $ticket->zariadenie_nazov,
        $params["umiestnenie"] ?? $ticket->umiestnenie,
        $params["popis"] ?? $ticket->popis,
        isset($params["odstranenie_predpoklad"]) ? (new DateTime($params["odstranenie_predpoklad"]))->format("Y-m-d H:i:s") : ($ticket->odstranenie_predpoklad ? $ticket->odstranenie_predpoklad->format("Y-m-d H:i:s") : null),
        isset($params["odstranenie_skutocne"]) ? (new DateTime($params["odstranenie_skutocne"]))->format("Y-m-d H:i:s") : ($ticket->odstranenie_skutocne ? $ticket->odstranenie_skutocne->format("Y-m-d H:i:s") : null),
        isset($params["zaciatok_riesenia"]) ? (new DateTime($params["zaciatok_riesenia"]))->format("Y-m-d H:i:s") : ($ticket->zaciatok_riesenia ? $ticket->zaciatok_riesenia->format("Y-m-d H:i:s") : null),
        isset($params["koniec_riesenia"]) ? (new DateTime($params["koniec_riesenia"]))->format("Y-m-d H:i:s") : ($ticket->koniec_riesenia ? $ticket->koniec_riesenia->format("Y-m-d H:i:s") : null),
        isset($params["vyriesene"]) ? ($params["vyriesene"] ? 1 : 0) : ($ticket->vyriesene ? 1 : 0),
        $params["nh_predpoklad"] ?? $ticket->nh_predpoklad,
        $params["nh_skutocne"] ?? $ticket->nh_skutocne,
        $params["vyriesil"] ?? ($ticket->vyriesil ? $ticket->vyriesil->id : null),
        $params["popis_riesenia"] ?? $ticket->popis_riesenia,
        $ticket->id
      ]);

    return self::generuj_json_odpoved(\API::odpoved($ticket->Obnov()->DoArray()), $response);
  }
}