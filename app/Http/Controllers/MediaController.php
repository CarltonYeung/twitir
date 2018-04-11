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

        $keyspace = 'twitir';

        $session = $cluster->connect($keyspace);

        $session->execute(
            'INSERT INTO media (filename, contents, type, size) VALUES (?, ?, ?, ?)',
            [
                'arguments' => [
                    $_FILES['content']['name'],
                    new Cassandra\Blob(file_get_contents($_FILES['content']['tmp_name'])),
                    $_FILES['content']['type'],
                    $_FILES['content']['size'],
            ],
        ]);

        return response()->prettyjson([
            'status' => config('status.ok'),
            'id' => $_FILES['content']['name'],
        ]);
    }

    public function getmedia($id)
    {
        $cluster = Cassandra::cluster()->build();

        $keyspace = 'twitir';

        $session = $cluster->connect($keyspace);

        $rows = $session->execute(
            'SELECT contents, type FROM twitir.media WHERE filename = ?',
            [
                'arguments' => [
                    $_GET['filename'],
            ],
        ]);

        foreach ($rows as $row) {
            header('Content-Type: '.$row['type']);
            echo $row['contents']->toBinaryString();
        }
    }

}
