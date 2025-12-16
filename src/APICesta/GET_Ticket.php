<?php

namespace AlfaDesk\API\APICesta;

use DateTime;
use SYS;
use AlfaDesk\API\APICesta;
use AlfaDesk\API\Ticket;

class GET_Ticket extends APICesta {
  static public function spusti(\Slim\Psr7\Request $request, \Slim\Psr7\Response $response, array $args) : \Slim\Psr7\Response {
    global $DB, $LOG;

    if(!SYS::je_prihlaseny()) return self::generuj_json_odpoved(\API::odpoved_chyba_prihlasenie(), $response, 401);

    try {
      $params = (array)json_decode($request->getBody());
    } catch(\Exception $e) {
      $LOG->error("Chyba pri parsovaní JSON tela požiadavky");
      $params = [];
    }

    $prihlaseny = $_SESSION["uzivatel"];
    $je_admin = $prihlaseny["rola"] === "admin";

    // Filtre parametrov
    $vyriesene = isset($params["vyriesene"]) ? (bool)$params["vyriesene"] : null;
    $datum_od = isset($params["datum_od"]) ? new DateTime($params["datum_od"]) : null;
    $datum_do = isset($params["datum_do"]) ? new DateTime($params["datum_do"]) : null;
    $limit = isset($params["limit"]) && is_numeric($params["limit"]) ? (int)$params["limit"] : 50;
    $stranka = isset($params["stranka"]) && is_numeric($params["stranka"]) ? (int)$params["stranka"] : 1;
    
    // Filtre pouzivatelov - admin moze menit, user automaticky len svoje vytvorene tickety
    if($je_admin) {
      $vytvoril_id = isset($params["vytvoril"]) && is_numeric($params["vytvoril"]) ? (int)$params["vytvoril"] : null;
      $vyriesil_id = isset($params["vyriesil"]) && is_numeric($params["vyriesil"]) ? (int)$params["vyriesil"] : null;
    } else {
      $vytvoril_id = $prihlaseny["id"];
      $vyriesil_id = null;
    }

    // Zostavenie SQL dotazu
    $where = [];
    $vazby = [];

    if($vytvoril_id !== null) {
      $where[] = "vytvoril_id = ?";
      $vazby[] = $vytvoril_id;
    }

    if($vyriesil_id !== null) {
      $where[] = "vyriesil_id = ?";
      $vazby[] = $vyriesil_id;
    }

    if($vyriesene !== null) {
      $where[] = "vyriesene = ?";
      $vazby[] = $vyriesene ? 1 : 0;
    }

    if($datum_od !== null) {
      $where[] = "vytvoreny >= ?";
      $vazby[] = $datum_od->format("Y-m-d H:i:s");
    }

    if($datum_do !== null) {
      $where[] = "vytvoreny <= ?";
      $vazby[] = $datum_do->format("Y-m-d H:i:s");
    }

    $whereKlauzula = count($where) > 0 ? "WHERE " . join(" AND ", $where) : "";
    $offset = ($stranka - 1) * $limit;

    try {
      $sql_cnt = "SELECT COUNT(id) AS cnt FROM tickets {$whereKlauzula}";
      $pocetVysledkov = $DB->fetchAssociative($sql_cnt, $vazby);
      $pocetVysledkov = $pocetVysledkov ? (int)$pocetVysledkov["cnt"] : 0;

      $sql = "SELECT * FROM tickets {$whereKlauzula} ORDER BY vytvoreny DESC LIMIT ? OFFSET ?";
      $vazby[] = $limit;
      $vazby[] = $offset;
      
      $vysledky = $DB->fetchAllAssociative($sql, $vazby);
      
      $tickety = [];
      foreach($vysledky as $data) {
        $ticket = new Ticket();
        $ticket->id = (int)$data["id"];
        $ticket->fronta = \AlfaDesk\API\Fronta::NacitajPodlaID((int)$data["fronta_id"]);
        $ticket->zariadenie = $data["zariadenie_id"] !== null ? \AlfaDesk\API\Zariadenie::NacitajPodlaID((int)$data["zariadenie_id"]) : null;
        $ticket->zariadenie_nazov = $data["zariadenie_nazov"] ?: "";
        $ticket->umiestnenie = $data["umiestnenie"] ?: "";
        $ticket->popis = $data["popis"] ?: "";
        $ticket->odstranenie_predpoklad = $data["odstranenie_predpoklad"] ? new DateTime($data["odstranenie_predpoklad"]) : null;
        $ticket->odstranenie_skutocne = $data["odstranenie_skutocne"] ? new DateTime($data["odstranenie_skutocne"]) : null;
        $ticket->zaciatok_riesenia = $data["zaciatok_riesenia"] ? new DateTime($data["zaciatok_riesenia"]) : null;
        $ticket->koniec_riesenia = $data["koniec_riesenia"] ? new DateTime($data["koniec_riesenia"]) : null;
        $ticket->vyriesene = (bool)$data["vyriesene"];
        $ticket->nh_predpoklad = (float)$data["nh_predpoklad"];
        $ticket->nh_skutocne = (float)$data["nh_skutocne"];
        $ticket->vyriesil = $data["vyriesil_id"] !== null ? \AlfaDesk\API\Uzivatel::NacitajPodlaID((int)$data["vyriesil_id"]) : null;
        $ticket->popis_riesenia = $data["popis_riesenia"] ?: "";
        $ticket->vytvorene_cas = new DateTime($data["vytvoreny"]);
        $ticket->vytvoril = \AlfaDesk\API\Uzivatel::NacitajPodlaID((int)$data["vytvoril_id"]);
        
        $tickety[] = $ticket->DoArray();
      }

      return self::generuj_json_odpoved(\API::odpoved([
        "tickety" => $tickety,
        "stranka" => $stranka,
        "limit" => $limit,
        "pocet" => $pocetVysledkov
      ]), $response);
      
    } catch(\Exception $e) {
      $LOG->error("Chyba pri načítaní ticketov: ".$e->getMessage());
      return self::generuj_json_odpoved(\API::odpoved_chyba(999, $e->getMessage()), $response, 500);
    }
  }
}
