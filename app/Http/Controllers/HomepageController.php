<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;

class HomepageController extends Controller
{
    /**
     * Show the homepage view.
     */
    public function index()
    {
        return view('homepage');
    }

}

?>
