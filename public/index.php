<?php

require '../vendor/autoload.php';

use pxgamer\TorrentApi\App;
use pxgamer\TorrentApi\Endpoint;

$App = new App;
$Endpoint = new Endpoint;

if (!isset($_REQUEST['api_key'])) {
    $_REQUEST['api_key'] = '';
}
if (!isset($_REQUEST['mode'])) {
    $_REQUEST['mode'] = '';
}
if (!isset($_REQUEST['id'])) {
    $_REQUEST['id'] = '';
}

// Authenticate the user
if (!$App::MUST_VALIDATE || $Endpoint->authenticate($_REQUEST['api_key'])) {
    switch ($_REQUEST['mode']) {
        case 'i':
        case 'info':
            echo $App->output($Endpoint->info($_REQUEST['id']));
            break;
        case 'u':
        case 'upload':
            echo $App->output($Endpoint->upload());
            break;
        case 'd':
        case 'download':
        default:
            echo $App->output($Endpoint->download($_REQUEST['id']));
    }
} else {
    echo $App->output(['authenticated' => false, 'return_code' => 403]);
}