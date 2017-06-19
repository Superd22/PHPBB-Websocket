<?php namespace scfr\WSBB\server\client;

use scfr\WSBB\server;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use phpbb\user_loader;
use phpbb\auth\auth as PhpbbAuth;

/**
* Represents a client connected to the WS Server
*/
class WSClient {
    /**
    * connection object for this client
    * @var SplObjectStorage
    */
    protected $conn;
    
    /**
    * If the current user is authed
    * @var boolean
    */
    protected $authed = false;
    
    /**
    * PHPBB User ID for the given authed user;
    * @var integer
    */
    protected $user_id;
    
    
    /**
    * PHPBB Auth object
    * @var PhpbbAuth
    */
    protected $auth;
    
    /**
    * @param integer $user_id the userid for this client
    */
    public function __construct($user_id = 1) {
        $this->user_id = $user_id;
        if($this->user_id > 1) $this->authed = true;
        
        $this->conn = new \SplObjectStorage;
        
        $this->init_auth();
    }
    
    /**
    * Populates the phpbb auth cache for this user
    */
    private function init_auth() {
        $this->auth = new PhpbbAuth();
        $udata = $this->auth->obtain_user_data($this->user_id);
        
        $this->auth->acl($udata);
    }
    
    /**
    * Attach a conn to this client
    * The client will then send every event it gets to this conn
    *
    * @param ConnectionInterface $conn
    */
    public function attach_conn(ConnectionInterface $conn) {
        $this->conn->attach($conn);
    }
    
    /**
    * Remove a conn from this client
    * No further event will be sent to this conn by this client
    *
    * @param ConnectionInterface $conn
    * @return boolean if this client still has active conns or not.
    */
    public function detach_conn(ConnectionInterface $conn) {
        $this->conn->detach($conn);
        return ($this->conn->count() > 0);
    }
    
    /**
    * Get the user id for this client
    * @return integer
    */
    public function get_user_id() {
        return $this->user_id;
    }
    
    /**
    * Get the phpbb auth object for this client
    * @return phpbb\auth\auth
    */
    public function get_auth() {
        return $this->auth;
    }
    
    /**
    * Send data to all active connection for this user
    * @param mixed $data
    */
    public function broadcast_data($data) {
        echo "[WSClient] Broadcasting data to user ({$this->get_user_id()}) \n";
        foreach($this->conn as $conn) {
            $conn->send($data);
        }
    }
    
}