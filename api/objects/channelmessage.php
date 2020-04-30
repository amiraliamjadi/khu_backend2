<?php
class ChannelMessage
{

    // database connection and table name
    private $conn;
    private $table_name = "channelmessages";

    // object properties
    public $channel_id;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->conn = $db;
    }




    // read channelmessages
    function read(){

        // select all query
        $query = "SELECT channelmessages.channel_message_id, channelmessages.channelmessages_time, channelmessages.text, users.username, users.user_name FROM channelmessages INNER JOIN users ON channelmessages.user_id = users.user_id  WHERE channel_id = " . $this->channel_id;


        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();


        return $stmt;
    }


}