<?php

namespace App\Http\Controllers;

use App\Domains\Users\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class WebAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'phone' => 'The provided credentials do not match our records.',
        ])->onlyInput('phone');
    }

    public function showRegister()
    {
        return view('auth.register-user');
    }

    public function showPartnerRegister()
    {
        return view('auth.register-partner');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:civilian,doctor,hospital,ambulance,security,fire',
            'blood_group' => 'nullable|string|max:10',
            'allergies' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'license' => 'required_if:role,doctor,hospital,ambulance,security,fire|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'additional_docs' => 'required_if:role,doctor,hospital,ambulance,security,fire|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'blood_group' => $request->blood_group,
            'allergies' => $request->allergies,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'license_path' => $request->hasFile('license') ? $request->file('license')->store('partner_docs', 'public') : null,
            'additional_docs_path' => $request->hasFile('additional_docs') ? $request->file('additional_docs')->store('partner_docs', 'public') : null,
        ]);

        // Create corresponding partner records so they appear in the Admin Panel
        if (in_array($user->role, ['ambulance', 'security', 'fire', 'doctor'])) {
            \App\Domains\Responders\Models\Responder::create([
                'user_id' => $user->id,
                'responder_type' => $user->role,
                'is_available' => true,
            ]);
        } elseif ($user->role === 'hospital') {
            \App\Domains\Responders\Models\Hospital::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'contact_phone' => $user->phone,
                'lat' => 0, // Default for now, can be updated later
                'lng' => 0,
            ]);
        }

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $request->only('email')
        );

        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request, $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(\Illuminate\Support\Str::random(60));

                $user->save();

                \Illuminate\Support\Facades\Event::dispatch(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
