<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FuelPrice extends Model
{
    protected $fillable = [
        'octane_92',
        'octane_95',
        'diesel',
        'global_usd_reference',
        'market_usd_rate',
        'region',
        'change_percent_92',
        'change_percent_95',
        'change_percent_diesel'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Get previous day's price for trend
    public static function getPreviousPrice($region, $field)
    {
        return static::where('region', $region)
            ->whereDate('created_at', '<', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->value($field);
    }

    // Get trend for a specific fuel type
    public function getTrend($field)
    {
        $current = $this->$field;

        // Use stored change percent if available
        $percent = match ($field) {
            'octane_92' => $this->change_percent_92,
            'octane_95' => $this->change_percent_95,
            'diesel' => $this->change_percent_diesel,
            default => 0
        };

        if ($percent > 0) {
            return ['direction' => 'up', 'percent' => $percent, 'icon' => '▲', 'color' => 'text-emerald-600'];
        } elseif ($percent < 0) {
            return ['direction' => 'down', 'percent' => abs($percent), 'icon' => '▼', 'color' => 'text-rose-600'];
        } else {
            return ['direction' => 'neutral', 'percent' => 0, 'icon' => '—', 'color' => 'text-slate-400'];
        }
    }
}
