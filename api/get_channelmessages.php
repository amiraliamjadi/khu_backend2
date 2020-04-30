<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include database and object files
include_once 'config/database.php';
include_once 'objects/channelmessage.php';

// instantiate database and channel object
$database = new Database();
$db = $database->getConnection();

// initialize object
$channel_message = new ChannelMessage($db);

// get posted data
$data = json_decode(file_get_contents("php://input"));

// set values
$channel_message->channel_id = $data->channel_id;

// query channelmessages
$stmt = $channel_message->read();
$num = $stmt->rowCount();

// check if more than 0 record found
if($num>0){

    // channelmessages array
    $channelmessages_arr=array();
    $channelmessages_arr["records"]=array();

    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);

        $channelmessage_item=array(
            "channel_message_id" => $row['channel_message_id'],
            "channelmessages_time" => $row['channelmessages_time'],
            "text" => $row['text'],
            "username" => $row['username'],
            "user_name" => $row['user_name'],

        );

        array_push($channelmessages_arr["records"], $channelmessage_item);
    }

    // set response code - 200 OK
    http_response_code(200);

    // show products data in json format
    echo json_encode($channelmessages_arr);
}

// no products found will be here
else{

    // set response code - 404 Not found
    http_response_code(404);

    // tell the user no products found
    echo json_encode(
        array("message" => "No channel message found")
    );
}