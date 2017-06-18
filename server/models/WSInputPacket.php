<?php namespace scfr\WSBB\server\models;

class WSInputPacket {
    public $type;
    public $data;
    
    public function __construct($data) {
        if(empty($data['type']) || empty($data['data'])) throw new \Exception("Invalid WSInput Packet");
        
        $this->type = $data['type'];
        $this->data = $data["data"];
    }
    
}
?>