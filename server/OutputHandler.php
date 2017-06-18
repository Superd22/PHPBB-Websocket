<?php namespace scfr\WSBB\server;

class OutputHandler {
    public static function handle($data) {
        return json_encode($data);
    }
}