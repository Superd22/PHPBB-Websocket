<?php namespace scfr\WSBB\server;

class InputHandler {

    public static function decode($data) {
        return new models\WSInputPacket(json_decode($data,true));
    }

}