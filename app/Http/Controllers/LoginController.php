<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Handle GET request.
     */
    public function index(Request $request)
    {
        return view('login');
    }

    /**
     * Handle POST request.
     */
    public function login(Request $request)
    {
    	$data = $request->json()->all();

    	$expected_parameters = ['username', 'password'];

        /**
         * Return error if request was not well-formed.
         */
        foreach ($expected_parameters as $p) {
        	if (!array_key_exists($p, $data)) {
        		return response()->json([
        			'status' => config('status.error'),
        			'error' => config('status.missing').$p
        		]);
        	}
        }

        /**
         * Return error if request contains invalid values.
         */
        foreach ($expected_parameters as $p) {
        	if (!is_string($data[$p]) || strlen($data[$p]) < 1) {
        		return response()->json([
        			'status' => config('status.error'),
        			'error' => config('status.invalid').$p
        		]);
        	}
        }

        /**
         * Get the user from the database.
         */
        $user = User::where('username', $data['username'])->first();

        /**
         * Return error if the username is not registered.
         */
        if (!$user) {
        	return response()->json([
        		'status' => config('status.error'),
        		'error' => config('status.invalid').'username'
        	]);
        }

        /**
         * Return error if the user's email is not verified.
         */
        if (!$user->verified) {
            return response()->json([
                'status' => config('status.error'),
                'error' => config('status.email_not_verified')
            ]);
        }

        /**
         * Return error if the password is incorrect.
         */
        if (!Hash::check($data['password'], $user->password)) {
        	return response()->json([
        		'status' => config('status.error'),
        		'error' => config('status.invalid').'password'
        	]);
        }

        /**
         * Login
         */

        return response()->json(['status' => config('status.ok')]);
    }
}

?>