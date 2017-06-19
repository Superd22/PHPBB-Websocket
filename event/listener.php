<?php namespace scfr\wsbb\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use scfr\wsbb\phpbb\EventDispatcher;

class listener implements EventSubscriberInterface
{
    
    /**
    * Constructor
    */
    public function __construct() {
        
    }
    
    static public function getSubscribedEvents()
    {
        return array(
        'core.index_modify_page_title' => 'json_header',
        'core.posting_modify_submit_post_after' => 'post_event',
        'core.common' => 'json_header',
        );
    }
    
    
    public function json_header($event) {
        //$this->send_packet_to_ws(["test" => true]);
    }
    
    public function post_event($event) {
        
        EventDispatcher::broadcast_event("PHPBB_POSTING", [
            "mode" => $event['mode'],
            "post" => $event['post_data'],
            "data" => $event['data'],
        ]);
        
    }
    
    
}
?>