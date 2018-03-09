<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AddUserController extends Controller
{
    /**
     * Handle POST request.
     */
    public function adduser(Request $request)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return json_encode(['username' => $data['username']]);
    }

    /**
     * Handle GET request.
     */
    public function index()
    {
        return view('adduser');
    }

}

?>
