<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Currency;
use App\Models\BlogPost;
use App\Models\GoldType;
use Carbon\Carbon;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate sitemap.xml for the website';

    public function handle()
    {
        $this->info('Generating sitemap...');

        // Use your live domain
        $baseUrl = 'https://luckeymm.online';

        $sitemap = Sitemap::create();

        // ========== MAIN PAGES ==========
        $this->info('Adding main pages...');

        // Homepage
        $sitemap->add(Url::create($baseUrl . '/')
            ->setLastModificationDate(Carbon::now())
            ->setChangeFrequency('daily')
            ->setPriority(1.0));

        // Live Market Rates
        $sitemap->add(Url::create($baseUrl . '/live-rates')
            ->setLastModificationDate(Carbon::now())
            ->setChangeFrequency('hourly')
            ->setPriority(0.9));

        // Gold Prices
        $sitemap->add(Url::create($baseUrl . '/gold-prices')
            ->setLastModificationDate(Carbon::now())
            ->setChangeFrequency('hourly')
            ->setPriority(0.9));

        // ========== CURRENCY PAGES ==========
        $this->info('Adding currency pages...');

        // Get ALL active currencies (not just 4)
        $currencies = Currency::where('is_active', true)->get();

        foreach ($currencies as $currency) {
            // Currency history page
            $sitemap->add(Url::create($baseUrl . "/history/{$currency->code}")
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency('daily')
                ->setPriority(0.8));

            // Currency rate page (if exists)
            $sitemap->add(Url::create($baseUrl . "/rates/{$currency->code}")
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency('daily')
                ->setPriority(0.7));
        }

        // ========== GOLD PAGES ==========
        $this->info('Adding gold pages...');

        // Gold types from your system
        $goldTypes = [
            'world_oz' => 'World Gold Spot',
            'new_system' => 'Myanmar Gold (New System)',
            'traditional' => 'Myanmar Gold (Traditional)',
        ];

        foreach ($goldTypes as $slug => $name) {
            $sitemap->add(Url::create($baseUrl . "/gold-history/{$slug}")
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency('daily')
                ->setPriority(0.8));
        }

        // If you have individual gold type pages from database
        $goldTypeRecords = GoldType::where('is_active', true)->get();
        foreach ($goldTypeRecords as $goldType) {
            $sitemap->add(Url::create($baseUrl . "/gold/{$goldType->id}")
                ->setLastModificationDate($goldType->updated_at ?? Carbon::now())
                ->setChangeFrequency('daily')
                ->setPriority(0.7));
        }

        // ========== BLOG PAGES ==========
        $this->info('Adding blog pages...');

        // Blog homepage
        $sitemap->add(Url::create($baseUrl . '/blog')
            ->setLastModificationDate(Carbon::now())
            ->setChangeFrequency('daily')
            ->setPriority(0.7));

        // Individual blog posts
        $blogPosts = BlogPost::where('is_published', true)->get();
        foreach ($blogPosts as $post) {
            $sitemap->add(Url::create($baseUrl . "/blog/{$post->slug}")
                ->setLastModificationDate($post->updated_at ?? $post->created_at)
                ->setChangeFrequency('weekly')
                ->setPriority(0.6));
        }

        // Blog categories (if you have them)
        $categories = BlogPost::where('is_published', true)
            ->select('category')
            ->distinct()
            ->pluck('category');

        foreach ($categories as $category) {
            if ($category) {
                $sitemap->add(Url::create($baseUrl . "/blog/category/{$category}")
                    ->setLastModificationDate(Carbon::now())
                    ->setChangeFrequency('weekly')
                    ->setPriority(0.5));
            }
        }

        // ========== STATIC PAGES ==========
        $this->info('Adding static pages...');

        $staticPages = [
            '/privacy' => 'Privacy Policy',
            '/terms' => 'Terms of Service',
            '/contact' => 'Contact Us',
            '/about' => 'About Us',
            '/faq' => 'FAQ',
            '/sitemap' => 'Sitemap',
        ];

        foreach ($staticPages as $path => $name) {
            $sitemap->add(Url::create($baseUrl . $path)
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency('monthly')
                ->setPriority(0.4));
        }

        // ========== USER DASHBOARD PAGES (if public) ==========
        $this->info('Adding user pages...');

        // Only add if these pages are public (no login required)
        // Or add with lower priority
        $sitemap->add(Url::create($baseUrl . '/login')
            ->setChangeFrequency('monthly')
            ->setPriority(0.3));

        $sitemap->add(Url::create($baseUrl . '/register')
            ->setChangeFrequency('monthly')
            ->setPriority(0.3));

        // ========== EXCHANGE RATE COMPARISON PAGES ==========
        $this->info('Adding exchange rate comparison pages...');

        // Popular currency pairs
        $pairs = [
            'USD/MMK',
            'SGD/MMK',
            'EUR/MMK',
            'THB/MMK',
            'USD/SGD',
            'USD/EUR',
            'USD/THB',
            'JPY/MMK',
            'CNY/MMK',
            'MYR/MMK',
            'INR/MMK',
        ];

        foreach ($pairs as $pair) {
            $sitemap->add(Url::create($baseUrl . "/compare/{$pair}")
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency('daily')
                ->setPriority(0.6));
        }

        // ========== API ENDPOINTS (optional - for SEO) ==========
        // Note: Usually you don't need to add API endpoints to sitemap
        // But if you have public API documentation, add it

        // ========== SAVE THE SITEMAP ==========
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('✅ Sitemap generated successfully!');
        $this->info('📁 Location: ' . public_path('sitemap.xml'));

        // Count total URLs
        $content = file_get_contents(public_path('sitemap.xml'));
        preg_match_all('/<loc>/', $content, $matches);
        $count = count($matches[0]);
        $this->info("📊 Total URLs in sitemap: {$count}");

        return Command::SUCCESS;
    }
}
