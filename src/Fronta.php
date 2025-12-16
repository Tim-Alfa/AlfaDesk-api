<?php

namespace AlfaDesk\API;

class Fronta extends APIObjekt {
  public int $id;
  public string $nazov;

  public static function NacitajPodlaID(int $id) : ?self {
    global $DB;

    $data = $DB->fetchAssociative("SELECT * FROM queues WHERE id = ?", [$id]);
    if(!$data) return null;

    $fronta = new self();
    $fronta->id = (int)$data["id"];
    $fronta->nazov = $data["nazov"];

    return $fronta;
  }

  public function DoArray() : array {
    return [
      "id" => $this->id,
      "nazov" => $this->nazov
    ];
  }
}