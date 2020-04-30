<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include database and object files
include_once 'config/database.php';
include_once 'objects/group.php';
include_once 'objects/channel.php';

// instantiate database and group object
$database = new Database();
$db = $database->getConnection();

// initialize object
$group = new Group($db);
$channel = new Channel($db);


// get posted data
$data = json_decode(file_get_contents("php://input"));

// set values
$group->username = $data->username;
$channel->username = $data->username;

// query products
$stmt_group = $group->read();
$num_group = $stmt_group->rowCount();

$stmt_channel = $channel->read();
$num_channel = $stmt_channel->rowCount();

// check if more than 0 record found
if($num_group>0 || $num_channel>0)
{

    // groups array
    $groups_arr=array();
    $groups_arr["group_records"]=array();

    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt_group->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);

        $group_item=array(
            "group_id" => $row['group_id'],
            "group_name" => $row['group_name']
        );

        array_push($groups_arr["group_records"], $group_item);
    }



    $channels_arr = array();
    $channels_arr["channel_records"]=array();

    while ($row = $stmt_channel->fetch(PDO::FETCH_ASSOC))
    {
        extract($row);

        $channel_item = array(
            "channel_id" => $row['channel_id'],
            "channel_name" => $row['channel_name']
        );

        array_push($channels_arr["channel_records"],$channel_item);

    }

    $final_array = array();

    $final_array = array_merge($groups_arr,$channels_arr);


    // set response code - 200 OK
    http_response_code(200);

    // show groups data in json format
    echo json_encode($final_array);
}

else{

    // set response code - 404 Not found
    http_response_code(404);

    // tell the user no groups found
    echo json_encode(
        array("message" => "No groups or channels found.")
    );
}