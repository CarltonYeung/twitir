<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class hw4Controller extends Controller
{
    public function deposit()
    {
        $cluster = Cassandra::cluster()->build();

        $keyspace = 'hw4';

        $session = $cluster->connect($keyspace);

        $session->execute(
            'INSERT INTO imgs (filename, contents, type, size) VALUES (?, ?, ?, ?)',
            [
                'arguments' => [
                    $_FILES['contents']['name'],
                    new Cassandra\Blob(file_get_contents($_FILES['contents']['tmp_name'])),
                    $_FILES['contents']['type'],
                    $_FILES['contents']['size'],
            ],
        ]);
    }

    public function retrieve()
    {
        $cluster = Cassandra::cluster()->build();

        $keyspace = 'hw4';

        $session = $cluster->connect($keyspace);

        $rows = $session->execute(
            'SELECT contents, type FROM hw4.imgs WHERE filename = ?',
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
