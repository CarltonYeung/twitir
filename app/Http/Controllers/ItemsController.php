<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MongoDB;
use Validator;

class ItemsController extends Controller
{
    private $client = null;

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
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => config('status.unauthorized'),
            ]);
        }

        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'content' => [
                'required',
                'string',
                'max:280',
            ],
            'childType' => [
                'string',
                'nullable',
                'in' => [
                    null,
                    'retweet',
                    'reply',
                ],
            ],
        ]);

        if ($validator->fails()) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => $validator->errors(),
            ]);
        }

        if (!$this->client) {
            $this->client = new MongoDB\Client('mongodb://'.config('database.mongodb.host').':'.config('database.mongodb.port'));
        }

        $collection = $this->client->twitir->items;

        $item = $collection->insertOne([
            'username' => Auth::user()->username,
            'property' => [
                'likes' => 0,
            ],
            'retweeted' => 0,
            'content' => $data['content'],
            'childType' => $data['childType'],
            // 'parent' => null,
            // 'media' => [],
            'timestamp' => time(),
        ]);

        return response()->prettyjson([
            'status' => config('status.ok'),
            'id' => ($item->getInsertedId())->__toString(),
        ]);
    }

    public function getitem(Request $request, $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => [
                'required',
                'regex:(^[0-9a-f]{24}$)',
            ],
        ]);

        if ($validator->fails()) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => $validator->errors(),
            ]);
        }

        if (!$this->client) {
            $this->client = new MongoDB\Client('mongodb://'.config('database.mongodb.host').':'.config('database.mongodb.port'));
        }

        $collection = $this->client->twitir->items;

        $item = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);

        if (!$item) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => 'There is no item with this id',
            ]);
        }

        $item = iterator_to_array($item);
        $item = ['id' => $item['_id']->__toString()] + $item;
        unset($item['_id']);

        return response()->prettyjson([
            'status' => config('status.ok'),
            'item' => $item,
        ]);
    }

    public function deleteitem(Request $request, $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => [
                'required',
                'regex:(^[0-9a-f]{24}$)',
            ],
        ]);

        if ($validator->fails()) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => $validator->errors(),
            ]);
        }

        if (!Auth::check()) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => config('status.unauthorized'),
            ]);
        }

        if (!$this->client) {
            $this->client = new MongoDB\Client('mongodb://'.config('database.mongodb.host').':'.config('database.mongodb.port'));
        }

        $collection = $this->client->twitir->items;

        $item = $collection->findOneAndDelete([
            '_id' => new MongoDB\BSON\ObjectId($id),
            'username' => Auth::user()->username,
        ]);

        if (!$item) {
            return response('', 400);
        }

        return response('', 200);
    }

}
