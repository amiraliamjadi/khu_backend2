<?php
class Channel{

    // database connection and table name
    private $conn;
    private $table_name = "channels";

    // object properties
    public $channel_id;
    public $channel_name;
    public $username;

    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }


    public function catchUserId($username)
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


    // read groups
    function read(){

        $user_id = $this->catchUserId($this->username);

        // select all query
        $query = "SELECT channels.channel_id, channels.channel_name FROM channels INNER JOIN userchannel ON channels.channel_id = userchannel.channel_id WHERE user_id = " . $user_id;


        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        return $stmt;
    }


}
?>