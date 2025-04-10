<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request) {
        $request->validate([
            "name"=> "required",
            "email"=> "required|email|unique:users",
            "password"=> "required|min:6",
            'tel' => 'required|string|max:15',
        ]);

        if(User::where("email", $request->email)->exists()) {
            return back()->withErrors(['email'=> 'Email already exists.']);
        }

        $isFirstUser = User::count() == 0;

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tel' => $request->tel,
            'role' => $isFirstUser ? 'admin' : 'customer',
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

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect('/product');
        }

        return back()->with('error','Invalid email or password ⚠️');
    }

    public function isAdmin() {
        return Auth::check() && Auth::user()->id == 1;
    }

    public function logout() {
        Auth::logout();
        return redirect('/product');
    }
}
