<?php

$LINK = $_SERVER['PHP_SELF'];
$link_array = explode("/", $LINK);
$stranka = end($link_array);
$stranka = str_replace(".php", "", $stranka);


class SYS {

  static function assert_prihlaseny() {
    global $URL_SYSTEMU;
    if(!self::je_prihlaseny()) {
      header("Location: {$URL_SYSTEMU}/prihlasenie.php");
      ob_end_clean();
      die();
    }
  }

  static function assert_odhlaseny() {
    global $URL_SYSTEMU;
    if(self::je_prihlaseny()) {
      header("Location: {$URL_SYSTEMU}/plocha.php");
      ob_end_clean();
      die();
    }
  }

  static function ziskaj_moznost($kluc) {
    global $DB;

    return $DB->fetchOne("SELECT hodnota FROM moznosti WHERE kluc = ?", [$kluc]);
  }

  static function zapis_moznost($kluc, $hodnota) {
    global $DB;

    if($DB->fetchOne("SELECT id FROM moznosti WHERE kluc = ?", [$kluc])) {
      return $DB->update("moznosti", [
        "hodnota" => $hodnota,
        "posledna_uprava_cas" => (new DateTime("now"))->format("Y-m-d H:i:s"),
        "posledna_uprava_uziv" => is_array($_SESSION["uzivatel"] ?? false) ? ($_SESSION["uzivatel"]["id"] ?? null) : null
      ], [
        "kluc" => $kluc
      ]);
    } else {
      return $DB->insert("moznosti", [
        "kluc" => $kluc,
        "hodnota" => $hodnota,
        "posledna_uprava_cas" => (new DateTime("now"))->format("Y-m-d H:i:s"),
        "posledna_uprava_uziv" => is_array($_SESSION["uzivatel"] ?? false) ? ($_SESSION["uzivatel"]["id"] ?? null) : null
      ]);
    }
  }

  static function je_prihlaseny() {
    return (
      ($_SESSION["prihlaseny"] ?? false) &&
      (is_array($_SESSION["uzivatel"] ?? false))
    );
  }
  
  static function uuidv4() {
    $data = random_bytes(16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
      
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
  }
}