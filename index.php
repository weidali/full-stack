<?php

// INIT
const APP_ENV = 'general'; // 'general'

require('./cfg/' . strtolower(APP_ENV) . '.inc.php');
require('./includes/core/functions.php');

init_classes();
init_controllers_common();

DB::connect();

// SESSION

Session::init();
Route::init();

$g['path'] = Route::$path;
$g['users_count'] = User::users_count();
$g['plots_count'] = Plot::plots_count();
HTML::assign('global', $g);
HTML::display('./partials/index.html');
