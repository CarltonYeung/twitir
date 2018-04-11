<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Cassandra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MongoDB;
use Validator;

class MediaController extends Controller
{
    private static $client = null;

    public function __construct()
    {
        self::$client = new MongoDB\Client('mongodb://'.config('database.mongodb.host').':'.config('database.mongodb.port'));
    }

    /**
     * Handle GET request.
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        return view('addmedia');
    }

}
