<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user && $user->role === 'admin') {
            return view('settings_admin');
        }

        return view('settings');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'blood_group'               => 'nullable|string|max:10',
            'allergies'                 => 'nullable|string',
            'medical_conditions'        => 'nullable|string',
            'emergency_contact_name'    => 'nullable|string|max:255',
            'emergency_contact_phone'   => 'nullable|string|max:20',
        ];

        $changingPassword = filled($request->input('current_password')) || filled($request->input('new_password'));

        if ($changingPassword) {
            $rules['current_password']          = 'required|string';
            $rules['new_password']              = 'required|string|min:8|confirmed';
            $rules['new_password_confirmation'] = 'required|string';
        }

        $validated = $request->validate($rules);

        $fields = collect($validated)->only(['name', 'email', 'phone', 'blood_group', 'allergies',
            'medical_conditions', 'emergency_contact_name', 'emergency_contact_phone'])->toArray();

        if ($changingPassword) {
            if (!Hash::check($request->input('current_password'), $user->password)) {
                $error = ['current_password' => ['Current password is incorrect.']];
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['errors' => $error], 422);
                }
                return back()->withErrors($error)->withInput();
            }
            $fields['password'] = Hash::make($request->input('new_password'));
        }

        $user->update($fields);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => 'Profile updated successfully!']);
        }

        return back()->with('status', 'Profile updated successfully!');
    }

    public function deleteAccount(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            abort(403, 'Admin accounts cannot be self-deleted.');
        }

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->input('password'), $user->password)) {
            $error = ['password' => ['Password is incorrect.']];
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['errors' => $error], 422);
            }
            return back()->withErrors($error);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => 'Account deleted.', 'redirect' => route('login')]);
        }

        return redirect()->route('login');
    }
}
