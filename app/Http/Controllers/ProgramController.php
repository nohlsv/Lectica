<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgramController extends Controller
{
    /**
     * Get a list of programs (useful for dropdowns)
     */
    public function index(Request $request)
    {
        $programs = Program::orderBy('name')->get(['id', 'name', 'code']);

        // Return JSON for AJAX requests, redirect to appropriate page for browser requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($programs);
        }

        // Redirect to a proper page for browser requests
        return redirect()->route('home')->with('error', 'This endpoint is only available for AJAX requests.');
    }

    /**
     * Search programs by name or code
     */
    public function search(Request $request)
    {
        $query = $request->input('query', '');

        $programs = Program::search($query)
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'code']);

        // Return JSON for AJAX requests, redirect to appropriate page for browser requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($programs);
        }

        // Redirect to a proper page for browser requests
        return redirect()->route('home')->with('error', 'This endpoint is only available for AJAX requests.');
    }
}
