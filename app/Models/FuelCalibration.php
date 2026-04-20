<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelCalibration extends Model
{
    protected $table = 'fuel_calibration';

    protected $fillable = [
        'calibration_factor',
        'reference_price_92',
        'global_price_at_calibration',
        'usd_mmk_at_calibration',
        'notes'
    ];

    public static function getFactor(): float
    {
        $calibration = self::first();
        return $calibration ? (float) $calibration->calibration_factor : 1.4000;
    }
}
