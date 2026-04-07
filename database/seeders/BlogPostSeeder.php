<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run()
    {
        $posts = [
            [
                'title' => 'USD to MMK Exchange Rate Today: Market Analysis',
                'slug' => 'usd-to-mmk-exchange-rate-analysis',
                'excerpt' => 'Current USD to MMK exchange rates and what factors are influencing the market today.',
                'content' => "<p>The US Dollar to Myanmar Kyat exchange rate is currently showing strong activity in the market. Today's rates show USD buying at 4,324.26 MMK and selling at 4,453.99 MMK.</p><h2>What\'s Driving the Market?</h2>
<p>Several factors are influencing the USD/MMK exchange rate today:</p>"
                    . '<ul>
<li>Regional economic conditions</li>
<li>Global dollar strength</li>
<li>Local demand for foreign currency</li>
</ul>

<h2>What to Expect</h2>
<p>Market analysts predict continued stability in the near term, with potential fluctuations based on import/export activities.</p>',
                'is_published' => true,
                'published_at' => now(),
            ],
            [
                'title' => 'Gold Price in Myanmar: Why It\'s Rising',
                'slug' => 'gold-price-myanmar-rising',
                'excerpt' => 'Analysis of gold price trends in Myanmar and global factors affecting the market.',
                'content' => '<p>Gold prices in Myanmar have seen significant movement recently. The current gold price is 11,075,224 MMK per kyatthar.</p>

<h2>Global Gold Trends</h2>
<p>International gold prices are trading at $4,713.90 per ounce, influenced by:</p>
<ul>
<li>Global economic uncertainty</li>
<li>Central bank buying</li>
<li>Inflation concerns</li>
</ul>

<h2>Local Market Impact</h2>
<p>Myanmar\'s gold market typically follows international trends with local adjustments based on supply and demand.</p>',
                'is_published' => true,
                'published_at' => now(),
            ],
            [
                'title' => 'SGD to MMK: Singapore Dollar Exchange Rate Update',
                'slug' => 'sgd-to-mmk-exchange-rate-update',
                'excerpt' => 'Latest SGD to MMK exchange rates and what it means for travelers and businesses.',
                'content' => '<p>The Singapore Dollar is currently trading at 3,360.98 MMK for buying and 3,461.81 MMK for selling.</p>

<h2>Why SGD Matters for Myanmar</h2>
<p>Singapore is a key trading partner and many Myanmar businesses deal in SGD for imports and exports.</p>

<h2>Travel Tips</h2>
<p>If you\'re planning to travel to Singapore, now might be a good time to exchange currency given current rates.</p>',
                'is_published' => true,
                'published_at' => now(),
            ],
            [
                'title' => 'THB to MMK: Thai Baht Exchange Rate Insights',
                'slug' => 'thb-to-mmk-exchange-rate-insights',
                'excerpt' => 'Thai Baht exchange rates and how it affects border trade and tourism.',
                'content' => '<p>The Thai Baht is currently trading at 131.63 MMK for buying and 135.58 MMK for selling.</p>

<h2>Border Trade Impact</h2>
<p>Given Myanmar\'s strong border trade with Thailand, THB exchange rates directly affect businesses in states like Kayin, Mon, and Shan.</p>

<h2>Tourism Recovery</h2>
<p>As tourism recovers, understanding THB rates becomes increasingly important for travelers.</p>',
                'is_published' => true,
                'published_at' => now(),
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::updateOrCreate(
                ['slug' => $post['slug']],
                $post
            );
        }

        $this->command->info('Blog posts seeded successfully!');
    }
}
