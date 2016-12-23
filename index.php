<?php

// API endpoint for torrent files
require 'torrentAPI.php';

use pxgamer\torrentAPI\Endpoint;

$endPoint = new Endpoint;

if (isset($_REQUEST['api_key']) && $endPoint->checkAPI($_REQUEST['api_key'])) {
    if (isset($_REQUEST['mode']) && $_REQUEST['mode'] !== '' && $endPoint->checkMode($_SERVER['REQUEST_METHOD'],
            $_REQUEST['mode'])
    ) {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if (isset($_REQUEST['id']) && $_REQUEST['id'] !== '') {
                    echo $endPoint->get($_REQUEST['mode'], $_REQUEST['id']);
                } else {
                    echo $endPoint->json(400, ['status' => 'invalid id', 'return_code' => 400]);
                }
                break;
            case 'POST':
                echo $endPoint->post($_REQUEST['mode']);
                break;
            default:
                echo $endPoint->json(405, ['status' => 'request method not implemented', 'return_code' => 405]);
        }
    } else {
        echo $endPoint->json(405, ['status' => 'invalid or null mode parameter provided', 'return_code' => 405]);
    }
} else {
    echo $endPoint->json(401, ['status' => 'invalid or null api_key parameter provided', 'return_code' => 401]);
}
