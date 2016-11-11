<?php

// API endpoint for torrent files

namespace pxgamer {

    class torrentAPI
    {
        private static $torrent_dir = 'files/';
        private static $torrent_id = null;

        public function __construct()
        {
        }

        public static function checkAPI($key = null)
        {
            $valid_api_keys = [
              'open' => true, // Defaulted to open for anyone
            ];

            return ($key !== '' && isset($valid_api_keys[$key])) ? true : false;
        }

        public static function get($mode = 'file', $id = null)
        {
            self::$torrent_id = $id;
            if (self::$torrent_id === null || self::$torrent_id === '') {
                return self::json(400, ['status' => 'invalid id']);
            }
            switch ($mode) {
                case 'file':
                case 'f':
                case 'download':
                    return self::returnFile();
                    break;
                case 'info':
                case 'i':
                case 'information':
                    return self::returnInformation();
                    break;
                default:
                    return self::json();
            }
        }

        private static function returnFile()
        {
            if (file_exists(self::$torrent_dir.self::$torrent_id.'.torrent')) {
                http_response_code(200);
                header('Content-Type: application/x-bittorrent, application/octet-stream');
                header('Content-Disposition: attachment; filename="'.self::$torrent_id.'.torrent'.'"');
                echo file_get_contents(self::$torrent_dir.self::$torrent_id.'.torrent');
            } else {
                return self::json(404, ['status' => 'torrent with hash \''.self::$torrent_id.'\' not found']);
            }

            return true;
        }

        public static function json($status = 418, $array = ['status' => 'i\'m a teapot'])
        {
            http_response_code($status);
            header('Content-Type: application/json, text/json, text/plain');

            return json_encode($array);
        }
    }

    $tAPI = new \pxgamer\torrentAPI();
    if (isset($_REQUEST['api_key']) && $tAPI::checkAPI($_REQUEST['api_key'])) {
        if (isset($_REQUEST['mode'])) {
            switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
              if (isset($_REQUEST['id'])) {
                  echo $tAPI::get($_REQUEST['mode'], $_REQUEST['id']);
              } else {
                  echo $tAPI::json(400, ['status' => 'invalid id']);
              }
              break;
            default:
              echo $tAPI::json(405, ['status' => 'request method not implemented']);
          }
        } else {
            echo $tAPI::json(405, ['status' => 'invalid or null mode provided']);
        }
    } else {
        echo $tAPI::json(401, ['status' => 'invalid or null api_key parameter provided']);
    }
}
