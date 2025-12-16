<?php

namespace AlfaDesk\API;

class Zariadenie extends APIObjekt {
  public int $id;
  public string $nazov;
  public float $nh_predpoklad;
  public float $nh_priemer;

  public static function NacitajPodlaID(int $id) : ?self {
    global $DB;

    $data = $DB->fetchAssociative("SELECT * FROM devices WHERE id = ?", [$id]);
    if(!$data) return null;

    $zariadenie = new self();
    $zariadenie->id = (int)$data["id"];
    $zariadenie->nazov = $data["nazov"];
    $zariadenie->nh_predpoklad = (float)$data["nh_predpoklad"];
    $zariadenie->nh_priemer = (float)$data["nh_priemer"];

    return $zariadenie;
  }

  public function DoArray() : array {
    return [
      "id" => $this->id,
      "nazov" => $this->nazov,
      "nh_predpoklad" => $this->nh_predpoklad,
      "nh_priemer" => $this->nh_priemer
    ];
  }
}