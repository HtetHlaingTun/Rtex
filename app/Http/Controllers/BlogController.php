<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;
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

        // Get related posts (same month or category)
        $relatedPosts = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->whereMonth('published_at', $post->published_at?->month)
            ->limit(3)
            ->get();

        return Inertia::render('Blog/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'meta' => [
                'title' => $post->meta_title ?? $post->title,
                'description' => $post->meta_description ?? $post->excerpt
            ]
        ]);
    }
}
