<?php

function controller_user($act, $d)
{
    if ($act == 'create_window') return User::create_window($d);
    if ($act == 'store') return User::store($d);
    if ($act == 'destroy_window') return User::user_destroy_window($d);
    if ($act == 'user_destroy') return User::user_destroy($d);
    return '';
}
