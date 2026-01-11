<?php

namespace App\Http\Controllers\CMS;

use App\Http\Requests\TestimonialRequest;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TestimonialController
{
    public function index()
    {
        $testimonials = Testimonial::query()
            ->orderBy('order')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return Inertia::render('Dashboard/CMS/Testimonials/Index', [
            'testimonials' => $testimonials,
        ]);
    }

    public function create()
    {
        return Inertia::render('Dashboard/CMS/Testimonials/Create');
    }

    public function store(TestimonialRequest $request)
    {
        $data = $request->validated();

        // Handle photo upload if provided
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('testimonials', 'public');
        }

        Testimonial::create($data);

        return redirect()
            ->route('dashboard.cms.testimonials.index')
            ->with('success', 'Testimoni berhasil ditambahkan');
    }

    public function edit(Testimonial $testimonial)
    {
        return Inertia::render('Dashboard/CMS/Testimonials/Edit', [
            'testimonial' => $testimonial,
        ]);
    }

    public function update(TestimonialRequest $request, Testimonial $testimonial)
    {
        $data = $request->validated();

        // Handle photo upload if provided
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($testimonial->photo) {
                if (\Storage::disk('public')->exists($testimonial->photo)) {
                    \Storage::disk('public')->delete($testimonial->photo);
                }
            }
            $data['photo'] = $request->file('photo')->store('testimonials', 'public');
        } else {
            // If no new photo, keep the old one (remove from data to prevent nulling it if it was passed as null)
            // But wait, if we are sending multipart/form-data, empty file input might verify as null?
            // The request validation says 'photo' is nullable.
            // If it's null in data, it might overwrite the existing photo with null?
            // Let's check if the key exists in the request.
            if (array_key_exists('photo', $data) && is_null($data['photo'])) {
                unset($data['photo']);
            }
        }

        $testimonial->update($data);

        return redirect()
            ->route('dashboard.cms.testimonials.index')
            ->with('success', 'Testimoni berhasil diperbarui');
    }

    public function destroy(Testimonial $testimonial)
    {
        // Delete photo if exists
        if ($testimonial->photo) {
            \Storage::disk('public')->delete($testimonial->photo);
        }

        $testimonial->delete();

        return redirect()
            ->route('dashboard.cms.testimonials.index')
            ->with('success', 'Testimoni berhasil dihapus');
    }
}
