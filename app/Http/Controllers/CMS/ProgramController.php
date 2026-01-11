<?php

namespace App\Http\Controllers\CMS;

use App\Http\Requests\ProgramRequest;
use App\Models\Program;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProgramController
{
    public function index()
    {
        $programs = Program::query()
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Dashboard/CMS/Programs/Index', [
            'programs' => $programs,
        ]);
    }

    public function create()
    {
        return Inertia::render('Dashboard/CMS/Programs/Create');
    }

    public function store(ProgramRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['title']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('programs', 'public');
        }

        Program::create($data);

        return redirect()
            ->route('dashboard.cms.programs.index')
            ->with('success', 'Program berhasil ditambahkan');
    }

    public function edit(Program $program)
    {
        return Inertia::render('Dashboard/CMS/Programs/Edit', [
            'program' => $program,
        ]);
    }

    public function update(ProgramRequest $request, Program $program)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['title']);

        if ($request->hasFile('image')) {
            if ($program->image && \Storage::disk('public')->exists($program->image)) {
                \Storage::disk('public')->delete($program->image);
            }
            $data['image'] = $request->file('image')->store('programs', 'public');
        }

        $program->update($data);

        return redirect()
            ->route('dashboard.cms.programs.index')
            ->with('success', 'Program berhasil diperbarui');
    }

    public function destroy(Program $program)
    {
        if ($program->image && \Storage::disk('public')->exists($program->image)) {
            \Storage::disk('public')->delete($program->image);
        }

        $program->delete();

        return redirect()
            ->route('dashboard.cms.programs.index')
            ->with('success', 'Program berhasil dihapus');
    }
}
