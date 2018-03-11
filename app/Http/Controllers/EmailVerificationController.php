<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    /**
     * Handle GET request.
     */
    public function index(Request $request)
    {
    	/**
    	 * Automatically fill in the form with the query parameters if they are provided.
    	 */
        return view('verify')->with([
        	'email' => $request->query('email', ''),
        	'key' => $request->query('key', '')
        ]);
    }

    /**
     * Handle POST request.
     */
    public function verify(Request $request)
    {
    	$data = $request->json()->all();

    	$expected_parameters = ['email', 'key'];

        /**
         * Return error if request was not well-formed.
         */
        foreach ($expected_parameters as $p) {
        	if (!array_key_exists($p, $data)) {
        		return response()->json([
        			'status' => config('status.error'),
        			'error' => config('status.missing').$p
        		]);
        	}
        }

        /**
         * Return error if request contains invalid values.
         */
        foreach ($expected_parameters as $p) {
        	if (!is_string($data[$p]) || strlen($data[$p]) < 1) {
        		return response()->json([
        			'status' => config('status.error'),
        			'error' => config('status.invalid').$p
        		]);
        	}
        }

        /**
         * Get the user from the database.
         */
        $user = User::where('email', $data['email'])->first();

        /**
         * Return error if the email does not belong to any registered user.
         */
        if (!$user) {
        	return response()->json([
        		'status' => config('status.error'),
        		'error' => config('status.invalid').'email'
        	]);
        }

        /**
         * Return error if the email has already been verified.
         */
        if ($user->verified == true) {
        	return response()->json([
        		'status' => config('status.error'),
        		'error' => config('status.email_already_verified')
        	]);
        }

        /**
         * Return error if the verification key does not match.
         */
        if ($data['key'] !== $user->verification_key) {
        	return response()->json([
        		'status' => config('status.error'),
        		'error' => config('status.invalid').'key'
        	]);
        }

        $user->verified = true;
        $user->save();

        return response()->json(['status' => config('status.ok')]);
    }
}

?>