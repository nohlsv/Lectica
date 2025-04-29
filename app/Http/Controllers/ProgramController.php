<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * Get a list of programs (useful for dropdowns)
     */
    public function index(): JsonResponse
    {
        $programs = Program::orderBy('name')->get(['id', 'name', 'code']);

        return response()->json($programs);
    }

    /**
     * Search programs by name or code
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query', '');

        $programs = Program::search($query)
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'code']);

        return response()->json($programs);
    }
}
