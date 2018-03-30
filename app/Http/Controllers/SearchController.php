<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MongoDB;
use Validator;

class SearchController extends Controller
{
    /**
     * Handle GET request.
     */
    public function index(Request $request)
    {
        return view('search');
    }

    /**
     * Handle POST request.
     */
    public function search(Request $request)
    {
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'timestamp' => 'filled|integer', // integer strings are allowed
            'limit' => 'filled|integer|min:1|max:100',
            'q' => 'filled|string',
            'username' => 'filled|string',
            'following' => 'filled|boolean',
        ]);

        if ($validator->fails()) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => $validator->errors(),
            ]);
        }

        $client = new MongoDB\Client('mongodb://'.config('database.mongodb.host').':'.config('database.mongodb.port'));
        $collection = $client->twitir->items;

        // integer type cast is needed because validator doesn't fail integer strings
        $limit = array_key_exists('limit', $data) ? intval($data['limit']) : 25;
        $timestamp = array_key_exists('timestamp', $data) ? intval($data['timestamp']) : time();
        $query['timestamp'] = ['$lte' => $timestamp];

        if (array_key_exists('q', $data)) {
            $regex = preg_replace('/\s+/', '|', $data['q']);
            $query['content'] = ['$regex' => $regex, '$options' => 'i'];
        }

        if (array_key_exists('username', $data)) {
            $query['username'] = $data['username'];
        }

        if (!array_key_exists('following', $data) || $data['following']) {
            if (!Auth::check()) {
                return response()->prettyjson([
                    'status' => config('status.error'),
                    'error' => config('status.unauthorized'),
                ]);
            }

            $follow = $client->twitir->follows;
            $user = $follow->findOne(['username' => Auth::user()->username]);
            $following = iterator_to_array($user->following); // Convert from BSON

            if (!count($following) || !$following) {
                return response()->prettyjson([
                    'status' => config('status.error'),
                    'error' => 'Not following anyone',
                ]);
            }

            if (array_key_exists('username', $query)) {
                if (!in_array($query['username'], $following, true)) {
                    return response()->prettyjson([
                        'status' => config('status.error'),
                        'error' => 'Not a follower of the given username. Please follow the user or set "following" to false.',
                    ]);
                }
            } else {
                $query['username'] = ['$regex' => implode('|', $following)];
            }
        }

        $cursor = $collection->find($query, [
            'limit' => $limit,
            'sort' => [
                'timestamp' => -1 // get the most recent tweets
            ],
        ]);

        $items = $cursor->toArray();

        for ($i = 0; $i < count($items); $i++) {
            $item = iterator_to_array($items[$i]);
            $item = ['id' => $item['_id']->__toString()] + $item;
            unset($item['_id']);
            $items[$i] = $item;
        }

        return response()->prettyjson([
            'status' => config('status.ok'),
            'items' => $items,
        ]);
    }

}