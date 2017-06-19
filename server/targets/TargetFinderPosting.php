<?php namespace scfr\wsbb\server\targets;

use scfr\wsbb\server as server;

/**
 * Target finder for a posting type event from phpb
 * (ie new thread / reply / edit)
 */
class TargetFinderPosting extends TargetFinder {
    
    protected function perfom_check_on_client(server\client\WSClient $client) {
        return $client->get_auth()->acl_get("f_read", $this->in_packet->data["post"]["forum_id"]);
    }
    
}