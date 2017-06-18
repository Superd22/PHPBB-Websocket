<?php namespace scfr\WSBB\tests;

use PHPUnit\Framework\TestCase;
use scfr\WSBB\server as server;
use Ratchet\ConnectionInterface;

/**
* @covers ClientManager
*/
final class ClientManagerTest extends TestCase
{
    
    public function test_new_conn() {
        $manager = new server\services\ClientManager();
        $con = $this->getMockConn();

        $manager->new_conn($con);
    }

    private function getMockConn() {
        $conn = $this->createMock(ConnectionInterface::class);

        return $conn;
    }
}
?>