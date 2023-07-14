<?php

class Plot
{
    const PLOT_FIELDS = [
        'plot_id',
        'status',
        'billing',
        'number',
        'size',
        'price',
        'base_fixed',
        'electricity_t1',
        'electricity_t2',
        'updated',
    ];
    // GENERAL
    public static function plot_info($plot_id)
    {
        $q = DB::query("SELECT plot_id, status, billing, number, size, price, base_fixed, electricity_t1, electricity_t2, updated
            FROM plots WHERE plot_id LIKE '%$plot_id%' LIMIT 1;") or die(DB::error());
        if ($row = DB::fetch_row($q)) {
            return [
                'id' => (int) $row['plot_id'],
                'status_id' => $row['status'],
                'billing' => $row['billing'],
                'number' => $row['number'],
                'size' => $row['size'],
                'price' => number_format($row['price'], 0, '', ' '),
                'electricity_t1' => $row['electricity_t1'],
                'electricity_t2' => $row['electricity_t2']
            ];
        } else {
            return [
                'id' => 0,
                'status_id' => 0,
                'billing' => 0,
                'number' => '',
                'size' => '',
                'price' => '',
                'electricity_t1' => 0,
                'electricity_t2' => 0
            ];
        }
    }

    public static function plots_list($d = [])
    {
        // vars
        $search = isset($d['search']) && trim($d['search']) && is_numeric($d['search']) ? $d['search'] : '';
        $offset = isset($d['offset']) && is_numeric($d['offset']) ? $d['offset'] : 0;
        $limit = 20;
        $items = [];
        // where
        $where = [];
        if ($search != '') $where[] = "number LIKE '%$search%'";
        $where = $where ? "WHERE " . implode(" AND ", $where) : "";
        // info
        $q = DB::query(
            "SELECT plot_id, status, billing, number, size, price, base_fixed, electricity_t1, electricity_t2, updated
            FROM plots 
            $where 
            ORDER BY number 
            LIMIT $limit
            OFFSET $offset;
            "
        ) or die(DB::error());
        while ($row = DB::fetch_row($q)) {
            $items[] = [
                'id' => (int) $row['plot_id'],
                'status' => $row['status'],
                'status_str' => Plot::plot_status_str($row['status']),
                'billing' => $row['billing'],
                'number' => $row['number'],
                'size' => $row['size'],
                'price' => number_format($row['price'], 0, '', ' '),
                'base_fixed' => (bool) $row['base_fixed'],
                'electricity_t1' => (float) $row['electricity_t1'],
                'electricity_t2' => (float) $row['electricity_t2'],
                'users' => $row['number'] ? User::users_list_plots($row['number']) : [],
                'updated' => date('Y/m/d', $row['updated'])
            ];
        }
        // paginator
        $q = DB::query("SELECT count(*) FROM plots " . $where . ";");
        $row = DB::fetch_row($q);
        $count = $row['count(*)'] ?? 0;
        $url = 'plots';
        if ($search) $url .= '?search=' . $search . '&';
        paginator($count, $offset, $limit, $url, $paginator);
        // output
        return ['items' => $items, 'paginator' => $paginator];
    }

    public static function all_plots_selected_fields($d = []): array
    {
        $fields = isset($d['fields']) ? $d['fields'] : [];
        if (count($fields) > 0) {
            $fields = array_filter($fields, function ($v) {
                return in_array($v, self::PLOT_FIELDS) ?? $v;
            }, ARRAY_FILTER_USE_BOTH);
        } else {
            $fields = self::PLOT_FIELDS;
        }
        $select = "SELECT " . implode(" , ", $fields);

        $q = DB::query("$select FROM plots ORDER BY plot_id;")
            or die(DB::error());

        while ($row = DB::fetch_row($q)) {
            $items[] = [
                'id' => isset($row['plot_id']) ? (int) $row['plot_id'] : null,
                'status' => isset($row['status']) ? $row['status'] : null,
                'billing' => isset($row['billing']) ? $row['billing'] : null,
                'number' => isset($row['number']) ? $row['number'] : null,
                'size' => isset($row['size']) ? $row['size'] : null,
                'price' => isset($row['price']) ? number_format($row['price'], 0, '', ' ') : null,
                'base_fixed' => isset($row['base_fixed']) ? (bool) $row['base_fixed'] : null,
                'electricity_t1' => isset($row['electricity_t1']) ? (float) $row['electricity_t1'] : null,
                'electricity_t2' => isset($row['electricity_t2']) ? (float) $row['electricity_t2'] : null,
                'updated' => isset($row['updated']) ? date('Y/m/d', $row['updated']) : null,
            ];
            print("\n");
        }

        return ['items' => $items];
    }

    public static function plots_count(): int
    {
        $q = DB::query("SELECT count(*) FROM plots;");
        $row = DB::fetch_row($q);
        return $row['count(*)'] ?? 0;
    }

    public static function plots_fetch($d = [])
    {
        $info = Plot::plots_list($d);
        HTML::assign('plots', $info['items']);

        return ['html' => HTML::fetch('./partials/plots_table.html'), 'paginator' => $info['paginator']];
    }

    // ACTIONS
    public static function plot_edit_window($d = [])
    {
        $plot_id = isset($d['plot_id']) && is_numeric($d['plot_id']) ? $d['plot_id'] : 0;
        HTML::assign('plot', Plot::plot_info($plot_id));

        return ['html' => HTML::fetch('./partials/plot_edit.html')];
    }

    public static function plot_edit_update($d = [])
    {
        // vars
        $plot_id = isset($d['plot_id']) && is_numeric($d['plot_id']) ? $d['plot_id'] : 0;
        $status = isset($d['status']) && is_numeric($d['status']) ? $d['status'] : 0;
        $billing = isset($d['billing']) && in_array($d['billing'], [0, 1]) ? $d['billing'] : 0;
        $number = isset($d['number']) && trim($d['number']) ? trim($d['number']) : '';
        $size = isset($d['size']) ? preg_replace('~\D+~', '', $d['size']) : 0;
        $price = isset($d['price']) ? preg_replace('~\D+~', '', $d['price']) : 0;
        $offset = isset($d['offset']) ? preg_replace('~\D+~', '', $d['offset']) : 0;
        // update
        if ($plot_id != 0) {
            $set = [];
            $set[] = "status='" . $status . "'";
            $set[] = "billing='" . $billing . "'";
            $set[] = "number='" . $number . "'";
            $set[] = "size='" . $size . "'";
            $set[] = "price='" . $price . "'";
            $set[] = "updated='" . Session::$ts . "'";
            $set = implode(", ", $set);
            DB::query("UPDATE plots 
                SET $set
                WHERE plot_id LIKE '%$plot_id%';") or die(DB::error());
        } else {
            DB::query("INSERT INTO plots (
                status,
                billing,
                number,
                size,
                price,
                updated
            ) VALUES (
                '" . $status . "',
                '" . $billing . "',
                '" . $number . "',
                '" . $size . "',
                '" . $price . "',
                '" . Session::$ts . "'
            );") or die(DB::error());
        }
        // output
        return Plot::plots_fetch(['offset' => $offset]);
    }

    private static function plot_status_str($id)
    {
        if ($id == 1) return 'Reserved';
        if ($id == 2) return 'Sold';
        return 'Free';
    }
}
