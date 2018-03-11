<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;

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

?>
