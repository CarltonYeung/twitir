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
    private static $cluster = null;

    public function __construct()
    {
        self::$client = new MongoDB\Client('mongodb://'.config('database.mongodb.host').':'.config('database.mongodb.port'));
        self::$cluster = Cassandra::cluster()
                         ->withContactPoints(config('cassandra.host'))
                         ->withPort(config('cassandra.port'))
                         ->build();
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
                'distinct',
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

        $parent_result = null;
        if ($parent_and_child) {
            if ($data['childType'] == 'retweet') {
                $update = [
                    '$inc' => ['retweeted' => 1, 'interest' => 1],
                ];
                $parent_result = $collection->findOneAndUpdate(['_id' => new MongoDB\BSON\ObjectId($data['parent'])], $update);
                if(!$parent_result) {
                    return response()->prettyjson([
                        'status' => config('status.error'),
                        'error' => 'Parent doesn\'t exist',
                    ]);
                }
            }
        }

        if (array_key_exists('media', $data)) {
            $keyspace = config('cassandra.keyspace');
            $session = self::$cluster->connect($keyspace);

            foreach ($data['media'] as $id) {
                $rows = $session->execute(
                    'SELECT COUNT(*) FROM ' . config('cassandra.refcounts') . ' WHERE id = ?', [
                        'arguments' => [
                            new Cassandra\Uuid($id)
                        ]
                    ]
                );

                if (!$rows[0]['count']->value()) {
                    return response()->prettyjson([
                        'status' => config('status.error'),
                        'error' => 'Media doesn\'t exist: ' . $id,
                    ]);
                }
            }

            foreach ($data['media'] as $id) {
                $rows = $session->execute(
                    'UPDATE ' . config('cassandra.refcounts') . ' SET refcount = refcount + 1 WHERE id = ?', [
                        'arguments' => [
                            new Cassandra\Uuid($id)
                        ]
                    ]
                );
            }
        }

        $item = $collection->insertOne([
            'username' => Auth::user()->username,
            'property' => [
                'likes' => 0,
                'likedBy' => [],
            ],
            'retweeted' => 0,
            'content' => $parent_result ? $parent_result['content'] : $data['content'],
            'childType' => $parent_and_child ? $data['childType'] : null,
            'parent' => $parent_and_child ? $data['parent'] : null,
            'media' => array_key_exists('media', $data) ? $data['media'] : [],
            'timestamp' => time(),
            'interest' => 0
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

        if ($item['childType'] === 'retweet') {
            $update = [
                '$inc' => ['retweeted' => -1, 'interest' => -1],
            ];
            $collection->updateOne(['_id' => new MongoDB\BSON\ObjectId($item['parent'])], $update);
        }

        $keyspace = config('cassandra.keyspace');
        $session = self::$cluster->connect($keyspace);

        $item = iterator_to_array($item);
        $media = $item['media'];
        foreach ($media as $id) {
            $rows = $session->execute(
                'SELECT refcount FROM ' . config('cassandra.refcounts') . ' WHERE id = ?', [
                    'arguments' => [
                        new Cassandra\Uuid($id)
                    ]
                ]
            );

            if ($rows[0]['refcount']->value() == 1) {
                $session->execute(
                    'DELETE FROM ' . config('cassandra.refcounts') . ' WHERE id = ?', [
                        'arguments' => [
                            new Cassandra\Uuid($id)
                        ]
                    ]
                );

                $session->execute(
                    'DELETE FROM ' . config('cassandra.media') . ' WHERE id = ?', [
                        'arguments' => [
                            new Cassandra\Uuid($id)
                        ]
                    ]
                );
            } else {
                $session->execute(
                    'UPDATE ' . config('cassandra.refcounts') . ' SET refcount = refcount - 1 WHERE id = ?', [
                        'arguments' => [
                            new Cassandra\Uuid($id)
                        ]
                    ]
                );
            }
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
                '$addToSet' => ['property.likedBy' => Auth::user()->username],
                '$inc' => ['property.likes' => 1, 'interest' => 1]
            ];
        } else {
            if (!in_array(Auth::user()->username, iterator_to_array($item['property']['likedBy']))) {
                return response()->prettyjson([
                    'status' => config('status.error'),
                    'error' => "You haven't liked this item.",
                ]);
            }

            $update = [
                '$pull' => ['property.likedBy' => Auth::user()->username],
                '$inc' => ['property.likes' => -1, 'interest' => -1]
            ];
        }

        $collection->updateOne(['_id' => new MongoDB\BSON\ObjectId($id)], $update);

        return response()->prettyjson(['status' => config('status.ok')]);
    }

}
