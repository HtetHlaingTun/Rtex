<?php

namespace App\Http\Controllers;

use App\Helpers\BreadcrumbHelper;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::published()
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
            'meta' => [
                'title' => 'Exchange Rate Blog - Market Insights & Updates',
                'description' => 'Latest updates on Myanmar exchange rates, gold prices, and market analysis. Expert insights on USD, SGD, EUR, THB to MMK.'
            ],
            'breadcrumbs' => BreadcrumbHelper::generate('Blog')
        ]);
    }

    public function show($slug)
    {
        $post = BlogPost::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $post->incrementViews();

        $relatedPosts = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->whereMonth('published_at', $post->published_at?->month)
            ->limit(3)
            ->get();

        // Generate image URL
        $image = $post->featured_image
            ? (filter_var($post->featured_image, FILTER_VALIDATE_URL)
                ? $post->featured_image
                : url($post->featured_image))
            : url('/default-og-image.jpg');

        $title = $post->meta_title ?? $post->title;
        $description = $post->meta_description ?? Str::limit(strip_tags($post->content), 160);

        // CRITICAL: Share meta data globally for Blade layout
        view()->share('og_meta', [
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'url' => url()->current(),
        ]);

        return Inertia::render('Blog/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'og_meta' => [
                'title' => $title,
                'description' => $description,
                'image' => $image,
                'url' => url()->current(),
            ],
            'breadcrumbs' => [
                ['label' => 'Home', 'route' => 'welcome'],
                ['label' => 'Blog', 'route' => 'blog.index'],
                ['label' => $post->title, 'route' => null]  // ✅ Shows actual post title
            ]
        ]);
    }
}
