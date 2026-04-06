<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserAsset;
use App\Helpers\GoldCalculator;

class UpdateGoldAssetsSeeder extends Seeder
{
    public function run()
    {
        $goldAssets = UserAsset::where('type', 'gold')
            ->whereNull('troy_ounces')
            ->get();

        foreach ($goldAssets as $asset) {
            // Try to determine product type from name
            $productType = $this->determineProductType($asset->name);
            $customGrams = null;

            if ($productType === 'custom') {
                $customGrams = $this->extractGrams($asset->name);
            }

            if ($productType) {
                $weightData = GoldCalculator::calculateWeight(
                    $asset->quantity,
                    $productType,
                    $customGrams
                );

                $asset->update([
                    'product_type' => $weightData['product_type'],
                    'weight_unit' => $weightData['weight_unit'],
                    'kyatthar_type' => $weightData['kyatthar_type'],
                    'weight_in_grams' => $weightData['grams'],
                    'troy_ounces' => $weightData['troy_ounces'],
                ]);
            }
        }
    }

    private function determineProductType($name)
    {
        $name = strtolower($name);

        if (str_contains($name, 'oz') || str_contains($name, 'ounce')) {
            return '1oz';
        }
        if (str_contains($name, '50g') || str_contains($name, '50 gram')) {
            return '50g';
        }
        if (str_contains($name, '100g') || str_contains($name, '100 gram')) {
            return '100g';
        }
        if (str_contains($name, '10 kyatthar') || str_contains($name, '10kyatthar')) {
            return '10kyatthar';
        }
        if (str_contains($name, 'kyatthar')) {
            return '1kyatthar';
        }

        return 'custom';
    }

    private function extractGrams($name)
    {
        preg_match('/(\d+(?:\.\d+)?)\s*g(?:ram)?/i', $name, $matches);
        return isset($matches[1]) ? (float)$matches[1] : null;
    }
}
