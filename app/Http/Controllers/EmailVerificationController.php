<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Validator;

class EmailVerificationController extends Controller
{
    /**
     * Handle GET request.
     */
    public function index(Request $request)
    {
        return view('verify')->with([
        	'email' => $request->query('email', ''),
        	'key' => $request->query('key', ''),
        ]);
    }

    /**
     * Handle POST request.
     */
    public function verify(Request $request)
    {
    	$data = $request->json()->all();

    	$validator = Validator::make($data, [
            'email' => 'required|string|email|max:255|exists:users',
            'key' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => $validator->errors(),
            ]);
        }

        $user = User::where([
            'email' => $data['email'],
            'verification_key' => $data['key'],
            'verified' => false,
        ])->first();

        if (!$user) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => config('status.verification_failed'),
            ]);
        }

        $user->verified = true;
        $user->save();

        return response()->prettyjson(['status' => config('status.ok')]);
    }
}
