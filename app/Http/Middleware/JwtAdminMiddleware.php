<?php
namespace App\Http\Middleware;
use Closure;
use Exception;
use App\User;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
class JwtAdminMiddleware
{
    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->get('token');

        if(!$token) {
            // Unauthorized response if token not there
            return response()->json([
                'error' => 'Token not provided.'
            ], 401);
        }
        try {
            $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        } catch(ExpiredException $e) {
            return response()->json([
                'error' => 'Provided token is expired.'
            ], 401);
        } catch(Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'An error while decoding token.'
            ], 400);
        }
        $user = User::find($credentials->sub);
        $role = $user->role;

        if ($role != 'admin') {
            return response()->json([
                'error'=> 'admin privilege required',
            ], 400);
        }
        // Now let's put the user in the request class so that you can grab it from there
        $request->auth = $user;
        return $next($request);
    }
}