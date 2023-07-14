<?php

class User
{

    // GENERAL
    public static function user_info($d)
    {
        // vars
        $user_id = isset($d['user_id']) && is_numeric($d['user_id']) ? $d['user_id'] : 0;
        $phone = isset($d['phone']) ? preg_replace('~\D+~', '', $d['phone']) : 0;
        // where
        if ($user_id) $where = "user_id='" . $user_id . "'";
        else if ($phone) $where = "phone='" . $phone . "'";
        else return [];
        // info
        $q = DB::query("SELECT user_id, phone, access FROM users WHERE " . $where . " LIMIT 1;") or die(DB::error());
        if ($row = DB::fetch_row($q)) {
            return [
                'id' => (int) $row['user_id'],
                'access' => (int) $row['access']
            ];
        } else {
            return [
                'id' => 0,
                'access' => 0
            ];
        }
    }

    public static function users_list($d = [])
    {
        // vars
        $search = isset($d['search']) && trim($d['search']) ? $d['search'] : '';
        $offset = isset($d['offset']) && is_numeric($d['offset']) ? $d['offset'] : 0;
        $limit = 20;
        $items = [];
        // where
        $where = [];
        if ($search != '') $where[] = "phone LIKE '%$search%' OR email LIKE '%$search%' OR first_name LIKE '%$search%'";
        $where = $where ? "WHERE " . implode(" AND ", $where) : "";
        // info
        $q = DB::query(
            "SELECT user_id, plot_id, first_name, last_name, phone, email, last_login, updated
            FROM users 
            $where 
            ORDER BY user_id 
            LIMIT $limit
            OFFSET $offset;
            "
        ) or die(DB::error());
        while ($row = DB::fetch_row($q)) {
            $items[] = [
                'id' => (int) $row['user_id'],
                'plot_id' => $row['plot_id'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'phone' => $row['phone'],
                'email' => $row['email'],
                'last_login' => date('Y/m/d', $row['last_login']),
                'updated' => date('Y/m/d', $row['updated'])
            ];
        }
        // paginator
        $q = DB::query("SELECT count(*) FROM users " . $where . ";");
        $row = DB::fetch_row($q);
        $count = $row['count(*)'] ?? 0;
        $url = 'users';
        if ($search) $url .= '?search=' . $search . '&';
        paginator($count, $offset, $limit, $url, $paginator);

        return ['items' => $items, 'paginator' => $paginator];
    }

    public static function users_list_plots($number)
    {
        // vars
        $items = [];
        // info
        $q = DB::query("SELECT user_id, plot_id, first_name, email, phone
        FROM users WHERE plot_id = $number;");
        while ($row = DB::fetch_row($q)) {
            $plot_ids = explode(',', $row['plot_id']);
            $val = false;
            foreach ($plot_ids as $plot_id) if ($plot_id == $number) $val = true;
            if ($val) $items[] = [
                'id' => (int) $row['user_id'],
                'first_name' => $row['first_name'],
                'email' => $row['email'],
                'phone_str' => phone_formatting($row['phone'])
            ];
        }

        return $items;
    }

    public static function users_fetch($d = [])
    {
        $info = User::users_list($d);
        HTML::assign('users', $info['items']);

        return ['html' => HTML::fetch('./partials/users/users_table.html'), 'paginator' => $info['paginator']];
    }


    public static function user_destroy_window($d = [])
    {
        $user_id = isset($d['user_id']) && is_numeric($d['user_id']) ? $d['user_id'] : 0;
        HTML::assign('user', ['id' => $user_id, 'user_id' => $user_id, 'foo' => 'bar']);

        return ['html' => HTML::fetch('./partials/users/user_destroy.html')];
    }

    public static function user_destroy($d = [])
    {
        $offset = isset($d['offset']) ? preg_replace('~\D+~', '', $d['offset']) : 0;
        $user_id = isset($d['user_id']) && is_numeric($d['user_id']) ? $d['user_id'] : 0;

        DB::query("DELETE FROM users WHERE user_id=$user_id;") or die(DB::error());
        Session::logout($user_id);

        return User::users_fetch(['offset' => $offset]);
    }
}
