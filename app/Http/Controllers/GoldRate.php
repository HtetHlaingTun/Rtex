<?php

namespace App\Http\Controllers;


use Inertia\Inertia;

class GoldRate extends Controller
{
    public function index()
    {
        return Inertia::render('Gold/Index', [
            // 'rates' => $rates
        ]);
    }
}
