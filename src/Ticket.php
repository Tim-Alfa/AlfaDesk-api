<?php

namespace AlfaDesk\API;

class Ticket extends APIObjekt {
  public int $id;
  public Fronta $fronta;
  public ?Zariadenie $zariadenie;
  public string $zariadenie_nazov;
  public string $umiestnenie;
  public string $popis;
  public ?\DateTime $odstranenie_predpoklad;
  public ?\DateTime $odstranenie_skutocne;
  public ?\DateTime $zaciatok_riesenia;
  public ?\DateTime $koniec_riesenia;
  public bool $vyriesene;
  public float $nh_predpoklad;
  public float $nh_skutocne;
  public ?Uzivatel $vyriesil;
  public string $popis_riesenia;
  public \DateTime $vytvorene_cas;
  public Uzivatel $vytvoril;

  public static function NacitajPodlaID(int $id) : ?self {
    global $DB;

    $data = $DB->fetchAssociative("SELECT * FROM tickets WHERE id = ?", [$id]);
    if(!$data) return null;

    $ticket = new self();
    $ticket->id = (int)$data["id"];
    $ticket->fronta = Fronta::NacitajPodlaID((int)$data["fronta_id"]);
    $ticket->zariadenie = $data["zariadenie_id"] !== null ? Zariadenie::NacitajPodlaID((int)$data["zariadenie_id"]) : null;
    $ticket->zariadenie_nazov = $data["zariadenie_nazov"] ?: "";
    $ticket->umiestnenie = $data["umiestnenie"] ?: "";
    $ticket->popis = $data["popis"] ?: "";
    $ticket->odstranenie_predpoklad = $data["odstranenie_predpoklad"] ? new \DateTime($data["odstranenie_predpoklad"]) : null;
    $ticket->odstranenie_skutocne = $data["odstranenie_skutocne"] ? new \DateTime($data["odstranenie_skutocne"]) : null;
    $ticket->zaciatok_riesenia = $data["zaciatok_riesenia"] ? new \DateTime($data["zaciatok_riesenia"]) : null;
    $ticket->koniec_riesenia = $data["koniec_riesenia"] ? new \DateTime($data["koniec_riesenia"]) : null;
    $ticket->vyriesene = (bool)$data["vyriesene"];
    $ticket->nh_predpoklad = (float)$data["nh_predpoklad"];
    $ticket->nh_skutocne = (float)$data["nh_skutocne"];
    $ticket->vyriesil = $data["vyriesil_id"] !== null ? Uzivatel::NacitajPodlaID((int)$data["vyriesil_id"]) : null;
    $ticket->popis_riesenia = $data["popis_riesenia"] ?: "";
    $ticket->vytvorene_cas = new \DateTime($data["vytvoreny"]);
    $ticket->vytvoril = Uzivatel::NacitajPodlaID((int)$data["vytvoril_id"]);

    return $ticket;
  }

  public function DoArray() : array {
    return [
      "id" => $this->id,
      "fronta" => $this->fronta->DoArray(),
      "zariadenie" => $this->zariadenie ? $this->zariadenie->DoArray() : null,
      "zariadenie_nazov" => $this->zariadenie_nazov,
      "umiestnenie" => $this->umiestnenie,
      "popis" => $this->popis,
      "odstranenie_predpoklad" => $this->odstranenie_predpoklad ? $this->odstranenie_predpoklad->format("Y-m-d H:i:s") : null,
      "odstranenie_skutocne" => $this->odstranenie_skutocne ? $this->odstranenie_skutocne->format("Y-m-d H:i:s") : null,
      "zaciatok_riesenia" => $this->zaciatok_riesenia ? $this->zaciatok_riesenia->format("Y-m-d H:i:s") : null,
      "koniec_riesenia" => $this->koniec_riesenia ? $this->koniec_riesenia->format("Y-m-d H:i:s") : null,
      "vyriesene" => $this->vyriesene,
      "nh_predpoklad" => $this->nh_predpoklad,
      "nh_skutocne" => $this->nh_skutocne,
      "vyriesil" => $this->vyriesil ? $this->vyriesil->DoArraySkratene() : null,
      "popis_riesenia" => $this->popis_riesenia,
      "vytvorene_cas" => $this->vytvorene_cas->format("Y-m-d H:i:s"),
      "vytvoril" => $this->vytvoril->DoArraySkratene(),
    ];
  }
}