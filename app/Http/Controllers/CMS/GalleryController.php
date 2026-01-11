<?php

namespace App\Http\Controllers\CMS;

use App\Http\Requests\GalleryRequest;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GalleryController
{
    public function index(Request $request)
    {
        $galleries = Gallery::query()
            ->when($request->category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return Inertia::render('Dashboard/CMS/Galleries/Index', [
            'galleries' => $galleries,
            'filters' => $request->only(['category']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Dashboard/CMS/Galleries/Create');
    }

    public function store(GalleryRequest $request)
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('galleries', 'public');
        }

        Gallery::create($data);

        return redirect()
            ->route('dashboard.cms.galleries.index')
            ->with('success', 'Galeri berhasil ditambahkan');
    }

    public function edit(Gallery $gallery)
    {
        return Inertia::render('Dashboard/CMS/Galleries/Edit', [
            'gallery' => $gallery,
        ]);
    }

    public function update(GalleryRequest $request, Gallery $gallery)
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($gallery->image) {
                if (\Storage::disk('public')->exists($gallery->image)) {
                    \Storage::disk('public')->delete($gallery->image);
                }
            }
            $data['image'] = $request->file('image')->store('galleries', 'public');
        } else {
            // If no new image, ensure we don't accidentally unset it if logic elsewhere sends null
            if (array_key_exists('image', $data) && is_null($data['image'])) {
                unset($data['image']);
            }
        }

        $gallery->update($data);

        return redirect()
            ->route('dashboard.cms.galleries.index')
            ->with('success', 'Galeri berhasil diperbarui');
    }

    public function destroy(Gallery $gallery)
    {
        if ($gallery->image && \Storage::disk('public')->exists($gallery->image)) {
            \Storage::disk('public')->delete($gallery->image);
        }

        $gallery->delete();

        return redirect()
            ->route('dashboard.cms.galleries.index')
            ->with('success', 'Galeri berhasil dihapus');
    }
}
