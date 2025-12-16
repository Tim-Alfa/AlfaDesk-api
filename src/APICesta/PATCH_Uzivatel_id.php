<?php

namespace AlfaDesk\API\APICesta;

use SYS;
use AlfaDesk\API\APICesta;
use AlfaDesk\API\Uzivatel;

class PATCH_Uzivatel_id extends APICesta {
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

    try {
      $params = (array)json_decode($request->getBody());
    } catch(\Exception $e) {
      $LOG->error("Chyba pri parsovaní JSON tela požiadavky");
      throw $e;
    }

    $updateData = [
      "meno" => $params["meno"] ?? $uzivatel->meno,
      "priezvisko" => $params["priezvisko"] ?? $uzivatel->priezvisko,
      "cele_meno" => $params["cele_meno"] ?? $uzivatel->cele_meno,
      "osobne_cislo" => $params["osobne_cislo"] ?? $uzivatel->osobne_cislo,
      "email" => $params["email"] ?? $uzivatel->email,
      "telefonne_cislo" => $params["telefonne_cislo"] ?? $uzivatel->telefonne_cislo,
      "pozicia" => $params["pozicia"] ?? $uzivatel->pozicia,
      "rola" => $params["rola"] ?? $uzivatel->rola,
      "aktivny" => isset($params["aktivny"]) ? ($params["aktivny"] ? 1 : 0) : ($uzivatel->aktivny ? 1 : 0)
    ];

    if(isset($params["heslo"]) && $params["heslo"]) {
      $updateData["heslo"] = password_hash($params["heslo"], PASSWORD_DEFAULT);
    }

    $DB->executeQuery("UPDATE users SET
      meno = ?,
      priezvisko = ?,
      cele_meno = ?,
      osobne_cislo = ?,
      email = ?,
      telefonne_cislo = ?,
      pozicia = ?,
      rola = ?,
      aktivny = ?" . (isset($updateData["heslo"]) ? ",
      heslo = ?" : "") . "
      WHERE id = ?", array_merge(
        [
          $updateData["meno"],
          $updateData["priezvisko"],
          $updateData["cele_meno"],
          $updateData["osobne_cislo"],
          $updateData["email"],
          $updateData["telefonne_cislo"],
          $updateData["pozicia"],
          $updateData["rola"],
          $updateData["aktivny"]
        ],
        isset($updateData["heslo"]) ? [$updateData["heslo"]] : [],
        [$uzivatel->id]
      ));

    return self::generuj_json_odpoved(\API::odpoved($uzivatel->Obnov()->DoArray()), $response);
  }
}
