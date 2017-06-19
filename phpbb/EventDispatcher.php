<?php namespace scfr\wsbb\phpbb;

final class EventDispatcher {
    
    /**
    * Sends an event to the websocket stream for it to handle.
    *
    * @param string $event_type the type of event to send
    * @param mixed $event_data the data to send
    */
    public static function broadcast_event($event_type, $event_data) {
        $packet = [
            "type" => $event_type,
            "data" => $event_data
        ];

        self::send_packet_to_ws($packet);
    }
    
    private static function send_packet_to_ws($packet) {
        $context = new \ZMQContext();
        $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'wsbbsock');

        /**
         * @todo allow ip config
         */
        $socket->connect("tcp://127.0.0.1:5555");
        
        $socket->send(json_encode($packet));
    }
}