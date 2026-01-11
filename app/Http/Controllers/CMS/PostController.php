<?php

namespace App\Http\Controllers\CMS;

use App\Http\Requests\PostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PostController
{
    public function index(Request $request)
    {
        $posts = Post::query()
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            })
            ->when($request->category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dashboard/CMS/Posts/Index', [
            'posts' => $posts,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Dashboard/CMS/Posts/Create');
    }

    public function store(PostRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['title']);

        if ($request->is_published) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        Post::create($data);

        return redirect()
            ->route('dashboard.cms.posts.index')
            ->with('success', 'Berita berhasil ditambahkan');
    }

    public function edit(Post $post)
    {
        return Inertia::render('Dashboard/CMS/Posts/Edit', [
            'post' => $post,
        ]);
    }

    public function update(PostRequest $request, Post $post)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['title']);

        // Set published_at if publishing for the first time
        if ($request->is_published && ! $post->published_at) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($post->image && \Storage::disk('public')->exists($post->image)) {
                \Storage::disk('public')->delete($post->image);
            }
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        $post->update($data);

        return redirect()
            ->route('dashboard.cms.posts.index')
            ->with('success', 'Berita berhasil diperbarui');
    }

    public function destroy(Post $post)
    {
        // Delete image if exists
        if ($post->image && \Storage::disk('public')->exists($post->image)) {
            \Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return redirect()
            ->route('dashboard.cms.posts.index')
            ->with('success', 'Berita berhasil dihapus');
    }
}
