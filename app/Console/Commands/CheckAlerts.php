<?php
// app/Console/Commands/CheckAlerts.php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\ExchangeRate;
use App\Models\Notification;
use App\Events\AlertTriggered;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckAlerts extends Command
{
    protected $signature = 'watch:alerts-check {--cleanup : Delete old alerts older than 1 year}';
    protected $description = 'Check all active alerts and trigger notifications';

    public function handle()
    {
        // First, cleanup old alerts if flag is passed
        if ($this->option('cleanup')) {
            $this->cleanupOldAlerts();
        }

        // Then check for triggered alerts
        $this->checkTriggeredAlerts();

        return Command::SUCCESS;
    }

    protected function cleanupOldAlerts()
    {
        $oneYearAgo = Carbon::now()->subYear();

        $deletedAlerts = Alert::where('triggered_at', '<', $oneYearAgo)->delete();
        if ($deletedAlerts > 0) {
            $this->info("🗑️ Deleted {$deletedAlerts} old alert(s) older than 1 year.");
        }

        $deletedNotifications = Notification::where('created_at', '<', $oneYearAgo)->delete();
        if ($deletedNotifications > 0) {
            $this->info("🗑️ Deleted {$deletedNotifications} old notification(s) older than 1 year.");
        }
    }

    protected function checkTriggeredAlerts()
    {
        $alerts = Alert::where('is_active', true)
            ->whereNull('triggered_at')
            ->with('currency')
            ->get();

        $triggered = 0;

        foreach ($alerts as $alert) {
            $latestRate = ExchangeRate::where('currency_id', $alert->currency_id)
                ->latest()
                ->first();

            if (!$latestRate) continue;

            $currentRate = ($latestRate->buy_rate + $latestRate->sell_rate) / 2;
            $shouldTrigger = false;

            if ($alert->type === 'above' && $currentRate >= $alert->target_rate) {
                $shouldTrigger = true;
            } elseif ($alert->type === 'below' && $currentRate <= $alert->target_rate) {
                $shouldTrigger = true;
            }

            if ($shouldTrigger) {
                $alert->update(['triggered_at' => now()]);

                $formattedTargetRate = number_format($alert->target_rate, 0);
                $formattedCurrentRate = number_format($currentRate, 0);

                $message = "{$alert->currency->code} has gone {$alert->type} {$formattedTargetRate} MMK. Current rate: {$formattedCurrentRate} MMK";

                $notification = Notification::create([
                    'user_id' => $alert->user_id,
                    'type' => 'alert',
                    'title' => 'Price Alert',
                    'message' => $message,
                    'data' => [
                        'currency_id' => $alert->currency_id,
                        'currency_code' => $alert->currency->code,
                        'type' => $alert->type,
                        'target_rate' => $alert->target_rate,
                        'current_rate' => $currentRate,
                    ],
                ]);

                // Broadcast the event - make sure this is called
                try {
                    broadcast(new AlertTriggered($notification, $alert->user_id));
                    $this->info("✅ Alert triggered and broadcasted for {$alert->currency->code}");
                } catch (\Exception $e) {
                    $this->error("Failed to broadcast: " . $e->getMessage());
                }

                $triggered++;
            }
        }

        $this->info("📊 Checked {$alerts->count()} alerts. Triggered: {$triggered}");
    }
}
