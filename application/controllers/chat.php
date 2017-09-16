<?php

/**
 * The Default Example Controller Class
 *
 * @author Shreyansh Goel
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RM;
use Framework\Registry as Registry;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;

class Chat extends Controller implements MessageComponentInterface {

	
	protected $clients;
    protected $clientsIds;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->clientsIds = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients[$conn] = $conn;
        // echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;

        $full_msg = json_decode($msg);
        if(isset($full_msg->userid)){
            $this->clientsIds[$full_msg->userid] = $from;
            return;
        }
        // echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
        //     , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($full_msg->send_to as $send_to) {
            // $msg_save = new models\Chat([
            //     'to_id' => $send_to, 
            //     'from_id' => $full_msg->from, 
            //     'message' => $full_msg->message
            //     ]);
            // $msg_save->save();
            if (isset($this->clients[$this->clientsIds[$send_to]])) {
                $this->clients[$this->clientsIds[$send_to]]->send($full_msg->message);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        unset($this->clients[$conn]);

        // echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        // $conn->close();
    }

}