<?php

use Laravel\Lumen\Routing\Router;

/** @var Router $router */
$router->get('/', ["uses" => "Controller@index", "as" => "index"]);

// PASTE
$router->get('/paste', ["uses" => "PasteController@getPasteList", "as" => "paste.list"]);
$router->post('/paste', ["uses" => "PasteController@createPaste", "as" => "paste.create"]);
$router->get('/paste/{token}', ["uses" => "PasteController@getPaste", "as" => "paste.get"]);
$router->post('/paste/{token}', ["uses" => "PasteController@editPaste", "as" => "paste.edit"]); // todo [admin]
$router->delete('/paste/{token}', ["uses" => "PasteController@deletePaste", "as" => "paste.deletePaste"]);
