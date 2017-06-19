<?php namespace scfr\wsbb\server\targets;

use scfr\wsbb\server as server;

/**
* Defines the base TargetFinder class, used on a restricted event to check
* what currently connected user can get said event
*/
abstract class TargetFinder {
    /**
    * Client Manager service instance
    * @var services\ClientManager
    */
    protected $client_manager;
    protected $in_packet;
    private $out_packet;
    
    public function __construct(server\models\WSInputPacket $packet) {
        $this->client_manager = server\services\ClientManager::get_service();

        $this->in_packet = $packet;
        $this->make_packet($packet->type, $packet->data);
    }
    
    protected function make_packet($event_name = "default_event", $event_data = "") {
        $this->packet = server\OutputHandler::encode([
            "event" => $event_name,
            "data"  => $event_data,
        ]);
    }
    
    protected function send_event_to_client(server\client\WSClient $client) {
        $client->broadcast_data($this->packet);
    }
    
    /**
     * Perform the actual permission check on the given client.
     * Returns true if the event should be broadcasted, false(y) otherwise
     *
     * @param server\client\WSClient $client
     * @return boolean
     */
    abstract protected function perfom_check_on_client(server\client\WSClient $client);
    
    protected function perform_check_on_first_client() {
        $guest_user = $this->client_manager->get_users()->get(1);
        return $this->perfom_check_on_client($guest_user);
    }
    
    /**
     * Main method to find targets and send them event if they have the required permission(s)
     * @return void
     */
    public function target_find() {
        $users = $this->client_manager->get_users();

        $sendAll = $this->perform_check_on_first_client();
        foreach($users as $user) {
            if($sendAll || ( $user->get_user_id() != 1 && $this->perfom_check_on_client($user) ) ) $this->send_event_to_client($user);
        }
    }
    
}