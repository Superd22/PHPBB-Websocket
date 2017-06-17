<?php namespace scfr\WSBB\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Listener implements EventSubscriberInterface {
    /** @var \phpbb\template\template */
    protected $template;
    /** @var \phpbb\user */
    protected $user;
    /** @param \phpbb\db\driver\driver_interface */
    protected $db;

    /**
     * Constructor
     *
     * @param \phpbb\template\template             $template          Template object
     * @param \phpbb\user   $user             User object
     * @param \phpbb\db\driver\driver_interface   $db             Database object
     * @access public
     */
    public function __construct( \phpbb\template\template $template, \phpbb\user $user, \phpbb\db\driver\driver_interface $db) {
        $this->template = $template;
        $this->user = $user;
        $this->db = $db;
    }

    static public function getSubscribedEvents() {
        return array();
    }
}
?>
