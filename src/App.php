<?php

namespace pxgamer\TorrentApi;

/**
 * Class App
 * @package pxgamer\TorrentApi
 */
class App
{
    /**
     * @var string
     */
    const APP_NAME = 'TorrentAPI';
    /**
     * @var bool
     */
    const MUST_VALIDATE = false;

    /**
     * App constructor.
     */
    public function __construct()
    {
        define('ROOT_DIR', realpath('../'));
        define('PUBLIC_DIR', realpath(ROOT_DIR . '/public'));
        define('SRC_DIR', realpath(ROOT_DIR . '/src'));
        define('FILE_DIR', realpath(ROOT_DIR . '/files'));
    }

    /**
     * @param $array
     * @return string
     */
    public function output($array)
    {
        http_response_code($array['return_code']);
        header("Content-Type: application/json, text/json, text/plain");

        return json_encode($array);
    }
}