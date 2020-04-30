<?php
namespace MyApp;
use PDO;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {

    // database connection and table name
    private $conn;

    protected $clients;
    private $connectionUsername;
    private $connectionGroupId;
    private $connectionChannelId;

    public function __construct($db)
    {
        $this->clients = new \SplObjectStorage();
        $this->connectionUsername = [];
        $this->connectionGroupId = [];
        $this->connectionChannelId = [];
        $this->conn = $db;
    }


    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {

        $data = json_decode($msg);

        switch ($data->authorization_status) {
            case false:
                $this->connectionUsername[$from->resourceId] = $data->username_athorize;
                if ($data->group_id_athorize !== 0)
                {
                    $this->connectionGroupId[$from->resourceId] = $data->group_id_athorize;
                }
                if ($data->channel_id_athorize !== 0)
                {
                    $this->connectionChannelId[$from->resourceId] = $data->channel_id_athorize;
                }
                break;


            case true:

                // group
               if ($data->channel_status == false && $data->group_status == true)
               {

                    $group_user_items = $this->getGroupUsers($data->object_id);

                    // insert db
                   $this->insertGroupMessages($data->object_id, $data->username, $data->message, $data->date);


                   // create json
                   $myArrGroup = array(
                       "text" => $data->message,
                       "groupmessages_time" => $data->date,
                       "username" => $data->username,
                       "user_name" => $data->user_name);
                   $myJSONGroup = json_encode($myArrGroup);



                   foreach ($this->clients as $client)
                   {
                       if ($from !== $client)
                       {
                           
                           if ($this->connectionGroupId[$client->resourceId] == $data->object_id)
                           {


                               $client_user_id = $this->catchUserId( $this->connectionUsername[$client->resourceId]);


                               foreach ($group_user_items as $group_user_item)
                               {
                                   if ( $group_user_item == $client_user_id)
                                   {
                                       $client->send($myJSONGroup);
                                       break;
                                   }
                               }



                           }

                       }

                   }
               }
               // channel
               if ($data->channel_status == true && $data->group_status == false)
               {
                   $channel_user_items = $this->getChannelUsers($data->object_id);

                   // insert db
                   $this->insertChannelMessages($data->object_id, $data->username, $data->message, $data->date);

                   // create json
                   $myArrChannel = array(
                       "text" => $data->message,
                       "channelmessages_time" => $data->date,
                       "username" => $data->username,
                       "user_name" => $data->user_name);
                   $myJSONChannel = json_encode($myArrChannel);



                   foreach ($this->clients as $client)
                   {
                       if ($from !== $client)
                       {
                           if ($this->connectionChannelId[$client->resourceId] == $data->object_id)
                           {


                               $client_user_id = $this->catchUserId( $this->connectionUsername[$client->resourceId]);


                               foreach ($channel_user_items as $channel_user_item)
                               {
                                   if ( $channel_user_item == $client_user_id)
                                   {
                                       $client->send($myJSONChannel);
                                       break;
                                   }
                               }



                           }

                       }

                   }
               }


        }



    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        $this->clients->offsetUnset($conn);
        unset($this->connectionUsername[$conn->resourceId]);

        if (array_key_exists($conn->resourceId,$this->connectionGroupId) )
        {
            unset($this->connectionGroupId[$conn->resourceId]);
        }
        if (array_key_exists($conn->resourceId,$this->connectionChannelId) )
        {
            unset($this->connectionChannelId[$conn->resourceId]);
        }

        echo "Connection Closed ({$conn->resourceId})\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred";
    }





    private function insertGroupMessages($group_id, $username, $text, $groupMessages_date)
    {
        $user_id = $this->catchUserId($username);


        $query = "INSERT INTO groupmessages (group_message_id, group_id, user_id, text, groupmessages_time) VALUES (NULL, '" . $group_id . "', '" . $user_id . "', '" . $text . "', '" . $groupMessages_date . "')";


        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();
    }



    private function insertChannelMessages($channel_id, $username, $text, $channelMessages_date)
    {
        $user_id = $this->catchUserId($username);


        $query = "INSERT INTO channelmessages (channel_message_id, channel_id, user_id, text, channelmessages_time) VALUES (NULL, '" . $channel_id . "', '" . $user_id . "', '" . $text . "', '" . $channelMessages_date . "')";


        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();
    }



    private function catchUserId($username)
    {

        $query = "SELECT user_id FROM users WHERE username = " . $username;

        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();
        $num = $stmt->rowCount();

        if ($num > 0)
        {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row['user_id'];
        }
        else{
            return null;
        }


    }


    private function getGroupUsers($group_id_data)
    {

        $query = "SELECT usergroup.user_id from usergroup WHERE group_id = " . $group_id_data;
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();
        $num = $stmt->rowCount();

        if ($num > 0)
        {
            $group_user_items = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
            {
                extract($row);
                array_push($group_user_items,$row['user_id']);
            }

            return $group_user_items;
        }
        else{
            return null;
        }


    }


    private function getChannelUsers($channel_id_data)
    {

        $query = "SELECT userchannel.user_id from userchannel WHERE channel_id = " . $channel_id_data;
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();
        $num = $stmt->rowCount();

        if ($num > 0)
        {
            $channel_user_items = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
            {
                extract($row);
                array_push($channel_user_items,$row['user_id']);
            }

            return $channel_user_items;
        }
        else{
            return null;
        }


    }




}
