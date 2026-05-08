<?php

namespace App\Http\Controllers;

use App\Domains\Users\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WebAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (Auth::attempt([$fieldType => $request->login, 'password' => $request->password])) {
            $user = Auth::user();
            
            if ($user->is_suspended) {
                Auth::logout();
                return back()->withErrors([
                    'login' => 'This account has been suspended. Please contact support.',
                ])->onlyInput('login');
            }

            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->onlyInput('login');
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
            'password' => $request->password,
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

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We can\'t find a user with that email address.']);
        }

        // Generate 6-digit code
        $code = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $code, 'created_at' => now()]
        );

        try {
            $user->sendPasswordResetNotification($code);
        } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Failed to send email. Gmail rejected your password. Please ensure your Google App Password is exactly 16 characters and correct in the .env file.']);
        } catch (\Exception $e) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Failed to send email due to a server error.']);
        }

        return redirect()->route('password.verify.code')->with('email', $request->email)->with('status', 'We have emailed your 6-digit password reset code!');
    }

    public function showVerifyCode(Request $request)
    {
        $email = session('email') ?? old('email');
        if (!$email) {
            return redirect()->route('password.request');
        }
        return view('auth.verify-code', ['email' => $email]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6'
        ]);

        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->code)
            ->first();

        if (!$tokenRecord) {
            return back()->withErrors(['code' => 'Invalid or expired code.'])->withInput();
        }

        return redirect()->route('password.reset', ['token' => $request->code, 'email' => $request->email]);
    }

    public function showResetPassword(Request $request, $token)
    {
        $email = $request->email ?? session('email');
        
        if (!$email) {
            return redirect()->route('password.request')->withErrors(['email' => 'Please enter your email to reset your password.']);
        }
        
        return view('auth.reset-password', ['token' => $token, 'email' => $email]);
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
            return back()->withErrors(['email' => 'Invalid or expired code.']);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We can\'t find a user with that email address.']);
        }

        $user->forceFill([
            'password' => $request->password
        ])->setRememberToken(Str::random(60));

        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Your password has been reset!');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
