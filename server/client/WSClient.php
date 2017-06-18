<?php namespace scfr\WSBB\server\client;

use scfr\WSBB\server;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use phpbb\user_loader;

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
    * @param integer $user_id the userid for this client
    */
    public function __construct($user_id = 0) {
        $this->user_id = $user_id;
        if($this->user_id > 0) $this->authed = true;

        $this->conn = new \SplObjectStorage;
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

    
}