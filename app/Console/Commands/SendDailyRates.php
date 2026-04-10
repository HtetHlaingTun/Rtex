<?php

namespace App\Console\Commands;

use App\Models\Subscriber;
use App\Models\ExchangeRate;
use App\Models\Currency;
use App\Models\WorldGoldSnapshot;
use App\Services\BrevoMailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendDailyRates extends Command
{
    protected $signature = 'mail:send-daily-rates
                            {--test : Send to a test email instead of all subscribers}
                            {--email= : Specific email to test with}
                            {--batch= : Send specific batch (1,2,3)}
                            {--batch-size=50 : Number of emails per batch}
                            {--dry-run : Show what would be sent without actually sending}';

    protected $description = 'Send daily exchange rate alerts to all active subscribers (batched)';

    protected $brevoMail;
    protected $dailyLimit = 290; // Leave 10 buffer from Brevo's 300 limit

    public function __construct(BrevoMailService $brevoMail)
    {
        parent::__construct();
        $this->brevoMail = $brevoMail;
    }

    public function handle()
    {
        $this->info('🚀 Starting daily rate email dispatch...');

        // Get rates for today
        $rates = $this->getLatestRates();
        $gold = $this->getLatestGold();

        if (empty($rates)) {
            $this->error('No rates found. Run bank sync first.');
            return 1;
        }

        // Handle test mode
        if ($this->option('test')) {
            $testEmail = $this->option('email') ?? 'mmratepro@gmail.com';
            return $this->sendTestEmail($testEmail, $rates, $gold);
        }

        // Get all active subscribers
        $allSubscribers = Subscriber::where('is_active', true)->get();
        $total = $allSubscribers->count();

        $this->info("Total active subscribers: {$total}");

        // Check if we need batching
        if ($total > $this->dailyLimit) {
            $this->warn("⚠️ You have {$total} subscribers. Brevo free limit is {$this->dailyLimit} emails/day.");
            $this->info("Emails will be sent in batches over multiple hours.");

            return $this->sendBatched($allSubscribers, $rates, $gold);
        }

        // Send all at once if under limit
        return $this->sendBatch($allSubscribers, $rates, $gold, 1, $total);
    }

    protected function sendBatched($subscribers, $rates, $gold)
    {
        $batchSize = (int) $this->option('batch-size');
        $total = $subscribers->count();
        $batches = ceil($total / $batchSize);

        $this->info("Splitting {$total} subscribers into {$batches} batches of ~{$batchSize}");

        // Check if specific batch requested
        $specificBatch = $this->option('batch');

        if ($specificBatch) {
            // Send only the requested batch
            $batchNumber = (int) $specificBatch;
            $skip = ($batchNumber - 1) * $batchSize;
            $batchSubscribers = $subscribers->slice($skip, $batchSize);

            return $this->sendBatch($batchSubscribers, $rates, $gold, $batchNumber, $total);
        }

        // Send all batches (for manual trigger)
        $totalSent = 0;
        $totalFailed = 0;

        for ($i = 1; $i <= $batches; $i++) {
            $skip = ($i - 1) * $batchSize;
            $batchSubscribers = $subscribers->slice($skip, $batchSize);

            $this->info("\n📦 Processing Batch {$i}/{$batches} ({$batchSubscribers->count()} emails)");

            $result = $this->sendBatch($batchSubscribers, $rates, $gold, $i, $total);

            $totalSent += $result['sent'];
            $totalFailed += $result['failed'];

            // Wait between batches to respect rate limits
            if ($i < $batches) {
                $this->info("⏳ Waiting 1 hour before next batch...");
                if (!$this->option('dry-run')) {
                    sleep(3600); // Wait 1 hour
                }
            }
        }

        $this->info("\n✅ All batches complete!");
        $this->info("   Total Sent: {$totalSent}");
        $this->info("   Total Failed: {$totalFailed}");

        return 0;
    }

    protected function sendBatch($subscribers, $rates, $gold, $batchNumber, $total)
    {
        $sent = 0;
        $failed = 0;
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info("🔍 DRY RUN - Would send to " . $subscribers->count() . " subscribers");
            foreach ($subscribers as $subscriber) {
                $this->line("   - {$subscriber->email}");
            }
            return ['sent' => 0, 'failed' => 0];
        }

        foreach ($subscribers as $subscriber) {
            try {
                $htmlContent = view('emails.daily-rates', [
                    'rates' => $rates,
                    'gold' => $gold,
                    'subscriber' => $subscriber
                ])->render();

                $result = $this->brevoMail->send(
                    $subscriber->email,
                    'Valued Subscriber',
                    'MMRatePro Daily Exchange Rates - ' . now()->format('F j, Y'),
                    $htmlContent
                );

                if ($result) {
                    $sent++;
                    $this->info("✓ [Batch {$batchNumber}] Sent to {$subscriber->email}");
                } else {
                    $failed++;
                    $this->error("✗ [Batch {$batchNumber}] Failed to send to {$subscriber->email}");
                }

                // Rate limit: 1 email per second (10 per second allowed, but be safe)
                usleep(500000); // 0.5 second delay

            } catch (\Exception $e) {
                $failed++;
                Log::error('Daily rate email failed: ' . $e->getMessage());
                $this->error("✗ [Batch {$batchNumber}] Error: " . $e->getMessage());
            }
        }

        $this->info("📊 Batch {$batchNumber} complete: Sent={$sent}, Failed={$failed}");

        // Log batch completion
        Log::info("Daily rates batch {$batchNumber} sent", [
            'sent' => $sent,
            'failed' => $failed,
            'batch' => $batchNumber
        ]);

        return ['sent' => $sent, 'failed' => $failed];
    }

    protected function sendTestEmail($testEmail, $rates, $gold)
    {
        $this->info("Test mode: Sending to {$testEmail}");

        try {
            $htmlContent = view('emails.daily-rates', [
                'rates' => $rates,
                'gold' => $gold,
                'subscriber' => (object)['email' => $testEmail]
            ])->render();

            $result = $this->brevoMail->send(
                $testEmail,
                'Test User',
                'MMRatePro Daily Exchange Rates - ' . now()->format('F j, Y') . ' (TEST)',
                $htmlContent
            );

            if ($result) {
                $this->info('✅ Test email sent successfully!');
            } else {
                $this->error('❌ Test email failed to send');
            }
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
        }

        return 0;
    }

    private function getLatestRates()
    {
        $currencies = Currency::where('is_active', true)->get();
        $rates = [];

        foreach ($currencies as $currency) {
            $rate = ExchangeRate::where('currency_id', $currency->id)
                ->where('is_verified', true)
                ->latest('rate_date')
                ->first();

            if ($rate) {
                $previous = ExchangeRate::where('currency_id', $currency->id)
                    ->where('id', '<', $rate->id)
                    ->latest('rate_date')
                    ->first();

                $rates[] = [
                    'currency' => [
                        'code' => $currency->code,
                        'name' => $currency->name,
                    ],
                    'buy_rate' => (float) $rate->buy_rate,
                    'sell_rate' => (float) $rate->sell_rate,
                    'change_percentage' => (float) ($rate->change_percentage ?? 0),
                    'market_trend' => $rate->market_trend ?? 'stable',
                ];
            }
        }

        return $rates;
    }

    private function getLatestGold()
    {
        $gold = WorldGoldSnapshot::latest('fetched_at')->first();

        if ($gold) {
            return [
                'usd_price' => $gold->usd_price,
                'mmk_price_new' => $gold->mmk_price_new ?? 0,
                'mmk_price_old' => $gold->mmk_price_old ?? 0,
            ];
        }

        return null;
    }
}
