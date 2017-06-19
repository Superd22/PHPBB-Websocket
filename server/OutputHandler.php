<?php namespace scfr\WSBB\server;

class OutputHandler {
    public static function encode($data) {
        return json_encode($data);
    }
}