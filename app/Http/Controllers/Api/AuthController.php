<?php

namespace App\Http\Controllers\Api;

use App\Domains\Responders\Models\Hospital;
use App\Domains\Responders\Models\Responder;
use App\Domains\Users\Models\User;
use App\Http\Controllers\Controller;
use App\Support\UrlHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected function present(User $user): User
    {
        $user->avatar = UrlHelper::absolute($user->avatar);
        return $user;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|string|in:civilian,doctor,hospital,ambulance,security,fire',
            'blood_group' => 'nullable|string|max:10',
            'allergies' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'license' => 'required_if:role,doctor,hospital,ambulance,security,fire|url|max:2048',
            'additional_docs' => 'required_if:role,doctor,hospital,ambulance,security,fire|url|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $role = $request->role ?? 'civilian';

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $role,
            'blood_group' => $request->blood_group,
            'allergies' => $request->allergies,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'license_path' => $request->license,
            'additional_docs_path' => $request->additional_docs,
        ]);

        // Create the matching partner record so this user shows up correctly
        // in the admin panel and is dispatch-eligible — mirrors
        // WebAuthController::register exactly (the existing API endpoint this
        // replaces never did this, so partner roles silently didn't work).
        if (in_array($role, ['ambulance', 'security', 'fire', 'doctor'], true)) {
            Responder::create([
                'user_id' => $user->id,
                'responder_type' => $role,
                'is_available' => true,
            ]);
        } elseif ($role === 'hospital') {
            Hospital::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'contact_phone' => $user->phone,
                'lat' => 0,
                'lng' => 0,
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->present($user),
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if ($user->is_suspended) {
            return response()->json([
                'message' => 'This account has been suspended. Please contact support.',
                'suspended' => true,
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->present($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Same 6-digit-code flow as WebAuthController — reuses the same
     * `password_reset_tokens` table and notification, just JSON responses
     * instead of session-flashed redirects.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => "We can't find a user with that email address."], 422);
        }

        $code = str_pad((string) mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $code, 'created_at' => now()]
        );

        try {
            $user->sendPasswordResetNotification($code);
        } catch (\Throwable $e) {
            Log::error('Password reset email failure: ' . $e->getMessage());
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'Failed to send the reset code. Please try again.'], 500);
        }

        return response()->json(['message' => 'We have emailed your 6-digit password reset code.']);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->code)
            ->first();

        if (!$tokenRecord) {
            return response()->json(['message' => 'Invalid or expired code.'], 422);
        }

        return response()->json(['message' => 'Code verified.', 'valid' => true]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$tokenRecord) {
            return response()->json(['message' => 'Invalid or expired code.'], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => "We can't find a user with that email address."], 422);
        }

        $user->forceFill([
            'password' => $request->password,
        ])->setRememberToken(Str::random(60));
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Your password has been reset.']);
    }
}
