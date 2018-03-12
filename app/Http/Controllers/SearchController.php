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
                'integer',
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

        $query = [];

        if (array_key_exists('timestamp', $data)) {
            $query['timestamp'] = ['$lte' => $data['timestamp']];
        } else {
            $query['timestamp'] = ['$lte' => time()];
        }

        $limit = array_key_exists('limit', $data) ? $data['limit'] : 25;

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
