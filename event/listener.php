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
        global $db, $phpbb_content_visibility;
        
        $tId = $event['post_data']['topic_id'];

        // We're a new post
        if(!isset($event['post_data']['topic_id'])) {
            $sql = "SELECT topic_id FROM " . TOPICS_TABLE . " WHERE forum_id=".$event['post_data']['forum_id']." ORDER BY topic_id DESC LIMIT 1";
            $result = $db->sql_query($sql);
            $data = $db->sql_fetchrow($result);

            $tId = $data['topic_id'];
        }
        
        // We want the latest post_data
        $sql = 'SELECT f.*, t.*
        FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f
        WHERE t.topic_id = " .$tId.  "
        AND f.forum_id = t.forum_id
        AND " . $phpbb_content_visibility->get_visibility_sql('topic', $event['post_data']['forum_id'], 't.');
        
        $result = $db->sql_query($sql);
        $new_p_data = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        
        $post_data = array_merge($event['post_data'], $new_p_data);
        
        EventDispatcher::broadcast_event("PHPBB_POSTING", [
        "mode" => $event['mode'],
        "post" => $post_data,
        "user" => $event['post_author_name'],
        "data" => $event['data'],
        ]);
        
    }
    
    
}
?>