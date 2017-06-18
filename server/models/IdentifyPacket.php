<?php namespace scfr\WSBB\server\models;

class IdentifyPacket {
  public $user_id;
  public $session_id;  

  public function __construct($data) {
    if(empty($data['user_id']) || empty($data['session_id'])) throw new \Exception("Invalid Identify Packet");

    $this->user_id = (integer) $data['user_id'];
    $this->session_id = $data["session_id"];
  }

}
?>