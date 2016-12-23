<?php

// API endpoint for torrent files
require '../torrentAPI.php';

use \pxgamer\torrentAPI\Endpoint;

$endPoint = new Endpoint;

echo $endPoint::json(200, ['authenticated' => $endPoint::checkAPI($_POST['api_key']), 'return_code' => 200]);
