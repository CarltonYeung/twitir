<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\AddUserVerification;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Mail;
use MongoDB;
use Validator;

class UsersController extends Controller
{
    private $client = null;

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
        if (!$this->client) {
            $this->client = new MongoDB\Client('mongodb://'.config('database.mongodb.host').':'.config('database.mongodb.port'));
        }

        $collection = $this->client->twitir->follows;

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

        if (Auth::user()->username === $data['username']) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => 'Cannot follow yourself.',
            ]);
        }

        if (!$this->client) {
            $this->client = new MongoDB\Client('mongodb://'.config('database.mongodb.host').':'.config('database.mongodb.port'));
        }

        $collection = $this->client->twitir->follows;

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

    public function getuser($username)
    {
        $user = User::select('email')->where('username', $username)->first();

        if (!$user) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => 'username does not exist',
            ]);
        }

        $email = $user->email;

        if (!$this->client) {
            $this->client = new MongoDB\Client('mongodb://'.config('database.mongodb.host').':'.config('database.mongodb.port'));
        }

        $collection = $this->client->twitir->follows;

        $user = $collection->findOne(['username' => $username]);

        return response()->prettyjson([
            'status' => config('status.ok'),
            'user' => [
                'email' => $email,
                'followers' => count($user->followers),
                'following' => count($user->following),
            ]
        ]);
    }

    public function getfollowers(Request $request, $username)
    {
        $limit = $request->query('limit');

        $validator = Validator::make(
            [
                'username' => $username, 
                'limit' => $limit
            ],
            [
                'username' => 'required|string|max:255',
                'limit' => 'nullable|integer|min:1|max:200',
            ]
        );

        if ($validator->fails()) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => $validator->errors(),
            ]);
        }

        $limit = $limit ? intval($limit) : 50;

        if (!$this->client) {
            $this->client = new MongoDB\Client('mongodb://'.config('database.mongodb.host').':'.config('database.mongodb.port'));
        }

        $collection = $this->client->twitir->follows;

        $user = $collection->findOne(
            ['username' => $username], 
            ['projection' => ['followers' => ['$slice' => $limit]]]
        );

        return response()->prettyjson([
            'status' => config('status.ok'),
            'users' => $user->followers,
        ]);
    }

    public function getfollowing(Request $request, $username)
    {
        $limit = $request->query('limit');

        $validator = Validator::make(
            [
                'username' => $username, 
                'limit' => $limit
            ],
            [
                'username' => 'required|string|max:255',
                'limit' => 'nullable|integer|min:1|max:200',
            ]
        );

        if ($validator->fails()) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => $validator->errors(),
            ]);
        }

        $limit = $limit ? intval($limit) : 50;

        if (!$this->client) {
            $this->client = new MongoDB\Client('mongodb://'.config('database.mongodb.host').':'.config('database.mongodb.port'));
        }

        $collection = $this->client->twitir->follows;

        $user = $collection->findOne(
            ['username' => $username], 
            ['projection' => ['following' => ['$slice' => $limit]]]
        );

        return response()->prettyjson([
            'status' => config('status.ok'),
            'users' => $user->following,
        ]);
    }

}
