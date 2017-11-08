<?php namespace scfr\WSBB\server\services;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use scfr\WSBB\server as server;


class ClientManager {
    
    /**
    * Undocumented variable
    *
    * @var Ds\Map
    */
    protected $users;
    protected $conn;
    /**
    * Instance of the singleton service
    * @var ClientManager
    */
    private static $_instance;
    
    private function __construct() {
        $this->users = new \Ds\Map();
        $this->conn = new \SplObjectStorage();
        $this->users->put(1, new server\client\WSClient(1));
    }
    
    
    public function get_service() {
        if(!self::$_instance) self::$_instance = new ClientManager();
        return self::$_instance;
    }
    
    public function get_users() {
        return $this->users;
    }
    
    public function get_conns() {
        return $this->conn;
    }
    
    
    /**
    * Will add a new connection to our cache
    * And assign it to User 1 (not logged in) by default
    *
    * @param ConnectionInterface $conn
    * @return void
    */
    public function new_conn(ConnectionInterface $conn) {
        global $config;
        
        // acknowledge connection
        $conn->send(1);
        
        // Attach to un-authorized temporary
        $u = $this->users->get(1);
        $this->conn->attach($conn, 1);
        $u->attach_conn($conn);
        
        $cookiesstr = $conn->httpRequest->getHeader('cookie');
        preg_match_all("/([^;= ]*)=([^;= ]*)/", $cookiesstr[0], $capturCookie, PREG_SET_ORDER);
        $user_id = $session_id = 1;
        
        $cookies = [];
        if($capturCookie) {
            foreach($capturCookie as $c) {
                $cookies[$c[1]] = $c[2];
            }
        }

        // Check cookie
        if(!empty($cookies[$config['cookie_name'].'_u'] && !empty($cookies[$config['cookie_name'].'_sid']))) {
            
            $user_id = $cookies[ $config['cookie_name'] . '_u' ];
            $session_id = $cookies[ $config['cookie_name'] . '_sid' ];
            
        }
        
        $identify = new server\models\IdentifyPacket([
        "user_id" => $user_id,
        "session_id" => $session_id,
        ]);
        
        $this->auth_conn($conn, $identify);
    }
    
    /**
    * Will remove a connection from the server,
    * Deleting it from our conn collection, deleting it from its corresponding user conn collection
    * and deleting the user if no longer needed
    *
    * @param ConnectionInterface $conn the connection to remove
    * @return void
    */
    public function remove_conn(ConnectionInterface $conn) {
        $user_id = $this->conn[$conn];
        $client = $this->users->get($user_id);
        
        $this->detach_conn_from_client($client, $conn);
        // No longer using this conn
        $this->conn->detach($conn);
        
    }
    
    /**
    * Will authorize this connection and assign it to the specified user client
    * Will check if the supplied session match for the user if identify points to
    * a logged-in user.
    *
    * @param ConnectionInterface $conn
    * @param server\models\IdentifyPacket $identify
    * @return void
    */
    public function auth_conn(ConnectionInterface $conn, server\models\IdentifyPacket $identify) {
        // We want to un-log this conn, no probs.
        if($identify->user_id === 1) {
            $this->move_conn($conn, 1);
            
            return true;
        }
        // We want to log in to a specified user and we're authorized to do so, do it.
        if($this->check_identity($identify)) {
            $this->move_conn($conn, $identify->user_id);
            return true;
        }
        
        // We did not perform anything.
        return false;
    }
    
    /**
    * Detach a connection from a client
    * Will perform garbage collect if the client is no longer needed
    *
    * @param client\WSClient $client
    * @param ConnectionInterface $conn
    */
    private function detach_conn_from_client(server\client\WSClient $client, ConnectionInterface $conn) {
        // Detach this conn and check if we still need this user
        echo "[CLIENT] Detached conn ({$conn->resourceId}) from user ({$client->get_user_id()}) \n";
        if(!$client->detach_conn($conn)) {
            // Garbage collect
            if($client->get_user_id() > 1)  {
                $this->users->remove($client->get_user_id());
                echo "[CLIENT] Removed user({$client->get_user_id()}) \n";
            }
        }
    }
    
    /**
    * Attaches a connection to a client
    *
    * @param client\WSClient $client
    * @param ConnectionInterface $conn
    */
    private function attach_conn_to_client(server\client\WSClient $client, ConnectionInterface $conn) {
        $this->users->put($client->get_user_id(), $client);
        $this->conn->attach($conn, $client->get_user_id());
        
        $client->attach_conn($conn);
        echo "[CLIENT] Attached conn ({$conn->resourceId}) to ({$client->get_user_id()}) \n";
    }
    
    /**
    * Check wether a given identify packet is a valid & authed one
    *
    * @param server\models\IdentifyPacket $identify the packet to check
    * @return boolean
    */
    private function check_identity(server\models\IdentifyPacket $identify) {
        global $db;
        
        $sql = 'SELECT u.*, s.*
        FROM ' . SESSIONS_TABLE . ' s, ' . USERS_TABLE . " u
        WHERE s.session_id = '" . $db->sql_escape($identify->session_id) . "'
        AND u.user_id = s.session_user_id";
        
        $result = $db->sql_query($sql);
        $data = $db->sql_fetchrow($result);
        
        // Did the session exist in the db ?
        if(isset($data)) {
            /** @todo more checks ? */
            return $data['session_user_id'] == $identify->user_id;
        }
        
        return false;
    }
    
    /**
    * Helper method to move a connection over to a new user id
    * If the new user id has no client, this will create one for it.
    *
    * @param ConnectionInterface $conn
    * @param integer $user_id
    * @return void
    */
    private function move_conn(ConnectionInterface $conn, $new_user_id) {
        
        $old_user_id = $this->conn[$conn];
        
        // Check conn exists
        if(empty($old_user_id)) throw new \Exception("Tried moving a conn that wasn't previously assigned to a client");
        if($old_user_id == $new_user_id) return true;
        
        // Check conn was assigned to a client
        $old_client = $this->users->get($old_user_id);
        if(empty($old_client)) throw new \Exception("Tried moving a conn that was assigned to a non-existant client");
        
        // Get the new target for the move
        try {
            $new_client = $this->users->get($new_user_id);
        }
        catch(\Exception $e) {
            echo "[CLIENT] Creating new Client ({$new_user_id}) \n";
            $new_client = new server\client\WSClient($new_user_id);
        }
        
        // Perform the move
        $this->detach_conn_from_client($old_client, $conn);
        $this->attach_conn_to_client($new_client, $conn);
    }
    
    /**
    * Alias of auth_conn
    * @see auth_conn
    */
    public function un_auth_conn(ConnectionInterface $conn, server\models\IdentifyPacket $identity) {
        return $this->auth_conn($conn, $identity);
    }
}