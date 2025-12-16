<?php

namespace AlfaDesk\API;

use DateTime;

class Uzivatel extends APIObjekt {
  public int $id;
  public ?string $meno;
  public ?string $priezvisko;
  public ?string $cele_meno;
  public ?string $osobne_cislo;
  public ?string $email;
  public ?string $telefonne_cislo;
  public ?string $pozicia;
  public string $rola;
  public bool $aktivny;
  public ?DateTime $posledne_prihlasenie;
  public ?DateTime $posledna_zmena_hesla;
  public ?DateTime $vytvoreny;
  public ?DateTime $aktualizovany;

  public static function NacitajPodlaID(int $id) : ?self {
    global $DB;

    $data = $DB->fetchAssociative("SELECT * FROM users WHERE id = ?", [$id]);
    if(!$data) return null;

    $uzivatel = new self();
    $uzivatel->meno = $data["meno"];
    $uzivatel->priezvisko = $data["priezvisko"];
    $uzivatel->cele_meno = $data["cele_meno"];
    $uzivatel->osobne_cislo = $data["osobne_cislo"];
    $uzivatel->email = $data["email"];
    $uzivatel->telefonne_cislo = $data["telefonne_cislo"];
    $uzivatel->pozicia = $data["pozicia"];
    $uzivatel->rola = $data["rola"];
    $uzivatel->aktivny = (bool)$data["aktivny"];
    $uzivatel->posledne_prihlasenie = $data["posledne_prihlasenie"] ? new DateTime($data["posledne_prihlasenie"]) : null;
    $uzivatel->posledna_zmena_hesla = $data["posledna_zmena_hesla"] ? new DateTime($data["posledna_zmena_hesla"]) : null;
    $uzivatel->vytvoreny = $data["vytvoreny"] ? new DateTime($data["vytvoreny"]) : null;
    $uzivatel->aktualizovany = $data["aktualizovany"] ? new DateTime($data["aktualizovany"]) : null;
    $uzivatel->id = (int)$data["id"];

    return $uzivatel;
  }

  public function DoArray() : array {
    return [
      "id" => $this->id,
      "meno" => $this->meno,
      "priezvisko" => $this->priezvisko,
      "cele_meno" => $this->cele_meno,
      "osobne_cislo" => $this->osobne_cislo,
      "email" => $this->email,
      "telefonne_cislo" => $this->telefonne_cislo,
      "pozicia" => $this->pozicia,
      "rola" => $this->rola,
      "aktivny" => $this->aktivny,
      "posledne_prihlasenie" => $this->posledne_prihlasenie ? $this->posledne_prihlasenie->format("Y-m-d H:i:s") : null,
      "posledna_zmena_hesla" => $this->posledna_zmena_hesla ? $this->posledna_zmena_hesla->format("Y-m-d H:i:s") : null,
      "vytvoreny" => $this->vytvoreny ? $this->vytvoreny->format("Y-m-d H:i:s") : null,
      "aktualizovany" => $this->aktualizovany ? $this->aktualizovany->format("Y-m-d H:i:s") : null,
    ];
  }

  public function DoArraySkratene() : array {
    return [
      "id" => $this->id,
      "meno" => $this->meno,
      "priezvisko" => $this->priezvisko,
      "cele_meno" => $this->cele_meno,
      "email" => $this->email,
      "rola" => $this->rola,
      "aktivny" => $this->aktivny,
    ];
  }
}