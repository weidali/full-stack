<?php

// INIT
const APP_ENV = 'dev'; // 'general'

require('./cfg/' . strtolower(APP_ENV) . '.inc.php');
require('./includes/core/functions.php');

init_classes();
init_controllers_common();

DB::connect();

// SESSION

Session::init();
Route::init();

$g['path'] = Route::$path;
HTML::assign('global', $g);
HTML::display('./partials/index.html');
