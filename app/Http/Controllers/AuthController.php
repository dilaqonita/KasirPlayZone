<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $response = Http::withoutVerifying()
                ->post('https://sixties-pout-envoy.ngrok-free.dev/api/login', [
                    'email' => $request->email,
                    'password' => $request->password,
                ]);

            $data = $response->json();

            Log::info('LOGIN RESPONSE:', $data ?? []);

            if (!$response->successful()) {
                return back()
                    ->withInput()
                    ->with('error', $data['message'] ?? 'Email atau password salah');
            }

            /*
            |--------------------------------------------------------------------------
            | Ambil token dari beberapa kemungkinan struktur response API
            |--------------------------------------------------------------------------
            */
            $token =
                $data['access_token']
                ?? $data['token']
                ?? ($data['user']['api_token'] ?? null)
                ?? ($data['data']['token'] ?? null)
                ?? null;

            if (!$token) {
                Log::error('TOKEN TIDAK DITEMUKAN', $data ?? []);

                return back()
                    ->withInput()
                    ->with('error', 'Token tidak ditemukan dari API');
            }

            /*
            |--------------------------------------------------------------------------
            | Simpan token dan data user ke session kasir web
            |--------------------------------------------------------------------------
            */
            Session::put('api_token', $token);

            if (isset($data['user'])) {
                Session::put('user', $data['user']);
            } elseif (isset($data['data']['user'])) {
                Session::put('user', $data['data']['user']);
            }

            Log::info('LOGIN SUCCESS, REDIRECT TO DASHBOARD', [
                'has_token' => Session::has('api_token'),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Redirect setelah login
            |--------------------------------------------------------------------------
            | Setelah login berhasil, user diarahkan ke dashboard.
            */
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            Log::error('LOGIN ERROR: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Login API error');
        }
    }

    public function logout()
    {
        $token = Session::get('api_token');

        if ($token) {
            try {
                Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])
                    ->withoutVerifying()
                    ->post('https://sixties-pout-envoy.ngrok-free.dev/api/logout');
            } catch (\Exception $e) {
                Log::error('LOGOUT API ERROR: ' . $e->getMessage());
            }
        }

        Session::forget('api_token');
        Session::forget('user');

        return redirect()->route('login');
    }
}
