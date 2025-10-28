<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required','string','min:6','regex:/^(?=.*[A-Z])(?=.*\d)[A-Za-z\d]+$/'],
            'tel' => 'required|max:30',
        ]);
        // ensure password not equal to name/email
        if (strcasecmp($request->password, $request->name) === 0 || strcasecmp($request->password, $request->email) === 0) {
            return back()->withErrors(['password' => 'Password must not be the same as your name or email.'])->withInput();
        }

        if(User::where("email", $request->email)->exists()) {
            return back()->withErrors(['email'=> 'Email already exists.']);
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tel' => $request->tel,
            'role' => 'customer',
        ]);

        return redirect()->route('login')->with('success', 'User created successfully');
    }

    public function login(Request $request) {
        $request->validate([
            'email'=> 'required|email',
            'password'=> 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->with('error', 'Account has not been registered ⚠️');
        }

        $remember = $request->boolean('remember');
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $remember)) {
            $request->session()->regenerate();

            // ✅ Check role after login
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.index'); // send to admin dashboard
            }
            if (Auth::user()->role === 'manager') {
                return redirect()->route('manager.index'); // send to manager dashboard
            }

            return redirect()->route('products.index'); // normal users
        }

        return back()->with('error','Invalid email or password ⚠️');
    }

    public function adminLogin(Request $request) {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Gate master credentials to first-login only; otherwise use stored password
        $admin = User::where('name', $request->username)->where('role', 'admin')->first();

        if ($admin && $admin->first_login) {
            // During first login, only allow master credentials
            if ($request->username === 'admin' && $request->password === 'admiN123456789') {
                Auth::login($admin);
                $request->session()->regenerate();
                return redirect()->route('admin.first-login');
            }
            return back()->with('error', 'Use the default admin credentials for first-time setup.');
        }

        // After first login is completed, require stored password
        if ($admin && Hash::check($request->password, $admin->password)) {
            Auth::login($admin);
            $request->session()->regenerate();
            return redirect()->route('admin.index');
        }

        return back()->with('error', 'Invalid admin credentials ⚠️');
    }

    public function showFirstLogin() {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin' || !$user->first_login) {
            return redirect()->route('login');
        }
        
        return view('etry.admin-first-login');
    }

    public function updateAdminProfile(Request $request) {
        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed', 'regex:/^(?=.*[A-Z])(?=.*\d)[A-Za-z\d]+$/'],
            'email' => ['required', 'email'],
        ]);

        $user = Auth::user();
        if (!$user || $user->role !== 'admin' || !$user->first_login) {
            return redirect()->route('login');
        }

        // Ensure password not equal to name/email
        if (strcasecmp($request->password, $user->name) === 0 || strcasecmp($request->password, $request->email) === 0) {
            return back()->withErrors(['password' => 'Password must not be the same as your name or email.'])->withInput();
        }

        $user->update([
            'password' => Hash::make($request->password),
            'email' => $request->email,
            'first_login' => false,
        ]);

        return redirect()->route('admin.index')->with('success', 'Admin profile updated successfully!');
    }

    public function isAdmin() {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public function logout() {
        Auth::logout();
        return redirect('/product');
    }

    public function showForgotPassword() {
        return view('etry.forgot-password');
    }

    public function sendResetLinkEmail(Request $request) {
        $request->validate(['email' => 'required|email']);

        if (!User::where('email', $request->email)->exists()) {
            return back()->withErrors(['email' => 'We can\'t find a user with that email address.']);
        }

        try {
            $status = Password::sendResetLink($request->only('email'));

            return $status === Password::RESET_LINK_SENT
                ? back()->with('status', __($status))
                : back()->withErrors(['email' => __($status)]);
        } catch (\Exception $e) {
            Log::error('Password reset error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Unable to send password reset email. Please try again later.']);
        }
    }

    public function showResetPassword(string $token) {
        return view('etry.reset-password', ['token' => $token, 'email' => request('email')]);
    }

    public function resetPassword(Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required','string','min:6','confirmed','regex:/^(?=.*[A-Z])(?=.*\d)[A-Za-z\d]+$/'],
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user && (strcasecmp($request->password, $user->name) === 0 || strcasecmp($request->password, $request->email) === 0)) {
            return back()->withErrors(['password' => 'Password must not be the same as your name or email.']);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->password = Hash::make($request->password);
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function showAdminForgotPassword() {
        return view('etry.admin-forgot-password');
    }

    public function resetAdminPassword(Request $request) {
        // Two-step flow: (1) send 6-digit code to email, (2) verify code + reset password

        // Step 1: Send code
        if ($request->input('step') === 'send') {
            $request->validate([
                'username' => 'required|string',
                'email' => 'required|email',
            ]);

            $admin = User::where('name', $request->username)
                ->where('role', 'admin')
                ->first();
            if (!$admin) {
                return back()->withErrors(['username' => 'Admin account not found.'])->withInput();
            }
            if (strcasecmp($admin->email, $request->email) !== 0) {
                return back()->withErrors(['email' => 'Email does not match our records.'])->withInput();
            }

            $code = (string) random_int(100000, 999999);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $admin->email],
                ['token' => Hash::make($code), 'created_at' => now()]
            );

            try {
                Mail::raw(
                    "Your admin password reset verification code is: {$code}\n\nThis code expires in 10 minutes.",
                    function ($message) use ($admin) {
                        $message->to($admin->email)->subject('Admin Password Reset Code');
                    }
                );
            } catch (\Throwable $e) {
                return back()->withErrors(['email' => 'Failed to send email. Please check SMTP settings.'])->withInput();
            }

            return back()
                ->with('success', 'Verification code sent to your email.')
                ->with('code_sent', true)
                ->withInput(['email' => $admin->email, 'username' => $admin->name]);
        }

        // Step 2: Verify code and reset password
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6',
            'password' => ['required', 'string', 'min:6', 'confirmed', 'regex:/^(?=.*[A-Z])(?=.*\d)[A-Za-z\d]+$/'],
        ]);

        $admin = User::where('email', $request->email)
            ->where('role', 'admin')
            ->first();
        if (!$admin) {
            return back()
                ->withErrors(['email' => 'Admin account not found.'])
                ->with('code_sent', true)
                ->withInput(['email' => $request->email]);
        }

        $record = DB::table('password_reset_tokens')->where('email', $admin->email)->first();
        if (!$record) {
            return back()
                ->withErrors(['code' => 'No verification code found. Please request a new code.'])
                ->with('code_sent', true)
                ->withInput(['email' => $request->email]);
        }

        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->lt(now()->subMinutes(10))) {
            DB::table('password_reset_tokens')->where('email', $admin->email)->delete();
            return back()
                ->withErrors(['code' => 'Verification code has expired. Please request a new code.'])
                ->with('code_sent', true)
                ->withInput(['email' => $request->email]);
        }

        if (!Hash::check($request->code, $record->token)) {
            return back()
                ->withErrors(['code' => 'Invalid verification code.'])
                ->with('code_sent', true)
                ->withInput(['email' => $request->email]);
        }

        if (strcasecmp($request->password, $admin->name) === 0 || strcasecmp($request->password, $admin->email) === 0) {
            return back()
                ->withErrors(['password' => 'Password must not be the same as your name or email.'])
                ->with('code_sent', true)
                ->withInput(['email' => $request->email]);
        }

        $admin->password = Hash::make($request->password);
        $admin->save();

        DB::table('password_reset_tokens')->where('email', $admin->email)->delete();

        return redirect()->route('login')->with('success', 'Admin password has been reset. You may now log in.');
    }
}
