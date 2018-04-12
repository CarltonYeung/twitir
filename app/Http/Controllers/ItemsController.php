<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Cassandra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MongoDB;
use Validator;

class ItemsController extends Controller
{
    private static $client = null;

    public function __construct()
    {
        self::$client = new MongoDB\Client('mongodb://'.config('database.mongodb.host').':'.config('database.mongodb.port'));
    }

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
            'parent' => [
                'required_with:childType', // if childType is present and not empty (i.e., not null)
                'regex:(^[0-9a-f]{24}$)',
            ],
            'media' => [
                'filled',
            ],
            'media.*' => [
                'regex:(^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$)',
            ],
        ]);

        if ($validator->fails()) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => $validator->errors(),
            ]);
        }

        $collection = self::$client->twitir->items;

        $parent_and_child = array_key_exists('parent', $data)
                         && array_key_exists('childType', $data)
                         && $data['parent']
                         && $data['childType'];

        if ($parent_and_child) {
            $inc = $data['childType'] === 'retweet' ? 1 : 0;
            $update = ['$inc' => ['retweeted' => $inc]];

            $parent_result = $collection->updateOne(['_id' => new MongoDB\BSON\ObjectId($data['parent'])], $update);

            if(!$parent_result->getMatchedCount()) {
                return response()->prettyjson([
                    'status' => config('status.error'),
                    'error' => 'Parent doesn\'t exist',
                ]);
            }
        }

        if (array_key_exists('media', $data)) {
            $cluster = Cassandra::cluster()->build();
            $keyspace = config('cassandra.keyspace');
            $session = $cluster->connect($keyspace);

            foreach ($data['media'] as $id) {
                $media_exists = $session->execute(
                    'SELECT COUNT(*) FROM ' . config('cassandra.table') . ' WHERE primary_keys = ?', [
                        'arguments' => [
                            new Cassandra\Uuid($id)
                        ]
                    ]
                );

                if (!$media_exists) {
                    return response()->prettyjson([
                        'status' => config('status.error'),
                        'error' => 'Media doesn\'t exist: ' . $id,
                    ]);
                }
            }
        }

        $item = $collection->insertOne([
            'username' => Auth::user()->username,
            'property' => [
                'likes' => 0,
                'likedBy' => [],
            ],
            'retweeted' => 0,
            'content' => $data['content'],
            'childType' => $parent_and_child ? $data['childType'] : null,
            'parent' => $parent_and_child ? $data['parent'] : null,
            'media' => array_key_exists('media', $data) ? $data['media'] : [],
            'timestamp' => time(),
        ]);

        return response()->prettyjson([
            'status' => config('status.ok'),
            'id' => ($item->getInsertedId())->__toString(),
        ]);
    }

    public function getitem($id)
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

        $collection = self::$client->twitir->items;

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

    public function deleteitem($id)
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

        $collection = self::$client->twitir->items;

        $item = $collection->findOneAndDelete([
            '_id' => new MongoDB\BSON\ObjectId($id),
            'username' => Auth::user()->username,
        ]);

        if (!$item) {
            return response('', 400);
        }

        return response('', 200);
    }

    public function likeitem(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => config('status.unauthorized'),
            ]);
        }

        $data = $request->json()->all();

        $validator = Validator::make(['id' => $id, $data], [
            'id' => [
                'required',
                'regex:(^[0-9a-f]{24}$)',
            ],
            'like' => [
                'boolean',
            ],
        ]);

        if ($validator->fails()) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => $validator->errors(),
            ]);
        }

        $collection = self::$client->twitir->items;

        $item = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);

        if (!$item) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => "Item doesn't exist",
            ]);
        }

        $update;
        if (!array_key_exists('like', $data) || $data['like']) {
            if (in_array(Auth::user()->username, iterator_to_array($item['property']['likedBy']))) {
                return response()->prettyjson([
                    'status' => config('status.error'),
                    'error' => "You already liked this item.",
                ]);
            }

            $update = [
                '$inc' => ['property.likes' => 1],
                '$addToSet' => ['property.likedBy' => Auth::user()->username],
            ];
        } else {
            if (!in_array(Auth::user()->username, iterator_to_array($item['property']['likedBy']))) {
                return response()->prettyjson([
                    'status' => config('status.error'),
                    'error' => "You haven't liked this item.",
                ]);
            }

            $update = [
                '$inc' => ['property.likes' => -1],
                '$pull' => ['property.likedBy' => Auth::user()->username],
            ];
        }

        $collection->updateOne(['_id' => new MongoDB\BSON\ObjectId($id)], $update);

        return response()->prettyjson(['status' => config('status.ok')]);
    }

}
