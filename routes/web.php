<?php

use Laravel\Lumen\Routing\Router;

/** @var Router $router */
$router->get('/', ["uses" => "Controller@index", "as" => "index"]);

// PASTE
$router->get('/paste', ["uses" => "PasteController@getPasteList", "as" => "paste.getPasteList"]);
$router->post('/paste', ["uses" => "PasteController@createPaste", "as" => "paste.createPaste"]);
$router->get('/paste/{token}', ["uses" => "PasteController@getPaste", "as" => "paste.getPaste"]);
$router->post('/paste/{token}', ["uses" => "PasteController@editPaste", "as" => "paste.editPaste"]); // todo
$router->delete('/paste/{token}', ["uses" => "PasteController@deletePaste", "as" => "paste.deletePaste"]);
$router->get('/paste/{token}/comment', ["uses" => "PasteController@getPasteCommentList", "as" => "paste.getPasteCommentList"]);
$router->post('/paste/{token}/comment', ["uses" => "PasteController@createPasteComment", "as" => "paste.createPasteComment"]);
$router->get('/paste/{token}/comment/{comment}', ["uses" => "PasteController@getPasteComment", "as" => "paste.getPasteComment"]);
$router->post('/paste/{token}/comment/{comment}', ["uses" => "PasteController@editPasteComment", "as" => "paste.editPasteComment"]); // todo
$router->delete('/paste/{token}/comment/{comment}', ["uses" => "PasteController@deletePasteComment", "as" => "paste.deletePasteComment"]); // todo

// LANGUAGE
$router->get('/language', ["uses" => "LanguageController@getLanguageList", "as" => "language.getLanguageList"]); // todo
$router->post('/language', ["uses" => "LanguageController@createLanguage", "as" => "language.createLanguage"]); // todo
$router->get('/language/{slug}', ["uses" => "LanguageController@getLanguage", "as" => "language.getLanguage"]); // todo
$router->post('/language/{slug}', ["uses" => "LanguageController@editLanguage", "as" => "language.editLanguage"]); // todo
$router->delete('/language/{slug}', ["uses" => "LanguageController@deleteLanguage", "as" => "language.deleteLanguage"]); // todo
