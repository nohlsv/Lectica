<?php

namespace App\Http\Middleware;

use App\Services\QuestService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AssignDailyQuests
{
    public function __construct(
        private QuestService $questService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only assign quests to authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            $this->questService->assignDailyQuests($user);
        }

        return $next($request);
    }
}
