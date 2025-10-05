<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class FacultyController extends Controller
{
    /**
     * Check if user has faculty or admin privileges
     */
    private function checkFacultyAccess()
    {
        if (!in_array(auth()->user()->user_role, ['faculty', 'admin'])) {
            abort(403, 'Access denied. Faculty or admin privileges required.');
        }
    }

    /**
     * Show the faculty update page with programs and tags
     */
    public function update()
    {
        $this->checkFacultyAccess();
        $programs = Program::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get()->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'aliases' => $tag->aliases ?? [],
                'searchable_terms' => $tag->getSearchableTerms(),
                'files_count' => $tag->files()->count(),
            ];
        });

        return Inertia::render('Faculty/Update', [
            'programs' => $programs,
            'tags' => $tags,
        ]);
    }

    /**
     * Create a new program
     */
    public function storeProgram(Request $request)
    {
        $this->checkFacultyAccess();
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:programs,name',
            'code' => 'required|string|max:20|unique:programs,code',
            'college' => 'required|string|max:255',
        ]);

        $program = Program::create($validated);

        return back()->with('success', 'Program created successfully.');
    }

    /**
     * Update an existing program
     */
    public function updateProgram(Request $request, Program $program)
    {
        $this->checkFacultyAccess();
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('programs', 'name')->ignore($program->id),
            ],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('programs', 'code')->ignore($program->id),
            ],
            'college' => 'required|string|max:255',
        ]);

        $program->update($validated);

        return back()->with('success', 'Program updated successfully.');
    }

    /**
     * Delete a program
     */
    public function deleteProgram(Program $program)
    {
        $this->checkFacultyAccess();
        // Check if program has users before deleting
        if ($program->users()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete program with associated users.']);
        }

        $program->delete();

        return back()->with('success', 'Program deleted successfully.');
    }

    /**
     * Create a new tag
     */
    public function storeTag(Request $request)
    {
        $this->checkFacultyAccess();
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name',
            'aliases' => 'nullable|array',
            'aliases.*' => 'string|max:50',
        ]);

        // Filter out empty aliases
        if (isset($validated['aliases'])) {
            $validated['aliases'] = array_filter($validated['aliases'], function($alias) {
                return !empty(trim($alias));
            });
        }

        $tag = Tag::create($validated);

        return back()->with('success', 'Tag created successfully.');
    }

    /**
     * Update an existing tag
     */
    public function updateTag(Request $request, Tag $tag)
    {
        $this->checkFacultyAccess();
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tags', 'name')->ignore($tag->id),
            ],
            'aliases' => 'nullable|array',
            'aliases.*' => 'string|max:50',
        ]);

        // Filter out empty aliases
        if (isset($validated['aliases'])) {
            $validated['aliases'] = array_filter($validated['aliases'], function($alias) {
                return !empty(trim($alias));
            });
        }

        $tag->update($validated);

        return back()->with('success', 'Tag updated successfully.');
    }

    /**
     * Delete a tag
     */
    public function deleteTag(Tag $tag)
    {
        $this->checkFacultyAccess();
        // Check if tag has associated files before deleting
        if ($tag->files()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete tag with associated files.']);
        }

        $tag->delete();

        return back()->with('success', 'Tag deleted successfully.');
    }

    /**
     * Add an alias to a tag
     */
    public function addTagAlias(Request $request, Tag $tag)
    {
        $this->checkFacultyAccess();
        $validated = $request->validate([
            'alias' => 'required|string|max:50|min:1',
        ]);

        try {
            $tag->addAlias($validated['alias']);
            $tag->save();

            return back()->with('success', 'Alias added successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to add alias: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove an alias from a tag
     */
    public function removeTagAlias(Request $request, Tag $tag)
    {
        $this->checkFacultyAccess();
        $validated = $request->validate([
            'alias' => 'required|string|max:50',
        ]);

        try {
            $tag->removeAlias($validated['alias']);
            $tag->save();

            return back()->with('success', 'Alias removed successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to remove alias: ' . $e->getMessage()]);
        }
    }
}