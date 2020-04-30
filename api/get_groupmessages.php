<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include database and object files
include_once 'config/database.php';
include_once 'objects/groupmessage.php';

// instantiate database and group object
$database = new Database();
$db = $database->getConnection();

// initialize object
$group_message = new GroupMessage($db);

// get posted data
$data = json_decode(file_get_contents("php://input"));

// set values
$group_message->group_id = $data->group_id;

// query groupmessages
$stmt = $group_message->read();
$num = $stmt->rowCount();

// check if more than 0 record found
if($num>0){

    // groupmessages array
    $groupmessages_arr=array();
    $groupmessages_arr["records"]=array();

    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);

        $groupmessage_item=array(
            "group_message_id" => $row['group_message_id'],
            "groupmessages_time" => $row['groupmessages_time'],
            "text" => $row['text'],
            "username" => $row['username'],
            "user_name" => $row['user_name'],

        );

        array_push($groupmessages_arr["records"], $groupmessage_item);
    }

    // set response code - 200 OK
    http_response_code(200);

    // show products data in json format
    echo json_encode($groupmessages_arr);
}

// no products found will be here
else{

    // set response code - 404 Not found
    http_response_code(404);

    // tell the user no products found
    echo json_encode(
        array("message" => "No group message found")
    );
}