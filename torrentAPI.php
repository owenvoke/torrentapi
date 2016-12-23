<?php

namespace pxgamer\torrentAPI;

class Endpoint
{
    const TORRENT_DIR = 'files/';

    private static $torrent_id = null;

    private static function uploadFile()
    {
        if (isset($_FILES['torrent_file']['tmp_name'])) {
            $torrent_info = self::parse_torrent(file_get_contents($file));
            if (!file_exists(self::TORRENT_DIR . strtoupper($torrent_info['info_hash']) . '.torrent')) {
                if (move_uploaded_file($_FILES['torrent_file']['tmp_name'],
                    self::TORRENT_DIR . strtoupper($torrent_info['info_hash']) . '.torrent')) {
                    return self::json(201, ['status' => 'file successfully added', 'return_code' => 201]);
                } else {
                    return self::json();
                }
            } else {
                return self::json(200, ['status' => 'torrent already exists, no action taken', 'return_code' => 200]);
            }
        }

        return self::json(400, ['status' => 'no torrent file provided', 'return_code' => 400]);
    }

    private static function returnInformation()
    {
        if (file_exists(self::TORRENT_DIR . strtoupper(self::$torrent_id) . '.torrent')) {
            $torrent_info = self::parse_torrent(file_get_contents(self::TORRENT_DIR . strtoupper(self::$torrent_id) . '.torrent'));
            $info_array = [
                'info_hash' => $torrent_info['info_hash'],
                'title' => (isset($torrent_info['info']['name'])) ? $torrent_info['info']['name'] : null,
                'status' => 'success',
                'return_code' => 200,
            ];

            return self::json(200, $info_array);
        } else {
            return self::json(404, [
                'status' => 'torrent with hash \'' . strtoupper(self::$torrent_id) . '\' not found',
                'return_code' => 404
            ]);
        }
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

        return '';
    }

    public function checkAPI($key = null)
    {
        if ($key == null) {
            return false;
        }

        $conn = mysqli_connect('localhost', 'torrent_api', 'cgT3JsMXg2hiE33w', 'torrent_api');
        $stmt = $conn->prepare("SELECT * FROM users WHERE api_key = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $stmt->bind_result($id, $username, $api_key, $is_enabled);
        $stmt->fetch();

        return ($api_key == $key && $is_enabled == 1) ? true : false;
    }

    public function checkMode($method = null, $mode = null)
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
            'POST' => [
                'uload' => true,
                'u' => true,
                'upload' => true,
            ],
        ];

        return ($method !== '' && $mode !== '' && isset($modes[$method][$mode])) ? true : false;
    }

    public function get($mode = 'file', $id = null)
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

    public function post($mode = 'upload', $additional = [])
    {
        switch ($mode) {
            case 'uload':
            case 'u':
            case 'upload':
                return self::uploadFile();
                break;
            default:
                return self::json();
        }
    }

    public function json($status = 418, $array = ['status' => 'i\'m a teapot', 'return_code' => 418])
    {
        http_response_code($status);
        header('Content-Type: application/json, text/json, text/plain');

        return json_encode($array);
    }

    private function returnFile()
    {
        if (file_exists(self::TORRENT_DIR . strtoupper(self::$torrent_id) . '.torrent')) {
            http_response_code(200);
            header('Content-Type: application/x-bittorrent, application/octet-stream');
            header('Content-Disposition: attachment; filename="' . strtoupper(self::$torrent_id) . '.torrent' . '"');
            echo file_get_contents(self::TORRENT_DIR . strtoupper(self::$torrent_id) . '.torrent');
        } else {
            return self::json(404, [
                'status' => 'torrent with hash \'' . strtoupper(self::$torrent_id) . '\' not found',
                'return_code' => 404
            ]);
        }

        return true;
    }
}
