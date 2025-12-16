<?php

namespace AlfaDesk\API;

use AlfaDesk\API\APICesta\GET_Ticket;
use AlfaDesk\API\APICesta\GET_Ticket_id;
use AlfaDesk\API\APICesta\POST_Ticket;
use AlfaDesk\API\APICesta\GET_Fronta_id;
use AlfaDesk\API\APICesta\POST_Fronta;
use AlfaDesk\API\APICesta\PATCH_Fronta_id;
use AlfaDesk\API\APICesta\DELETE_Fronta_id;
use AlfaDesk\API\APICesta\GET_Zariadenie_id;
use AlfaDesk\API\APICesta\POST_Zariadenie;
use AlfaDesk\API\APICesta\PATCH_Zariadenie_id;
use AlfaDesk\API\APICesta\DELETE_Zariadenie_id;
use AlfaDesk\API\APICesta\GET_Uzivatel_id;
use AlfaDesk\API\APICesta\POST_Uzivatel;
use AlfaDesk\API\APICesta\PATCH_Uzivatel_id;
use AlfaDesk\API\APICesta\DELETE_Uzivatel_id;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Slim\Psr7\Response as SlimResponse;

require __DIR__ . "/sys/load.php";

$app = AppFactory::create();
$app->addRoutingMiddleware();

$app->setBasePath('/php/AlfaDesk-api/AlfaDesk-api');

$app->get("/uzivatel/login", function ($request, $response, $args) {
  return \AlfaDesk\API\APICesta\GET_Uzivatel_Login::spusti($request, $response, $args);
});
$app->get("/uzivatel/logout", function ($request, $response, $args) {
  return \AlfaDesk\API\APICesta\GET_Uzivatel_Logout::spusti($request, $response, $args);
});
$app->get("/ticket", function ($request, $response, $args) {
  return GET_Ticket::spusti($request, $response, $args);
});
$app->post("/ticket", function ($request, $response, $args) {
  return POST_Ticket::spusti($request, $response, $args);
});
$app->get("/ticket/{id}", function ($request, $response, $args) {
  return GET_Ticket_id::spusti($request, $response, $args);
});
$app->patch("/ticket/{id}", function ($request, $response, $args) {
  return \AlfaDesk\API\APICesta\PATCH_Ticket_id::spusti($request, $response, $args);
});

$app->get("/fronta/{id}", function ($request, $response, $args) {
  return GET_Fronta_id::spusti($request, $response, $args);
});
$app->post("/fronta", function ($request, $response, $args) {
  return POST_Fronta::spusti($request, $response, $args);
});
$app->patch("/fronta/{id}", function ($request, $response, $args) {
  return PATCH_Fronta_id::spusti($request, $response, $args);
});
$app->delete("/fronta/{id}", function ($request, $response, $args) {
  return DELETE_Fronta_id::spusti($request, $response, $args);
});

$app->get("/zariadenie/{id}", function ($request, $response, $args) {
  return GET_Zariadenie_id::spusti($request, $response, $args);
});
$app->post("/zariadenie", function ($request, $response, $args) {
  return POST_Zariadenie::spusti($request, $response, $args);
});
$app->patch("/zariadenie/{id}", function ($request, $response, $args) {
  return PATCH_Zariadenie_id::spusti($request, $response, $args);
});
$app->delete("/zariadenie/{id}", function ($request, $response, $args) {
  return DELETE_Zariadenie_id::spusti($request, $response, $args);
});

$app->get("/uzivatel/{id}", function ($request, $response, $args) {
  return GET_Uzivatel_id::spusti($request, $response, $args);
});
$app->post("/uzivatel", function ($request, $response, $args) {
  return POST_Uzivatel::spusti($request, $response, $args);
});
$app->patch("/uzivatel/{id}", function ($request, $response, $args) {
  return PATCH_Uzivatel_id::spusti($request, $response, $args);
});
$app->delete("/uzivatel/{id}", function ($request, $response, $args) {
  return DELETE_Uzivatel_id::spusti($request, $response, $args);
});

$errorMiddleware = $app->addErrorMiddleware(false, false, false);

$customErrorHandler = function (
  ServerRequestInterface $request,
  Throwable $exception,
  bool $displayErrorDetails,
  bool $logErrors,
  bool $logErrorDetails
) : ResponseInterface {
  global $LOG;
  $trace = \SYS::uuidv4();
  $LOG->error("[ FATAL ]: " . $trace . " - " . $exception->getMessage(), ["trace" => $exception->getTraceAsString(), "file" => $exception->getFile(), "line" => $exception->getLine()]);
  $resp = new SlimResponse();
  $resp->getBody()->write(\API::odpoved_chyba(9999, "Nastala neočakávana chyba. Kontaktujte administrátora pre viac informácií.", $trace));
  return $resp->withHeader('Content-Type', 'application/json')->withStatus(500);
};

$errorMiddleware->setDefaultErrorHandler($customErrorHandler);
$app->run();