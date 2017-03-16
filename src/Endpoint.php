<?php

namespace pxgamer\TorrentApi;

/**
 * Class Endpoint
 * @package pxgamer\TorrentApi
 */
class Endpoint
{
    /**
     * @var Torrent
     */
    public $Torrent;

    /**
     * @var string
     */
    private $DB_HOST = 'localhost';
    /**
     * @var string
     */
    private $DB_USER = 'root';
    /**
     * @var string
     */
    private $DB_PASS = 'root';
    /**
     * @var string
     */
    private $DB_NAME = 'torrent_api';

    /**
     * Endpoint constructor.
     */
    public function __construct()
    {
        $this->Torrent = new Torrent();
    }

    /**
     * @return array
     */
    public function upload()
    {
        if (isset($_FILES['torrent_file']['tmp_name'])) {
            $torrent_info = $this->Torrent->parse(file_get_contents($_FILES['torrent_file']['tmp_name']));
            if (isset($torrent_info['info_hash'])) {
                if (!file_exists(FILE_DIR . DIRECTORY_SEPARATOR . strtoupper($torrent_info['info_hash']) . '.torrent')) {
                    if (move_uploaded_file($_FILES['torrent_file']['tmp_name'],
                        FILE_DIR . DIRECTORY_SEPARATOR . strtoupper($torrent_info['info_hash']) . '.torrent')) {
                        return ['status' => 'file successfully added', 'return_code' => 201];
                    } else {
                        return [];
                    }
                } else {
                    return ['status' => 'torrent already exists, no action taken', 'return_code' => 200];
                }
            } else {
                return ['status' => 'invalid torrent file', 'return_code' => 400];
            }
        }

        return ['status' => 'no torrent file provided', 'return_code' => 400];
    }

    /**
     * @param $torrent_id
     * @return array
     */
    public function info($torrent_id)
    {
        if (file_exists(FILE_DIR . DIRECTORY_SEPARATOR . strtoupper($torrent_id) . '.torrent')) {
            $torrent_info = $this->Torrent->parse(file_get_contents(FILE_DIR . DIRECTORY_SEPARATOR . strtoupper($torrent_id) . '.torrent'));

            $info_array = [
                'info_hash' => $torrent_info['info_hash'],
                'title' => (isset($torrent_info['info']['name'])) ? $torrent_info['info']['name'] : '',
                'files' => (isset($torrent_info['info']['files'])) ? $torrent_info['info']['files'] : [],
                'status' => 'success',
                'return_code' => 200,
            ];

            return $info_array;
        } else {
            return [
                "status" => "torrent with hash '" . strtoupper($torrent_id) . "' not found",
                "return_code" => 404
            ];
        }
    }

    /**
     * @param null $key
     * @return bool
     */
    public function authenticate($key = null)
    {
        if ($key == null) {
            return false;
        }

        $conn = new \mysqli($this->DB_HOST, $this->DB_USER, $this->DB_PASS, $this->DB_NAME);
        $stmt = $conn->prepare("SELECT * FROM users WHERE api_key = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $stmt->bind_result($id, $username, $api_key, $is_enabled);
        $stmt->fetch();

        return ($api_key == $key && $is_enabled == 1) ? true : false;
    }

    /**
     * @param $torrent_id
     * @return array
     */
    public function download($torrent_id)
    {
        if (file_exists(FILE_DIR . DIRECTORY_SEPARATOR . strtoupper($torrent_id) . '.torrent')) {
            http_response_code(200);
            header('Content-Type: application/x-bittorrent, application/octet-stream');
            header('Content-Disposition: attachment; filename="' . strtoupper($torrent_id) . '.torrent' . '"');
            echo file_get_contents(FILE_DIR . DIRECTORY_SEPARATOR . strtoupper($torrent_id) . '.torrent');
            die();
        } else {
            return [
                'status' => 'torrent with hash \'' . strtoupper($torrent_id) . '\' not found',
                'return_code' => 404
            ];
        }
    }
}
