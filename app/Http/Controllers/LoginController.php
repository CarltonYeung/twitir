<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
         * Handle an authentication attempt.
         *
         * @return Response
         */
        $query = [
            'username' => $data['username'], 
            'password' => $data['password'], 
            'verified' => true];
        $remember = true;
        if (Auth::attempt($query, $remember)) {
            return response()->json(['status' => config('status.ok')]);
        } else {
            return response()->json([
                'status' => config('status.error'),
                'error' => config('status.bad_login')
            ]);
        }
    }
}

?>