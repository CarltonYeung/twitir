<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Cassandra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MongoDB;
use Validator;

class MediaController extends Controller
{
    private static $client = null;

    public function __construct()
    {
        self::$client = new MongoDB\Client('mongodb://'.config('database.mongodb.host').':'.config('database.mongodb.port'));
    }

    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        return view('addmedia');
    }

    public function addmedia(Request $request)
    {
        if (!Auth::check()) {
            return response()->prettyjson([
                'status' => config('status.error'),
                'error' => config('status.unauthorized'),
            ]);
        }

        $cluster = Cassandra::cluster()->build();
        $keyspace = config('cassandra.keyspace');
        $session = $cluster->connect($keyspace);
        $uuid = new Cassandra\Uuid();

        $session->execute(
            'INSERT INTO ' . config('cassandra.media') . ' (id, filename, contents, type, size) VALUES (?, ?, ?, ?, ?)', [
                'arguments' => [
                    $uuid,
                    $_FILES['content']['name'],
                    new Cassandra\Blob(file_get_contents($_FILES['content']['tmp_name'])),
                    $_FILES['content']['type'],
                    $_FILES['content']['size'],
                ],
            ]
        );

        $session->execute(
            'UPDATE ' . config('cassandra.refcounts') . ' SET refcount = refcount + 0 WHERE id = ?', [
                'arguments' => [
                    $uuid,
                ],
            ]
        );

        return response()->prettyjson([
            'status' => config('status.ok'),
            'id' => $uuid->uuid(),
        ]);
    }

    public function getmedia($id)
    {
        $cluster = Cassandra::cluster()->build();
        $keyspace = config('cassandra.keyspace');
        $session = $cluster->connect($keyspace);

        $rows = $session->execute(
            'SELECT contents, type FROM ' . config('cassandra.keyspace') . '.' . config('cassandra.table') . ' WHERE id = ?', [
                'arguments' => [
                    new Cassandra\Uuid($id),
                ],
            ]
        );

        foreach ($rows as $row) {
            header('Content-Type: '.$row['type']);
            echo $row['contents']->toBinaryString();
        }
    }

}
