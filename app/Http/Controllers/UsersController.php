<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mail;
use App\Mail\AddUserVerification;
use Illuminate\Support\Facades\Hash;
use Validator;
use MongoDB;
use Illuminate\Support\Facades\Auth;

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

        // Add user to MySQL
        $user = User::create([
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'email' => $data['email'],
            'verification_key' => md5(uniqid(rand(), true)),
            'verified' => false,
        ]);

        // Create a Mongo document for the user
        $client = new MongoDB\Client('mongodb://'.config('database.mongodb.host').':'.config('database.mongodb.port'));

        $collection = $client->twitir->follow;

        $collection->insertOne([
            'username' => $data['username'],
            'following' => [],
            'followers' => [],
        ]);

        Mail::to($user->email)->send(new AddUserVerification($user));

        return response()->prettyjson(['status' => config('status.ok')]);
    }

    public function follow(Request $request)
    {
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'username' => 'required|string|max:255',
            'follow' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => $validator->errors(),
            ]);
        }

        if (!Auth::check()) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => config('status.unauthorized'),
            ]);
        }

        $client = new MongoDB\Client('mongodb://'.config('database.mongodb.host').':'.config('database.mongodb.port'));

        $collection = $client->twitir->follow;

        $followee = $collection->findOne([
            'username' => $data['username'],
        ]);

        if (!$followee) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => 'username does not exist',
            ]);
        }

        $update_operation = $data['follow'] ? '$addToSet' : '$pull';

        $collection->updateOne(
            ['username' => Auth::user()->username],
            [$update_operation => ['following' => $data['username']],
        ]);

        $collection->updateOne(
            ['username' => $data['username']],
            [$update_operation => ['followers' => Auth::user()->username],
        ]);
        
        return response()->prettyjson(['status' => config('status.ok')]);
    }

}

?>
