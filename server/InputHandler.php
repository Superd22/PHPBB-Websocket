<?php namespace scfr\WSBB\server;

class InputHandler {
    
    public static function decode($data, $as_array = true) {
        $decoded = json_decode($data, $as_array);
        if(is_scalar($decoded)) return $decoded;
        return new models\WSInputPacket($decoded);
    }
    
}