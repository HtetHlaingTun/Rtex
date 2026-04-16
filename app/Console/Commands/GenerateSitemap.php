<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Currency;
use App\Models\GoldType;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;


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

        $mainPages = [
            '/' => ['priority' => 1.0, 'frequency' => 'daily', 'label' => 'Homepage'],
            '/live-rates' => ['priority' => 0.9, 'frequency' => 'hourly', 'label' => 'Live Market Rates'],
            '/gold-prices' => ['priority' => 0.9, 'frequency' => 'hourly', 'label' => 'Gold Prices'],
            '/blog' => ['priority' => 0.8, 'frequency' => 'daily', 'label' => 'Blog'],
            '/privacy' => ['priority' => 0.4, 'frequency' => 'monthly', 'label' => 'Privacy Policy'],
            '/terms' => ['priority' => 0.4, 'frequency' => 'monthly', 'label' => 'Terms of Service'],
            '/contact' => ['priority' => 0.5, 'frequency' => 'weekly', 'label' => 'Contact Us'],
            '/rates' => ['priority' => 0.8, 'frequency' => 'daily', 'label' => 'Rates'],
            '/gold/index' => ['priority' => 0.8, 'frequency' => 'daily', 'label' => 'Gold Index'],
        ];

        foreach ($mainPages as $path => $config) {
            $sitemap->add(Url::create($baseUrl . $path)
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency($config['frequency'])
                ->setPriority($config['priority']));
            $this->line("  ✓ Added: {$config['label']}");
        }

        // ========== CURRENCY PAGES ==========
        $this->info('Adding currency pages...');

        // Get ALL active currencies
        $currencies = Currency::where('is_active', true)->get();
        $this->line("  Found {$currencies->count()} active currencies");

        foreach ($currencies as $currency) {
            // Currency history page
            $sitemap->add(Url::create($baseUrl . "/history/{$currency->code}")
                ->setLastModificationDate($currency->updated_at ?? Carbon::now())
                ->setChangeFrequency('daily')
                ->setPriority(0.8));

            // Currency rate page
            $sitemap->add(Url::create($baseUrl . "/rates/{$currency->code}")
                ->setLastModificationDate($currency->updated_at ?? Carbon::now())
                ->setChangeFrequency('daily')
                ->setPriority(0.7));

            $this->line("  ✓ Added: {$currency->code} pages");
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
            $this->line("  ✓ Added: {$name}");
        }

        // Individual gold type pages from database
        $goldTypeRecords = GoldType::where('is_active', true)->get();
        foreach ($goldTypeRecords as $goldType) {
            $sitemap->add(Url::create($baseUrl . "/gold/{$goldType->id}")
                ->setLastModificationDate($goldType->updated_at ?? Carbon::now())
                ->setChangeFrequency('daily')
                ->setPriority(0.7));
            $this->line("  ✓ Added: Gold Type ID {$goldType->id}");
        }

        // ========== BLOG PAGES ==========
        $this->info('Adding blog pages...');

        // Blog homepage already added above

        // Individual blog posts
        $blogPosts = BlogPost::where('is_published', true)->get();
        $this->line("  Found {$blogPosts->count()} published blog posts");

        foreach ($blogPosts as $post) {
            $sitemap->add(Url::create($baseUrl . "/blog/{$post->slug}")
                ->setLastModificationDate($post->updated_at ?? $post->created_at)
                ->setChangeFrequency('weekly')
                ->setPriority(0.7));

            // Facebook share page (for OG tags)
            $sitemap->add(Url::create($baseUrl . "/blog/{$post->slug}/fb")
                ->setLastModificationDate($post->updated_at ?? $post->created_at)
                ->setChangeFrequency('weekly')
                ->setPriority(0.4));

            $this->line("  ✓ Added: {$post->title}");
        }


        // Blog categories 
        try {
            if (Schema::hasTable('blog_posts') && Schema::hasColumn('blog_posts', 'category')) {
                $categories = BlogPost::where('is_published', true)
                    ->whereNotNull('category')
                    ->select('category')
                    ->distinct()
                    ->pluck('category');

                foreach ($categories as $category) {
                    if ($category) {
                        $slug = strtolower(str_replace(' ', '-', $category));
                        $sitemap->add(Url::create($baseUrl . "/blog/category/{$slug}")
                            ->setLastModificationDate(Carbon::now())
                            ->setChangeFrequency('weekly')
                            ->setPriority(0.6));
                        $this->line("  ✓ Added: Category - {$category}");
                    }
                }
            } else {
                $this->line("  ℹ️ No category column found, skipping blog categories");
            }
        } catch (\Exception $e) {
            $this->line("  ⚠️ Could not load blog categories: " . $e->getMessage());
        }

        // ========== EXCHANGE RATE COMPARISON PAGES ==========
        $this->info('Adding exchange rate comparison pages...');

        // Get all currency codes for dynamic pairs
        $currencyCodes = $currencies->pluck('code')->toArray();

        // Generate popular pairs
        $popularPairs = [
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
            'KRW/MMK',
            'HKD/MMK',
            'NZD/MMK',
            'AUD/MMK',
            'CAD/MMK',
            'CHF/MMK'
        ];

        // Generate cross pairs between major currencies
        $majorCurrencies = ['USD', 'EUR', 'SGD', 'THB', 'JPY', 'CNY', 'MYR'];
        for ($i = 0; $i < count($majorCurrencies); $i++) {
            for ($j = $i + 1; $j < count($majorCurrencies); $j++) {
                $popularPairs[] = $majorCurrencies[$i] . '/' . $majorCurrencies[$j];
            }
        }

        $popularPairs = array_unique($popularPairs);

        foreach ($popularPairs as $pair) {
            $sitemap->add(Url::create($baseUrl . "/compare/{$pair}")
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency('daily')
                ->setPriority(0.6));
            $this->line("  ✓ Added: Compare {$pair}");
        }

        // ========== API ENDPOINTS (Optional - for SEO of API docs) ==========
        $this->info('Adding API documentation pages...');

        $apiEndpoints = [
            '/api/live-gold' => 'Live Gold Rate API',
            '/api/market-pulse' => 'Market Pulse API',
            '/api/gold-history' => 'Gold History API',
        ];

        foreach ($apiEndpoints as $endpoint => $name) {
            $sitemap->add(Url::create($baseUrl . $endpoint)
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency('hourly')
                ->setPriority(0.5));
            $this->line("  ✓ Added: {$name}");
        }

        // ========== STATIC UTILITY PAGES ==========
        $this->info('Adding utility pages...');

        $utilityPages = [
            '/sitemap' => 'Sitemap',
            '/generate-og-image' => 'OG Image Generator',
            '/default-og-image.jpg' => 'Default OG Image',
        ];

        foreach ($utilityPages as $path => $name) {
            $sitemap->add(Url::create($baseUrl . $path)
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency('monthly')
                ->setPriority(0.3));
            $this->line("  ✓ Added: {$name}");
        }

        // ========== AUTH PAGES (with lower priority) ==========
        $this->info('Adding authentication pages...');

        $authPages = [
            '/login' => 'Login',
            '/register' => 'Register',
            '/forgot-password' => 'Forgot Password',
            '/reset-password' => 'Reset Password',
            '/verify-email' => 'Verify Email',
            '/dashboard' => 'Dashboard',
        ];

        foreach ($authPages as $path => $name) {
            $sitemap->add(Url::create($baseUrl . $path)
                ->setChangeFrequency('monthly')
                ->setPriority(0.3));
            $this->line("  ✓ Added: {$name}");
        }

        // ========== USER DASHBOARD PAGES (if public) ==========
        $this->info('Adding user dashboard pages...');

        $userPages = [
            '/user/dashboard' => 'User Dashboard',
            '/user/watchlist' => 'Watchlist',
            '/user/alerts' => 'Price Alerts',
            '/user/notifications' => 'Notifications',
            '/user/assets' => 'Assets',
        ];

        foreach ($userPages as $path => $name) {
            $sitemap->add(Url::create($baseUrl . $path)
                ->setChangeFrequency('daily')
                ->setPriority(0.5));
            $this->line("  ✓ Added: {$name}");
        }

        // ========== ADMIN PAGES (with lower priority, noindex recommended) ==========
        // Note: Consider adding robots meta tag to noindex admin pages
        $this->info('Adding admin pages (low priority)...');

        $adminPages = [
            '/currencies' => 'Currency Management',
            '/currencies/factors' => 'Currency Factors',
            '/currencies/pending' => 'Pending Rates',
            '/currencies/settings' => 'Currency Settings',
            '/gold/gold/index' => 'Gold Management',
            '/gold/pending' => 'Pending Gold Prices',
            '/gold/create' => 'Add Gold Price',
            '/gold-types/create' => 'Add Gold Type',
            '/admin/blog' => 'Blog Management',
            '/admin/contacts' => 'Contact Messages',
            '/profile' => 'Profile Settings',
        ];

        foreach ($adminPages as $path => $name) {
            $sitemap->add(Url::create($baseUrl . $path)
                ->setChangeFrequency('weekly')
                ->setPriority(0.2));
            $this->line("  ✓ Added: {$name}");
        }

        // ========== SAVE THE SITEMAP ==========
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->newLine();
        $this->info('✅ Sitemap generated successfully!');
        $this->info('📁 Location: ' . public_path('sitemap.xml'));

        // Count total URLs
        $content = file_get_contents(public_path('sitemap.xml'));
        preg_match_all('/<loc>/', $content, $matches);
        $count = count($matches[0]);
        $this->info("📊 Total URLs in sitemap: {$count}");

        // Create sitemap index if needed (for large sitemaps >50,000 URLs)
        if ($count > 45000) {
            $this->warn('⚠️  Sitemap is large. Consider splitting into multiple sitemap files.');
        }

        return Command::SUCCESS;
    }
}
