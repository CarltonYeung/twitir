<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemsController extends Controller
{
    /**
     * Handle GET request.
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        return view('additem');
    }

    /**
     * Handle POST request.
     */
    public function additem(Request $request)
    {
    	if (!Auth::check()) {
            return response()->json([
                'status' => config('status.error'),
                'error' => config('status.unauthorized')
            ]);
        }

        return response()->json(['status' => config('status.ok')]);
    }

}

?>
