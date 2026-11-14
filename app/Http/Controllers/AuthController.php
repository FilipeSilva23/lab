<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        //verifies the information, both fields are required and email has to be an email
        $login_info = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string']
        ]);

        //tries to log in, and checks if remember is true or false
        if (Auth::attempt($login_info, $request->boolean('remember'))) {
            //new session ID
            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        //if cant log in
        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        //removes auth information
        Auth::logout();
        //cleans everything
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
