<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;

class HomepageController extends Controller
{
    /**
     * Handle GET request.
     */
    public function index()
    {
        return view('homepage');
    }

}
