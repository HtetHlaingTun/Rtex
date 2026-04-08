<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

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
            ]
        ]);
    }

    public function show($slug)
    {
        $post = BlogPost::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Increment view count
        $post->incrementViews();

        // Get related posts
        $relatedPosts = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->whereMonth('published_at', $post->published_at?->month)
            ->limit(3)
            ->get();

        // Prepare absolute URLs for social sharing
        $metaTitle = $post->meta_title ?? $post->title;
        $metaDescription = $post->meta_description ?? $post->excerpt;

        // Fallback description if empty
        if (empty($metaDescription)) {
            $metaDescription = Str::limit(strip_tags($post->content), 160);
        }

        // Absolute URL for image (required by Facebook)
        $featuredImage = $post->featured_image
            ? (str_starts_with($post->featured_image, 'http')
                ? $post->featured_image
                : url($post->featured_image))
            : url('/og-image.jpg');

        return Inertia::render('Blog/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'meta' => [
                'title' => $metaTitle,
                'description' => $metaDescription,
                'image' => $featuredImage,
                'url' => url()->current(),
            ]
        ]);
    }
}
