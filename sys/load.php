<?php declare(strict_types=1);
session_start();

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


require __DIR__."/../vendor/autoload.php";

require __DIR__."/../sys/params.php";
require __DIR__."/../config.php";

require __DIR__."/sys.php";
require __DIR__."/api.php";

$LOG = new Logger("AlfaDeskAPI");
$LOG->pushHandler(new StreamHandler($LOGGING_PATH, $LOGGING_LEVEL));

set_exception_handler(static function(Throwable $ex) use($LOG) {
  $LOG->error("[RUNTIME ERROR] ".$ex->getMessage(), ["kod" => $ex->getCode(), "subor" => $ex->getFile(), "riadok" => $ex->getLine(), "trace" => $ex->getTrace()]);
  http_response_code(500);
});

$db_dsnParser = new DsnParser();
$db_connParams = $db_dsnParser->parse($DB_DSN);

$DB = DriverManager::getConnection($db_connParams);

ob_start();