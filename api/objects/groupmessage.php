<?php
class GroupMessage
{

    // database connection and table name
    private $conn;
    private $table_name = "groupmessages";

    // object properties
    public $group_id;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->conn = $db;
    }




    // read groupmessages
    function read(){

        // select all query
        $query = "SELECT groupmessages.group_message_id, groupmessages.groupmessages_time, groupmessages.text, users.username, users.user_name FROM groupmessages INNER JOIN users ON groupmessages.user_id = users.user_id  WHERE group_id = " . $this->group_id;


        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();


        return $stmt;
    }


}
