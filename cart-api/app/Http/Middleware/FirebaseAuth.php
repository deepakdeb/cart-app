<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class FirebaseAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        try {
            $factory = (new Factory)->withServiceAccount(storage_path('app/firebase-credentials.json'));
            $auth = $factory->createAuth();
            $verifiedToken = $auth->verifyIdToken($token);
            $uid = $verifiedToken->claims()->get('sub');

            // Find or create user in our DB
            $firebaseUser = $auth->getUser($uid);
            $user = User::firstOrCreate(
                ['firebase_uid' => $uid],
                [
                    'name'   => $firebaseUser->displayName ?? 'User',
                    'email'  => $firebaseUser->email,
                    'avatar' => $firebaseUser->photoUrl,
                ]
            );

            $request->merge(['auth_user' => $user]);
            $request->setUserResolver(fn() => $user);

        } catch (\Throwable $e) {
            return response()->json(['message' => 'Invalid or expired token.'], 401);
        }

        return $next($request);
    }
}