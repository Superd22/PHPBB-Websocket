<?php namespace scfr\WSBB\server;

class InputHandler {

    public static function decode($data) {

        var_dump($data);

        $decoded = json_decode($data,true);
        var_dump($decoded);
        var_dump(is_scalar($decoded));

        if(is_scalar($decoded)) return $decoded;
        return new models\WSInputPacket($decoded);
    }

}