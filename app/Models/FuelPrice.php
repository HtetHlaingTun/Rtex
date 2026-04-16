<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelPrice extends Model
{
    protected $fillable = ['octane_92', 'octane_95', 'diesel', 'global_usd_reference', 'market_usd_rate'];
}
