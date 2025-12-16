<?php

namespace AlfaDesk\API;

abstract class APIObjekt {
  public function DoArray() : array {
    return get_object_vars($this);
  }
  
  public static function NacitajPodlaID(int $id) : ?self {
    throw new \Exception("Neimplementovaná metóda! Kontaktujte administrátora.");
  }

  public function Obnov() : self {
    return static::NacitajPodlaID($this->id);
  }
}