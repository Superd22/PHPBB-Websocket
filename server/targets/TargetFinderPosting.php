<?php namespace scfr\wsbb\server\targets;

use scfr\wsbb\server;

/**
 * Target finder for a posting type event from phpb
 * (ie new thread / reply / edit)
 */
class TargetFinderPosting extends TargetFinder {
  
    public function __construct($) {
        parent::__construct();
        $this->make_packet("phpbb_posting", )
    }    
    
}