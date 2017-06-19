<?php namespace scfr\WSBB\server;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;


class EventServer implements MessageComponentInterface {
    
    /**
    * @var services\ClientManager
    */
    protected $client_manager;
    
    
    public function __construct() {
        $this->make_phpbb_env();
        $this->client_manager = services\ClientManager::get_service();
    }
    
    /**
    * Helper method to generate all of the PHP env and make accessible the globals
    * @return void
    */
    private function make_phpbb_env() {
        global $phpbb_root_path, $phpEx, $user, $auth, $cache, $db, $config, $template, $table_prefix, $phpbb_path_helper;
        global $phpbb_container, $request, $phpbb_log, $phpbb_dispatcher, $symfony_request, $phpbb_filesystem;
        
        if(defined('IN_PHPBB')) {
            return true;
        }
        define('IN_PHPBB', true);
        
        $phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : dirname(__DIR__) . "/../../../";
        $phpEx = substr(strrchr(__FILE__, '.'), 1);
        
        require_once($phpbb_root_path . 'common.' . $phpEx);
    }
    
    public function onOpen(ConnectionInterface $conn) {
        echo "[CLIENT] New connection! ({$conn->resourceId})\n";
        $this->client_manager->new_conn($conn);
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        print_r($from->WebSocket->request->getCookies());
        var_dump($msg);
    }
    
    public function onClose(ConnectionInterface $conn) {
        echo "[CLIENT] Connection {$conn->resourceId} has disconnected\n";

        $this->client_manager->remove_conn($conn);
        
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "[CLIENT] An error has occurred: {$e->getMessage()}\n";
        
        $conn->close();
    }

    public function onPhpbbPacket($data) {
        echo "[PHPBB] Got PHPBB Update";
        $packet = new models\WSInputPacket(json_decode($data, true));

        
    }
}