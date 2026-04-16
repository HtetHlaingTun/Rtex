<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class BankScraperService
{
    private array $headers = [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Referer'    => 'https://www.google.com/',
    ];

    /**
     * Fetch rates from all banks for a currency code.
     * Returns: ['kbz' => ['buy' => x, 'sell' => y], ...]
     */
    public function fetchAll(string $code): array
    {
        return [
            'kbz'  => $this->scrapeKBZ($code),
            'yoma' => $this->scrapeYoma($code),
            'cb'   => $this->scrapeCB($code),
            'aya'  => $this->scrapeAYA($code),
        ];
    }

    private function fetchCrawler(string $url, string $bank): ?Crawler
    {
        try {
            $response = Http::withHeaders($this->headers)->timeout(15)->get($url);
            if (!$response->successful()) {
                Log::warning("{$bank}: HTTP {$response->status()} for {$url}");
                return null;
            }
            return new Crawler($response->body());
        } catch (\Exception $e) {
            Log::error("{$bank} fetch error: " . $e->getMessage());
            return null;
        }
    }

    private function extractByIndex(Crawler $crawler, int $buyIdx, int $sellIdx): array
    {
        $buy  = $crawler->filter('td')->eq($buyIdx);
        $sell = $crawler->filter('td')->eq($sellIdx);
        return [
            'buy'  => $buy->count()  ? $this->cleanRate($buy->text())  : 0,
            'sell' => $sell->count() ? $this->cleanRate($sell->text()) : 0,
        ];
    }

    private function extractByRowSearch(Crawler $crawler, string $code, int $buyCol = 1, int $sellCol = 2): array
    {
        $buy = 0;
        $sell = 0;

        $crawler->filter('tr')->each(function ($row) use ($code, $buyCol, $sellCol, &$buy, &$sell) {
            if ($buy > 0 && $sell > 0) return;
            $cells = $row->filter('td');
            if ($cells->count() > max($buyCol, $sellCol)) {
                if (strtoupper(trim($cells->eq(0)->text())) === $code) {
                    $buy  = $this->cleanRate($cells->eq($buyCol)->text());
                    $sell = $this->cleanRate($cells->eq($sellCol)->text());
                }
            }
        });

        return ['buy' => $buy, 'sell' => $sell];
    }

    private function extractByRegex(Crawler $crawler, string $code): array
    {
        $text = $crawler->filter('body')->text('', false);
        $pattern = '/' . preg_quote($code) . '[^0-9]*([0-9,]+(?:\.[0-9]+)?)[^0-9]+([0-9,]+(?:\.[0-9]+)?)/i';

        if (preg_match($pattern, $text, $matches)) {
            $buy  = $this->cleanRate($matches[1]);
            $sell = $this->cleanRate($matches[2] ?? '0');
            if ($buy > 0 || $sell > 0) {
                return ['buy' => $buy, 'sell' => $sell];
            }
        }

        return ['buy' => 0, 'sell' => 0];
    }

    private function scrapeKBZ(string $code): array
    {
        $map = [
            'EUR' => ['buy' => 1,  'sell' => 3],
            'USD' => ['buy' => 5,  'sell' => 7],
            'SGD' => ['buy' => 9,  'sell' => 11],
            'THB' => ['buy' => 13, 'sell' => 15],
        ];

        if (!isset($map[$code])) return ['buy' => 0, 'sell' => 0];

        $crawler = $this->fetchCrawler('https://www.kbzbank.com/en/', 'KBZ');
        if (!$crawler) return ['buy' => 0, 'sell' => 0];

        $rates = $this->extractByIndex($crawler, $map[$code]['buy'], $map[$code]['sell']);
        Log::info("KBZ {$code}: Buy={$rates['buy']}, Sell={$rates['sell']}");
        return $rates;
    }

    private function scrapeYoma(string $code): array
    {
        $map = [
            'USD' => ['buy' => 2,  'sell' => 3],
            'EUR' => ['buy' => 6,  'sell' => 7],
            'SGD' => ['buy' => 10, 'sell' => 11],
        ];

        $crawler = $this->fetchCrawler('https://www.yomabank.com/en/', 'Yoma');
        if (!$crawler) return ['buy' => 0, 'sell' => 0];

        if (isset($map[$code])) {
            $rates = $this->extractByIndex($crawler, $map[$code]['buy'], $map[$code]['sell']);
            if ($rates['buy'] > 0 || $rates['sell'] > 0) {
                Log::info("Yoma {$code} (index): Buy={$rates['buy']}, Sell={$rates['sell']}");
                return $rates;
            }
        }

        $rates = $this->extractByRowSearch($crawler, $code, buyCol: 2, sellCol: 3);
        if ($rates['buy'] > 0 || $rates['sell'] > 0) {
            Log::info("Yoma {$code} (row): Buy={$rates['buy']}, Sell={$rates['sell']}");
            return $rates;
        }

        $rates = $this->extractByRegex($crawler, $code);
        Log::info("Yoma {$code} (regex): Buy={$rates['buy']}, Sell={$rates['sell']}");
        return $rates;
    }

    private function scrapeCB(string $code): array
    {
        $map = [
            'USD' => ['buy' => 1,  'sell' => 2],
            'THB' => ['buy' => 4,  'sell' => 5],
            'EUR' => ['buy' => 17, 'sell' => 18],
            'SGD' => ['buy' => 20, 'sell' => 21],
        ];

        $crawler = $this->fetchCrawler('https://www.cbbank.com.mm/en/online-trading-platform', 'CB');
        if (!$crawler) return ['buy' => 0, 'sell' => 0];

        if (isset($map[$code])) {
            $rates = $this->extractByIndex($crawler, $map[$code]['buy'], $map[$code]['sell']);
            if ($rates['buy'] > 0 || $rates['sell'] > 0) {
                Log::info("CB {$code} (index): Buy={$rates['buy']}, Sell={$rates['sell']}");
                return $rates;
            }
        }

        $rates = $this->extractByRowSearch($crawler, $code);
        if ($rates['buy'] > 0 || $rates['sell'] > 0) {
            Log::info("CB {$code} (row): Buy={$rates['buy']}, Sell={$rates['sell']}");
            return $rates;
        }

        $rates = $this->extractByRegex($crawler, $code);
        Log::info("CB {$code} (regex): Buy={$rates['buy']}, Sell={$rates['sell']}");
        return $rates;
    }

    private function scrapeAYA(string $code): array
    {
        $map = [
            'USD' => [['buy' => 8,  'sell' => 9],  ['buy' => 28, 'sell' => 29]],
            'EUR' => [['buy' => 33, 'sell' => 34]],
            'SGD' => [['buy' => 38, 'sell' => 39]],
            'THB' => [['buy' => 18, 'sell' => 19]],
        ];

        $crawler = $this->fetchCrawler(
            'https://ayabank.com/personal-banking/other-services/foreign-currency-exchange-service',
            'AYA'
        );
        if (!$crawler) return ['buy' => 0, 'sell' => 0];

        if (isset($map[$code])) {
            foreach ($map[$code] as $indices) {
                $rates = $this->extractByIndex($crawler, $indices['buy'], $indices['sell']);
                if ($rates['buy'] > 0 || $rates['sell'] > 0) {
                    Log::info("AYA {$code} (index): Buy={$rates['buy']}, Sell={$rates['sell']}");
                    return $rates;
                }
            }
        }

        $rates = $this->extractByRowSearch($crawler, $code, buyCol: 3, sellCol: 4);
        if ($rates['buy'] > 0 || $rates['sell'] > 0) {
            Log::info("AYA {$code} (row): Buy={$rates['buy']}, Sell={$rates['sell']}");
            return $rates;
        }

        $rates = $this->extractByRegex($crawler, $code);
        Log::info("AYA {$code} (regex): Buy={$rates['buy']}, Sell={$rates['sell']}");
        return $rates;
    }

    private function cleanRate(string $value): float
    {
        $clean = preg_replace('/[^0-9.]/', '', str_replace(',', '', $value));
        return is_numeric($clean) ? (float) $clean : 0;
    }
}
