<?php


namespace App\Helpers;

class GoldCalculator
{
    // Conversion constants
    const GRAMS_PER_TROY_OZ = 31.1035;
    const TROY_OZ_PER_GRAM = 0.0321507;

    // Kyatthar conversions (New standard: 16.329g per kyatthar)
    const KYATTHAR_NEW_GRAMS = 16.329;
    const KYATTHAR_OLD_GRAMS = 16.606;
    const KYATTHAR_NEW_OZ = 0.525; // 1 kyatthar new = 0.525 troy oz
    const KYATTHAR_OLD_OZ = 0.516; // 1 kyatthar old = 0.516 troy oz
    const KYATTHAR_NEW_OZ_PER_UNIT = 1.9048; // 1 kyatthar = 1.9048 oz

    /**
     * Calculate weight in grams and troy ounces based on product type
     */
    public static function calculateWeight($quantity, $productType, $customGrams = null)
    {
        $grams = 0;
        $troyOunces = 0;
        $weightUnit = null;
        $kyattharType = null;

        switch ($productType) {
            case '1oz':
                $grams = $quantity * self::GRAMS_PER_TROY_OZ;
                $troyOunces = $quantity;
                $weightUnit = 'oz';
                break;

            case '50g':
                $grams = $quantity * 50;
                $troyOunces = $quantity * (50 / self::GRAMS_PER_TROY_OZ);
                $weightUnit = 'gram';
                break;

            case '100g':
                $grams = $quantity * 100;
                $troyOunces = $quantity * (100 / self::GRAMS_PER_TROY_OZ);
                $weightUnit = 'gram';
                break;

            case '1kyatthar':
                $grams = $quantity * self::KYATTHAR_NEW_GRAMS;
                $troyOunces = $quantity * self::KYATTHAR_NEW_OZ_PER_UNIT;
                $weightUnit = 'kyatthar';
                $kyattharType = 'new';
                break;

            case '10kyatthar':
                $grams = $quantity * (self::KYATTHAR_NEW_GRAMS * 10);
                $troyOunces = $quantity * (self::KYATTHAR_NEW_OZ_PER_UNIT * 10);
                $weightUnit = 'kyatthar';
                $kyattharType = 'new';
                break;

            case 'custom':
                if ($customGrams) {
                    $grams = $quantity * $customGrams;
                    $troyOunces = $quantity * ($customGrams / self::GRAMS_PER_TROY_OZ);
                    $weightUnit = 'gram';
                }
                break;
        }

        return [
            'grams' => $grams,
            'troy_ounces' => $troyOunces,
            'weight_unit' => $weightUnit,
            'kyatthar_type' => $kyattharType,
            'product_type' => $productType
        ];
    }

    /**
     * Get current market price based on purchase currency
     */
    public static function getCurrentMarketPrice($asset, $currentRates)
    {
        // If we have troy ounces calculated, use them for accurate pricing
        if ($asset->troy_ounces && $asset->troy_ounces > 0) {
            switch ($asset->purchase_currency) {
                case 'USD':
                    return $currentRates['gold_usd'] ?? 0;
                case 'SGD':
                    return $currentRates['gold_sgd'] ?? 0;
                case 'MMK':
                    // Convert USD gold price to MMK
                    $usdPrice = $currentRates['gold_usd'] ?? 0;
                    $usdToMmk = $currentRates['usd_mmk'] ?? 1;
                    return $usdPrice * $usdToMmk;
                default:
                    return $currentRates['gold_usd'] ?? 0;
            }
        }

        // Fallback to original logic for non-gold or assets without weight
        switch ($asset->type) {
            case 'gold':
                if ($asset->purchase_currency === 'USD') {
                    return $currentRates['gold_usd'] ?? 0;
                } elseif ($asset->purchase_currency === 'SGD') {
                    return $currentRates['gold_sgd'] ?? 0;
                }
                return $currentRates['gold_mmk'] ?? 0;
            default:
                return $asset->purchase_price;
        }
    }

    /**
     * Get current value for gold asset (accounts for weight)
     */
    public static function getCurrentValue($asset, $currentRates)
    {
        // If we have troy ounces, calculate based on weight
        if ($asset->troy_ounces && $asset->troy_ounces > 0) {
            $pricePerTroyOz = 0;

            switch ($asset->purchase_currency) {
                case 'USD':
                    $pricePerTroyOz = $currentRates['gold_usd'] ?? 0;
                    break;
                case 'SGD':
                    $pricePerTroyOz = $currentRates['gold_sgd'] ?? 0;
                    break;
                case 'MMK':
                    // Convert USD gold price to MMK
                    $usdPrice = $currentRates['gold_usd'] ?? 0;
                    $usdToMmk = $currentRates['usd_mmk'] ?? 1;
                    $pricePerTroyOz = $usdPrice * $usdToMmk;
                    break;
            }

            return $asset->troy_ounces * $pricePerTroyOz;
        }

        // Fallback to simple calculation
        return $asset->getCurrentValueMmk($currentRates);
    }

    /**
     * Get product label for display
     */
    public static function getProductLabel($productType)
    {
        $labels = [
            '1oz' => '1 Troy Ounce (31.1035g)',
            '50g' => '50 Gram Gold Bar',
            '100g' => '100 Gram Gold Bar',
            '1kyatthar' => '1 Kyatthar (16.329g)',
            '10kyatthar' => '10 Kyatthar (163.29g)',
            'custom' => 'Custom Weight'
        ];
        return $labels[$productType] ?? 'Gold';
    }

    /**
     * Get conversion info for display
     */
    public static function getConversionInfo($productType, $customGrams = null)
    {
        switch ($productType) {
            case '1oz':
                return '1 Troy Ounce = 31.1035 grams';
            case '50g':
                return '50 grams = 1.6075 Troy Ounces';
            case '100g':
                return '100 grams = 3.215 Troy Ounces';
            case '1kyatthar':
                return '1 Kyatthar = 16.329 grams = 1.9048 Troy Ounces';
            case '10kyatthar':
                return '10 Kyatthar = 163.29 grams = 19.048 Troy Ounces';
            case 'custom':
                if ($customGrams) {
                    $troyOz = $customGrams / self::GRAMS_PER_TROY_OZ;
                    return "{$customGrams} grams = {$troyOz} Troy Ounces";
                }
                return 'Enter weight in grams';
            default:
                return '';
        }
    }
}
