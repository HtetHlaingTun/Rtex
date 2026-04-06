const STANDARD_PRODUCTS = {
    "1oz": { weight_g: 31.1035, type: "oz" },
    "50g": { weight_g: 50, type: "gram_standard" },
    "100g": { weight_g: 100, type: "gram_standard" },
    "1kyatthar_new": { weight_g: 16.329, type: "kyatthar" },
    "1kyatthar_old": { weight_g: 16.606, type: "kyatthar" },
};

const calculateCurrentValue = (asset, currentRates) => {
    let currentValue = 0;

    switch (asset.purchase_currency) {
        case "SGD":
            // For SGD, gold price is per Troy Oz
            if (asset.product_type === "gram_custom") {
                // Custom gram weight: calculate based on oz price
                const troyOz = asset.quantity_grams / 31.1035;
                currentValue = troyOz * (currentRates.gold_sgd || 0);
            } else {
                // Standard product: use fixed price
                currentValue =
                    asset.quantity *
                    (currentRates[`gold_sgd_${asset.product_id}`] ||
                        currentRates.gold_sgd);
            }
            break;

        case "MMK":
            // For MMK, gold price is per Kyatthar
            if (asset.product_type === "kyatthar") {
                currentValue = asset.quantity * (currentRates.gold_mmk || 0);
            } else {
                // Convert from oz to kyatthar for MMK calculation
                const kyattharPerOz = 1 / 1.9048; // 1 oz = 0.525 kyatthar
                const troyOz = asset.quantity_grams / 31.1035;
                currentValue =
                    troyOz * kyattharPerOz * (currentRates.gold_mmk || 0);
            }
            break;

        case "USD":
            // USD price is per Troy Oz
            if (asset.product_type === "gram_custom") {
                const troyOz = asset.quantity_grams / 31.1035;
                currentValue = troyOz * (currentRates.gold_usd || 0);
            } else {
                currentValue = asset.quantity * (currentRates.gold_usd || 0);
            }
            break;
    }

    return currentValue;
};
