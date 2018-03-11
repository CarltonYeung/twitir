<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

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

        $validator = Validator::make($data, [
            'username' => 'required|string|max:255|exists:users',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => config('status.error'),
                'error' => $validator->errors()
            ]);
        }

        $query = [
            'username' => $data['username'], 
            'password' => $data['password'], 
            'verified' => true];

        $remember = true;

        if (!Auth::attempt($query, $remember)) {
            return response()->json([
                'status' => config('status.error'),
                'error' => config('status.login_failed')
            ]);
        }

        return response()->json(['status' => config('status.ok')]);
    }
}

?>