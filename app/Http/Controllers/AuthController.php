<?php

namespace App\Http\Controllers;

use App\Modules\User\Models\User;
use App\Modules\Student\Models\Student;
use App\Modules\Grade\Models\Grade;
use App\Modules\City\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('topics');
        }

        return view('auth.login', [
            'isIndex' => true,
            'hideHeader' => true,
            'hideNavbar' => true,
        ]);
    }

    public function loginPost(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if (!$user->is_approved) {
            return redirect()->route('pending');
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended(route('topics'));
    }

    public function signup()
    {
        if (Auth::check()) {
            return redirect()->route('topics');
        }

        return view('auth.signup', [
            'isIndex' => true,
            'hideHeader' => true,
            'hideNavbar' => true,
            'cities' => City::all(),
            'grades' => Grade::all(),
        ]);
    }

    public function signupPost(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'city_id' => 'nullable|exists:cities,id',
            'grade_id' => 'nullable|exists:grades,id',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'type' => 'student',
            ]);

            $user->assignRole('student');

            Student::create([
                'user_id' => $user->id,
                'city_id' => $request->city_id,
                'grade_id' => $request->grade_id,
            ]);
        });

        return redirect()->route('pending')
            ->with('success', __('auth.registration_pending'));
    }

    public function pending()
    {
        if (Auth::check()) {
            return redirect()->route('topics');
        }

        return view('auth.pending', [
            'isIndex' => true,
            'hideHeader' => true,
            'hideNavbar' => true,
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
