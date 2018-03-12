<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use MongoDB;

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
            'timestamp' => [
                'filled',
                'integer', // integer strings don't fail
            ],
            'limit' => [
                'filled',
                'integer',
                'min:1',
                'max:100',
            ],
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
        $timestamp = array_key_exists('timestamp', $data) ? intval($data['timestamp']) : time();

        $query['timestamp'] = ['$lte' => $timestamp];

        $limit = array_key_exists('limit', $data) ? intval($data['limit']) : 25;

        $cursor = $collection->find($query, [
            'limit' => $limit,
            'sort' => [
                'timestamp' => -1 // get the most recent tweets
            ],
        ]);

        $items = $cursor->toArray();

        if (!count($items) || !$items) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => 'No matches found',
            ]);
        }

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

?>
