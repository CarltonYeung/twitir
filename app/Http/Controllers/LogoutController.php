<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    /**
     * Handle POST request.
     */
    public function logout()
    {
        Auth::logout();
        return response()->json(['status' => config('status.ok')]);
    }
}

?>