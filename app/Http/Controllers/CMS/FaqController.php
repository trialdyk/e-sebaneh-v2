<?php

namespace App\Http\Controllers\CMS;

use App\Http\Requests\FaqRequest;
use App\Models\Faq;
use Inertia\Inertia;

class FaqController
{
    public function index()
    {
        $faqs = Faq::query()
            ->orderBy('order')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return Inertia::render('Dashboard/CMS/Faqs/Index', [
            'faqs' => $faqs,
        ]);
    }

    public function create()
    {
        return Inertia::render('Dashboard/CMS/Faqs/Create');
    }

    public function store(FaqRequest $request)
    {
        Faq::create($request->validated());

        return redirect()
            ->route('dashboard.cms.faqs.index')
            ->with('success', 'FAQ berhasil ditambahkan');
    }

    public function edit(Faq $faq)
    {
        return Inertia::render('Dashboard/CMS/Faqs/Edit', [
            'faq' => $faq,
        ]);
    }

    public function update(FaqRequest $request, Faq $faq)
    {
        $faq->update($request->validated());

        return redirect()
            ->route('dashboard.cms.faqs.index')
            ->with('success', 'FAQ berhasil diperbarui');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()
            ->route('dashboard.cms.faqs.index')
            ->with('success', 'FAQ berhasil dihapus');
    }
}
