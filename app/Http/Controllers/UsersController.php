<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mail;
use App\Mail\AddUserVerification;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
	/**
     * Handle GET request.
     */
    public function index()
    {
        return view('adduser');
    }

    /**
     * Handle POST request.
     */
    public function adduser(Request $request)
    {
        $data = $request->json()->all();

        $expected_parameters = ['username', 'password', 'email'];

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
         * Return error if username is not unique.
         */
        if (User::where('username', $data['username'])->first()) {
        	return response()->json([
        		'status' => config('status.error'),
        		'error' => config('status.unavailable').'username'
        	]);
        }
        
        /**
         * Return error if email is not unique.
         */
        if (User::where('email', $data['email'])->first()) {
        	return response()->json([
        		'status' => config('status.error'),
        		'error' => config('status.unavailable').'email'
        	]);
        }

        /**
         * Add user record to database.
         */
        $user = new User;
        $user->username = $data['username'];
        $user->password = Hash::make($data['password']);
        $user->email = $data['email'];
        $user->verification_key = md5(uniqid(rand(), true));
        $user->verified = false;
        $user->save();

        /**
         * Send verification email.
         */
        Mail::to($user->email)->send(new AddUserVerification($user));

        return response()->json(['status' => config('status.ok')]);
    }

}

?>
