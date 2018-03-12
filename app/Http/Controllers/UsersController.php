<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mail;
use App\Mail\AddUserVerification;
use Illuminate\Support\Facades\Hash;
use Validator;

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

        $validator = Validator::make($data, [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => $validator->errors(),
            ]);
        }

        $user = User::create([
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'email' => $data['email'],
            'verification_key' => md5(uniqid(rand(), true)),
            'verified' => false,
        ]);

        Mail::to($user->email)->send(new AddUserVerification($user));

        return response()->prettyjson(['status' => config('status.ok')]);
    }

}

?>
