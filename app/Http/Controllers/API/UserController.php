<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|min:3|max:255',
                'phone' => 'string|nullable|min:3|max:16',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8|max:255',
                'password_confirmation' => 'required|same:password',
            ]);

            User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user = User::where('email', $request->email)->first();

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'error' => false,
                'message' => 'User created successfully',
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to create user',
                'error_alert' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $url = env('GOOGLE_CLOUD_STORAGE_BUCKET_URI');
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $credential = request(['email', 'password']);

            if (!auth()->attempt($credential)) {
                return response()->json([
                    'error' => true,
                    'message' => 'Login failed | email or password is wrong',
                    'error_alert' => 'Unauthorized',
                ], 500);
            }

            $user = User::where('email', $request->email)->first();

            if (!Hash::check($request->password, $user->password)) {
                throw new \Exception('Invalid credentials');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'error' => false,
                'message' => 'Login success',
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user,
                'photo_profile' => $user->photo ? $url . '/public/' . $user->photo : null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to login',
                'error_alert' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'error' => false,
                'message' => 'Logout success',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to logout',
                'error_alert' => 'Token is Invalid or Not Found',
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {

            $url = env('GOOGLE_CLOUD_STORAGE_BUCKET_URI');
            $request->validate([
                'name' => 'string|min:3|max:255',
                'phone' => 'string|nullable|min:3|max:16',
                'email' => 'email',
                'photo' => 'image|mimes:jpg,jpeg,png|max:2048',
            ]);

            $user = Auth::user();

            if ($request->hasFile('photo')) {
                // cek apakah user memiliki foto di google storage
                if ($user->photo) {
                    // jika ada, hapus foto di google storage
                    Storage::disk('gcs')->delete('public/' . $user->photo);
                }

                // simpan foto baru ke google storage
                $photoName = $request->file('photo')->store('public');

                // ambil nama file
                $photoName = explode('/', $photoName)[1];
            }

            $user->update([
                'name' => $request->name ? $request->name : $user->name,
                'phone' => $request->phone ? $request->phone : $user->phone,
                'email' => $request->email ? $request->email : $user->email,
                'photo' => $request->hasFile('photo') ? $photoName : $user->photo,
            ]);

            return response()->json([
                'error' => false,
                'message' => 'Profile updated successfully',
                'user' => $user,
                'photo_profile' => $user->photo ? $url . '/public/' . $user->photo : null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to update profile',
                'error_alert' => $e->getMessage(),
            ], 500);
        }
    }
}
