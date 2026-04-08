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

        $post->incrementViews();

        $relatedPosts = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->whereMonth('published_at', $post->published_at?->month)
            ->limit(3)
            ->get();

        // Generate absolute URL for featured image
        $featuredImage = $post->featured_image
            ? (filter_var($post->featured_image, FILTER_VALIDATE_URL)
                ? $post->featured_image
                : url($post->featured_image))
            : url('/default-og-image.jpg');

        $metaTitle = $post->meta_title ?? $post->title;
        $metaDescription = $post->meta_description ?? Str::limit(strip_tags($post->content), 160);

        // Share meta data with Inertia
        return Inertia::render('Blog/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'meta' => [
                'title' => $metaTitle,
                'description' => $metaDescription,
                'image' => $featuredImage,
                'url' => url()->current(),
            ]
        ])->withViewData([
            'meta' => [
                'title' => $metaTitle,
                'description' => $metaDescription,
                'image' => $featuredImage,
                'url' => url()->current(),
            ]
        ]);
    }
}
