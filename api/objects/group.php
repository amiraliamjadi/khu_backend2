<?php
class Group{

    // database connection and table name
    private $conn;
    private $table_name = "groups";

    // object properties
    public $group_id;
    public $group_name;
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
        $query = "SELECT groups.group_id, groups.group_name FROM groups INNER JOIN usergroup ON groups.group_id = usergroup.group_id WHERE user_id = " . $user_id;


        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        return $stmt;
    }


}
?>