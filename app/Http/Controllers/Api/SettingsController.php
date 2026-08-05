<?php

namespace App\Http\Controllers\Api;

use App\Domains\Responders\Models\Responder;
use App\Http\Controllers\Controller;
use App\Support\UrlHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    private function avatarUploadDir(): string
    {
        $override = env('PUBLIC_HTML_PATH');
        return $override ? rtrim($override, '/') . '/uploads/avatars' : public_path('uploads/avatars');
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $responderRoles = ['ambulance', 'fire', 'security'];

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'blood_group' => 'nullable|string|max:10',
            'allergies' => 'nullable|string',
            'medical_conditions' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            // extensions: (not mimes:) — mimes: needs php_fileinfo, which isn't
            // enabled on this host; extensions: checks the filename only.
            'avatar' => 'nullable|file|extensions:jpg,jpeg,png,webp|max:3072',
        ];

        if (in_array($user->role, $responderRoles, true)) {
            $rules['vehicle_reg'] = 'nullable|string|max:20';
            $rules['capacity'] = 'nullable|integer|min:1|max:20';
        }

        $changingPassword = filled($request->input('current_password')) || filled($request->input('new_password'));

        if ($changingPassword) {
            $rules['current_password'] = 'required|string';
            $rules['new_password'] = 'required|string|min:8|confirmed';
            $rules['new_password_confirmation'] = 'required|string';
        }

        $validated = $request->validate($rules);

        $fields = collect($validated)->only([
            'name', 'email', 'phone', 'blood_group', 'allergies',
            'medical_conditions', 'emergency_contact_name', 'emergency_contact_phone',
        ])->toArray();

        if ($request->hasFile('avatar')) {
            $dir = $this->avatarUploadDir();
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            $oldAvatar = $user->avatar;
            $filename = 'avatar-' . $user->id . '-' . time() . '.' . $request->file('avatar')->getClientOriginalExtension();
            $request->file('avatar')->move($dir, $filename);
            $fields['avatar'] = '/uploads/avatars/' . $filename;

            if ($oldAvatar && str_starts_with($oldAvatar, '/uploads/avatars/')) {
                $oldPath = $dir . '/' . basename($oldAvatar);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }
        }

        if ($changingPassword) {
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return response()->json(['errors' => ['current_password' => ['Current password is incorrect.']]], 422);
            }
            $fields['password'] = Hash::make($request->input('new_password'));
        }

        $user->update($fields);

        if (in_array($user->role, $responderRoles, true)) {
            $responder = Responder::where('user_id', $user->id)->first();
            if ($responder) {
                $responder->update([
                    'vehicle_reg' => $validated['vehicle_reg'] ?? null,
                    'capacity' => $validated['capacity'] ?? $responder->capacity,
                ]);
            }
        }

        return response()->json([
            'status' => 'Profile updated successfully!',
            'avatar_url' => UrlHelper::absolute($user->fresh()->avatar),
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            abort(403, 'Admin accounts cannot be self-deleted.');
        }

        $request->validate(['password' => 'required|string']);

        if (!Hash::check($request->input('password'), $user->password)) {
            return response()->json(['errors' => ['password' => ['Password is incorrect.']]], 422);
        }

        $user->currentAccessToken()->delete();
        $user->delete();

        return response()->json(['status' => 'Account deleted.']);
    }
}
