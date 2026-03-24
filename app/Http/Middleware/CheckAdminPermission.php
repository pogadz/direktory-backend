<?php

namespace App\Http\Middleware;

use App\Models\Profile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminPermission
{
    /**
     * Verify the session-authenticated user has an admin profile with the required permissions.
     * Mirrors CheckPermission but uses the session instead of Sanctum token abilities.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('admin.login');
        }

        $profileId = $request->session()->get('admin_profile_id');
        $profile   = Profile::find($profileId);

        if (!$profile || $profile->user_id !== $user->id || !$profile->is_active) {
            return redirect()->route('admin.login');
        }

        if (!empty($permissions) && !$profile->hasAnyPermission($permissions)) {
            abort(403, 'Insufficient permissions.');
        }

        $request->merge(['admin_profile' => $profile]);

        return $next($request);
    }
}
