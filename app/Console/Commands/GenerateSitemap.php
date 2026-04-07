<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Currency;

use Carbon\Carbon;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate sitemap.xml for the website';

    public function handle()
    {
        $this->info('Generating sitemap...');

        $sitemap = Sitemap::create();

        // Add homepage
        $sitemap->add(Url::create('/')
            ->setLastModificationDate(Carbon::now())
            ->setChangeFrequency('daily')
            ->setPriority(1.0));

        // Add currency exchange pages
        $currencies = Currency::whereIn('code', ['USD', 'SGD', 'EUR', 'THB'])->get();
        foreach ($currencies as $currency) {
            $sitemap->add(Url::create("/history/{$currency->code}")
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency('daily')
                ->setPriority(0.9));
        }

        // Add gold history pages
        $goldTypes = ['new_system', 'traditional', 'world_oz'];
        foreach ($goldTypes as $type) {
            $sitemap->add(Url::create("/gold-history/{$type}")
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency('daily')
                ->setPriority(0.8));
        }

        // Add public pages
        $sitemap->add(Url::create('/privacy')
            ->setChangeFrequency('monthly')
            ->setPriority(0.5));

        $sitemap->add(Url::create('/contact')
            ->setChangeFrequency('monthly')
            ->setPriority(0.5));

        // Save to public directory
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully at ' . public_path('sitemap.xml'));
    }
}
