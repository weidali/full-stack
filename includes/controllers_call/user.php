<?php

function controller_user($act, $d)
{
    if ($act == 'destroy_window') return User::user_destroy_window($d);
    if ($act == 'user_destroy') return User::user_destroy($d);
    return '';
}
