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

        try {

            $response =
                Http::withoutVerifying()
                ->post(
                    'https://sixties-pout-envoy.ngrok-free.dev/api/login',
                    [

                        'email' =>
                            $request->email,

                        'password' =>
                            $request->password

                    ]
                );


            $data =
                $response->json();


            Log::info(
                'LOGIN RESPONSE:',
                $data
            );


            /*
            ============================
            AMBIL TOKEN DARI RESPONSE
            ============================
            */

            $token =

                $data['access_token']
                ?? $data['token']
                ?? $data['user']['api_token']
                ?? $data['data']['token']
                ?? null;



            if (!$token) {

                Log::error(
                    'TOKEN TIDAK DITEMUKAN',
                    $data
                );

                return back()
                    ->with(
                        'error',
                        'Token tidak ditemukan dari API'
                    );

            }


            /*
            ============================
            SIMPAN TOKEN
            ============================
            */

            Session::put(
                'api_token',
                $token
            );


            Log::info(
                'TOKEN SAVED:',
                [

                    'token' =>
                        Session::get('api_token')

                ]
            );


            return redirect('/walk-in');

        }

        catch (\Exception $e) {

            Log::error(
                'LOGIN ERROR: '
                . $e->getMessage()
            );

            return back()
                ->with(
                    'error',
                    'Login API error'
                );

        }

    }



    public function logout()
    {

        $token =
            Session::get('api_token');


        if ($token) {

            Http::withHeaders([

                'Authorization' =>
                    'Bearer ' . $token

            ])
            ->withoutVerifying()
            ->post(
                'https://sixties-pout-envoy.ngrok-free.dev/api/logout'
            );

        }


        Session::forget('api_token');


        return redirect('/login');

    }

}