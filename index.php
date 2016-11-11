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

        public static function checkMode($method = null, $mode = null)
        {
            $modes = [
                'GET' => [
                    'file' => true,
                    'f' => true,
                    'download' => true,
                    'info' => true,
                    'i' => true,
                    'information' => true,
                ],
            ];

            return ($method !== '' && $mode !== '' && isset($modes[$method][$mode])) ? true : false;
        }

        public static function get($mode = 'file', $id = null)
        {
            self::$torrent_id = $id;
            if (self::$torrent_id === null || self::$torrent_id === '') {
                return self::json(400, ['status' => 'invalid id', 'return_code' => 400]);
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
                return self::json(404, ['status' => 'torrent with hash \''.self::$torrent_id.'\' not found', 'return_code' => 404]);
            }

            return true;
        }

        private static function returnInformation()
        {
            if (file_exists(self::$torrent_dir.self::$torrent_id.'.torrent')) {
                $torrent_info = self::parse_torrent(file_get_contents(self::$torrent_dir.self::$torrent_id.'.torrent'));
                $info_array = [
                    'info_hash' => $torrent_info['info_hash'],
                    'title' => (isset($torrent_info['info']['name'])) ? $torrent_info['info']['name'] : null,
                ];

                return self::json(200, $info_array);
            } else {
                return self::json(404, ['status' => 'torrent with hash \''.self::$torrent_id.'\' not found', 'return_code' => 404]);
            }

            return true;
        }

        public static function json($status = 418, $array = ['status' => 'i\'m a teapot', 'return_code' => 418])
        {
            http_response_code($status);
            header('Content-Type: application/json, text/json, text/plain');

            return json_encode($array);
        }

        private static function parse_torrent($s)
        {
            static $str;
            $str = $s;
            if ($str{0} == 'd') {
                $str = substr($str, 1);
                $ret = array();
                while (strlen($str) && $str{0} != 'e') {
                    $key = self::parse_torrent($str);
                    if (strlen($str) == strlen($s)) {
                        break;
                    }
          // prevent endless cycle if no changes made
          if (!strcmp($key, 'info')) {
              $save = $str;
          }
                    $value = self::parse_torrent($str);
                    if (!strcmp($key, 'info')) {
                        $tosha = substr($save, 0, strlen($save) - strlen($str));
                        $ret['info_hash'] = sha1($tosha);
                    }
          // process hashes - make this stuff an array by piece
          if (!strcmp($key, 'pieces')) {
              $value = explode('====',
                         substr(
                           chunk_split($value, 20, '===='),
                           0, -4
                         )
                       );
          };
                    $ret[$key] = $value;
                }
                $str = substr($str, 1);

                return $ret;
            } elseif ($str{0} == 'i') {
                $ret = substr($str, 1, strpos($str, 'e') - 1);
                $str = substr($str, strpos($str, 'e') + 1);

                return $ret;
            } elseif ($str{0} == 'l') {
                $ret = array();
                $str = substr($str, 1);
                while (strlen($str) && $str{0} != 'e') {
                    $value = self::parse_torrent($str);
                    if (strlen($str) == strlen($s)) {
                        break;
                    }
          // prevent endless cycle if no changes made
          $ret[] = $value;
                }
                $str = substr($str, 1);

                return $ret;
            } elseif (is_numeric($str{0})) {
                $namelen = substr($str, 0, strpos($str, ':'));
                $name = substr($str, strpos($str, ':') + 1, $namelen);
                $str = substr($str, strpos($str, ':') + 1 + $namelen);

                return $name;
            }
        }
    }

    $tAPI = new \pxgamer\torrentAPI();
    if (isset($_REQUEST['api_key']) && $tAPI::checkAPI($_REQUEST['api_key'])) {
        if (isset($_REQUEST['mode']) && $_REQUEST['mode'] !== '' && $tAPI::checkMode($_SERVER['REQUEST_METHOD'], $_REQUEST['mode'])) {
            switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
              if (isset($_REQUEST['id']) && $_REQUEST['id'] !== '') {
                  echo $tAPI::get($_REQUEST['mode'], $_REQUEST['id']);
              } else {
                  echo $tAPI::json(400, ['status' => 'invalid id', 'return_code' => 400]);
              }
              break;
            default:
              echo $tAPI::json(405, ['status' => 'request method not implemented', 'return_code' => 405]);
          }
        } else {
            echo $tAPI::json(405, ['status' => 'invalid or null mode provided', 'return_code' => 405]);
        }
    } else {
        echo $tAPI::json(401, ['status' => 'invalid or null api_key parameter provided', 'return_code' => 401]);
    }
}
