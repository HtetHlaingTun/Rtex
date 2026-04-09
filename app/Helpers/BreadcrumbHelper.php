<?php

namespace App\Helpers;

class BreadcrumbHelper
{
    public static function generate($pageName, $parent = null)
    {
        $breadcrumbs = [
            ['label' => 'Home', 'route' => 'welcome']
        ];

        if ($parent) {
            $breadcrumbs[] = ['label' => $parent['label'], 'route' => $parent['route']];
        }

        $breadcrumbs[] = ['label' => $pageName, 'route' => null];

        return $breadcrumbs;
    }
}
