<?php namespace scfr\WSBB\server\services;

/**
* Service that is application wide and will can be injected as a singleton
*/
class SingletonService {
    
    /** [SingletonService] */
    protected $instance;
    
    private function __constructor() {}
    
    /**
    * Returns the instance of this singleton service
    * @return [SingletonService]
    */
    function get_service() {
        if(!self::$instance) self::$instance = new SingletonService();
        return self::$instance;
    }
}