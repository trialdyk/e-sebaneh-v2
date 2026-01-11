<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Post;
use App\Models\Program;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function index()
    {
        // Site Settings
        $site = [
            'name' => SiteSetting::get('site_name', 'Pondok Pesantren Al-Hikmah'),
            'tagline' => SiteSetting::get('site_tagline', 'Membentuk Generasi Berakhlak Mulia, Berilmu Tinggi'),
            'subtitle' => SiteSetting::get('site_subtitle', 'Sejak 1999'),
            'description' => SiteSetting::get('site_description', 'Pondok Pesantren Al-Hikmah adalah lembaga pendidikan Islam yang berfokus pada pembentukan karakter Islami dan pengembangan ilmu pengetahuan.'),
            'logo' => SiteSetting::get('logo') ? Storage::url(SiteSetting::get('logo')) : null,
            'visi' => SiteSetting::get('site_vision', 'Menjadi lembaga pendidikan Islam terdepan yang melahirkan generasi berakhlakul karimah, berilmu tinggi, dan bermanfaat bagi umat.'),
            'misi' => explode("\n", SiteSetting::get('site_mission', "Menyelenggarakan pendidikan Islam yang berkualitas dan berkarakter\nMembina santri menjadi hafidz dan hafidzah Al-Quran\nMengintegrasikan ilmu agama dan ilmu umum secara seimbang\nMembangun lingkungan islami yang kondusif untuk belajar\nMencetak kader-kader ulama dan pemimpin umat")),
            'stats' => [
                ['label' => 'Santri Aktif', 'value' => SiteSetting::get('stats_students', '500+')],
                ['label' => 'Guru & Ustadz', 'value' => SiteSetting::get('stats_teachers', '45')],
                ['label' => 'Tahun Berdiri', 'value' => SiteSetting::get('stats_year', '1999')],
                ['label' => 'Alumni', 'value' => SiteSetting::get('stats_alumni', '2000+')],
            ],
        ];

        // Programs
        $programs = Program::active()
            ->ordered()
            ->get(['title', 'icon', 'description'])
            ->map(function ($program) {
                return [
                    'icon' => $program->icon,
                    'title' => $program->title,
                    'description' => $program->description,
                ];
            });

        // Galleries for Section
        $galleries = Gallery::active()
            ->latest()
            ->take(6)
            ->get(['title', 'image', 'category'])
            ->map(function ($gallery) {
                return [
                    'title' => $gallery->title,
                    'image' => $gallery->image ? Storage::url($gallery->image) : null,
                    'category' => $gallery->category,
                ];
            });

        // Hero Slides (Use random galleries or specific ones)
        $heroSlides = Gallery::active()
            ->inRandomOrder()
            ->take(3)
            ->get(['image'])
            ->map(function ($gallery) {
                return [
                    'image' => $gallery->image ? Storage::url($gallery->image) : null,
                ];
            });

        // Fallback for hero if no galleries
        if ($heroSlides->isEmpty()) {
            $heroSlides = [
                ['image' => 'https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?w=1200'],
                ['image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?w=1200'],
                ['image' => 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?w=1200'],
            ];
        }

        // Testimonials
        $testimonials = Testimonial::active()
            ->ordered()
            ->take(3)
            ->get(['name', 'role', 'photo', 'quote'])
            ->map(function ($testimonial) {
                return [
                    'name' => $testimonial->name,
                    'role' => $testimonial->role,
                    'photo' => $testimonial->photo ? Storage::url($testimonial->photo) : null,
                    'quote' => $testimonial->quote,
                ];
            });

        // Latest News
        $news = Post::published()
            ->latest()
            ->take(3)
            ->get(['id', 'title', 'slug', 'excerpt', 'image', 'category', 'published_at'])
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'excerpt' => $post->excerpt,
                    'image' => $post->image ? Storage::url($post->image) : null,
                    'category' => $post->category,
                    'date' => $post->published_at->format('Y-m-d'),
                ];
            });

        $contact = [
            'address' => SiteSetting::get('contact_address', 'Jl. Raya Pesantren No. 123, Malang, Jawa Timur 65151'),
            'phone' => SiteSetting::get('contact_phone', '0341-123456'),
            'whatsapp' => SiteSetting::get('contact_whatsapp', '081234567890'),
            'email' => SiteSetting::get('contact_email', 'info@alhikmah.sch.id'),
            'maps' => SiteSetting::get('contact_maps', 'https://maps.google.com/?q=-7.9666,112.6326'),
        ];

        $social = [
            'facebook' => SiteSetting::get('social_facebook', 'https://facebook.com/alhikmah'),
            'instagram' => SiteSetting::get('social_instagram', 'https://instagram.com/alhikmah'),
            'youtube' => SiteSetting::get('social_youtube', 'https://youtube.com/@alhikmah'),
        ];

        return Inertia::render('Landing/Index', [
            'site' => $site,
            'programs' => $programs,
            'heroSlides' => $heroSlides,
            'galleries' => $galleries,
            'news' => $news,
            'testimonials' => $testimonials,
            'contact' => $contact,
            'social' => $social,
        ]);
    }

    public function news()
    {
        $news = Post::published()
            ->latest()
            ->paginate(9)
            ->through(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'excerpt' => $post->excerpt,
                    'image' => $post->image ? Storage::url($post->image) : null,
                    'category' => $post->category,
                    'date' => $post->published_at->format('Y-m-d'),
                ];
            });

        return Inertia::render('Landing/News', [
            'news' => $news,
        ]);
    }

    public function newsDetail($slug)
    {
        $news = Post::published()
            ->where('slug', $slug)
            ->firstOrFail();

        // Format for frontend
        $newsData = [
            'id' => $news->id,
            'title' => $news->title,
            'slug' => $news->slug,
            'content' => $news->content,
            'image' => $news->image ? Storage::url($news->image) : null,
            'category' => $news->category,
            'date' => $news->published_at->format('Y-m-d'),
            'author' => 'Admin', // Or $news->user->name if relationship exists
            'views' => $news->views,
        ];

        $news->increment('views');

        $relatedNews = Post::published()
            ->where('id', '!=', $news->id)
            ->where('category', $news->category)
            ->latest()
            ->take(3)
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'excerpt' => $post->excerpt,
                    'image' => $post->image ? Storage::url($post->image) : null,
                    'category' => $post->category,
                    'date' => $post->published_at->format('Y-m-d'),
                ];
            });

        return Inertia::render('Landing/NewsDetail', [
            'news' => $newsData,
            'relatedNews' => $relatedNews,
        ]);
    }
}
