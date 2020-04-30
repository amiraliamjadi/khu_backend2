<?php
// 'user' object
class User{
 
    // database connection and table name
    private $conn;
    private $table_name = "users";
 
    // object properties
    public $user_id;
    public $username;
    public $password;
    public $proffessor;
    public $user_name;
 
    // constructor
    public function __construct($db){
        $this->conn = $db;
    }
 
// create new user record
function create(){
 
    // insert query
    $query = "INSERT INTO " . $this->table_name . "
            SET
                username = :username,
                password = :password,
                proffessor = :proffessor
                user_name = :user_name";
 
    // prepare the query
    $stmt = $this->conn->prepare($query);
 
    // sanitize
    $this->username=htmlspecialchars(strip_tags($this->username));
    $this->password=htmlspecialchars(strip_tags($this->password));
    $this->proffessor=htmlspecialchars(strip_tags($this->proffessor));
    $this->user_name=htmlspecialchars(strip_tags($this->user_name));
 
    // bind the values
    $stmt->bindParam(':username', $this->username);
    $stmt->bindParam(':proffessor', $this->proffessor);
    $stmt->bindParam(':user_name', $this->user_name);
 
    // hash the password before saving to database
    $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
    $stmt->bindParam(':password', $password_hash);
 
    // execute the query, also check if query was successful
    if($stmt->execute()){
        return true;
    }
 
    return false;
}
 
// check if given username exist in the database
function usernameExists(){
 
    // query to check if username exists
    $query = "SELECT user_id, username, password, proffessor, user_name
            FROM " . $this->table_name . "
            WHERE username = ?
            LIMIT 0,1";
 
    // prepare the query
    $stmt = $this->conn->prepare( $query );
 
    // sanitize
    $this->username=htmlspecialchars(strip_tags($this->username));
 
    // bind given email value
    $stmt->bindParam(1, $this->username);
 
    // execute the query
    $stmt->execute();
 
    // get number of rows
    $num = $stmt->rowCount();
 
    // if email exists, assign values to object properties for easy access and use for php sessions
    if($num>0){
 
        // get record details / values
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
 
        // assign values to object properties
        $this->user_id = $row['user_id'];
        $this->username = $row['username'];
        $this->password = $row['password'];
        $this->proffessor = $row['proffessor'];
        $this->user_name = $row['user_name'];
 
        // return true because username exists in the database
        return true;
    }
 
    // return false if username does not exist in the database
    return false;
}
 
// update() method will be here


}