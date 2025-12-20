<?php

namespace Aspro\Max\Smartseo\Admin;

class Helper
{

    const ROUTE_FILE = 'aspro.max_smartseo.php';

    static public function url($route, $params = [])
    {
        return self::ROUTE_FILE . '?' . http_build_query(
            array_filter(
              array_merge(['route' => $route], $params)
            )
        );
    }

}
