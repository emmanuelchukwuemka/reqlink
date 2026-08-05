<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Support\UrlHelper;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::with('author')
            ->published()
            ->latest('published_at')
            ->paginate(9);

        $posts->getCollection()->transform(fn ($post) => $this->present($post));

        return response()->json($posts);
    }

    public function show($slug)
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();

        abort_unless($post->is_published && $post->published_at && $post->published_at->lte(now()), 404);

        return response()->json($this->present($post));
    }

    protected function present(BlogPost $post): BlogPost
    {
        $post->cover_image = UrlHelper::absolute($post->cover_image);
        return $post;
    }
}
