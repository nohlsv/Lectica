<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AccessLog;
use Illuminate\Support\Facades\Auth;

class LogAccess
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        // Only log web requests, skip assets, etc.
        if ($request->isMethod('get') || $request->isMethod('post') || $request->isMethod('patch') || $request->isMethod('delete') || $request->isMethod('options')) {
            AccessLog::create([
                'user_id' => Auth::id(),
                'route' => $request->path(),
                'method' => $request->method(),
                'accessed_at' => now(),
            ]);
        }
        return $response;
    }
}

