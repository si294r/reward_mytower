<?php

defined('IS_DEVELOPMENT') OR exit('No direct script access allowed');

$json = json_decode($input);

$data['swrve_user_id'] = isset($json->swrve_user_id) ? $json->swrve_user_id : "";
$data['bolt'] = isset($json->bolt) ? $json->bolt : 0;
$data['karma'] = isset($json->karma) ? $json->karma : 0;

if (trim($data['swrve_user_id']) == "") {
    return array(
        "status" => FALSE,
        "message" => "Error: swrve_user_id is empty"
    );
}

include("/var/www/redshift-config2.php");
$connection = new PDO(
    "pgsql:dbname=$rdatabase;host=$rhost;port=$rport",
    $ruser, $rpass
);

// create record if not exists
$sql1 = "INSERT INTO reward_mytower_ios (swrve_user_id)
SELECT :user_id1 WHERE NOT EXISTS (
    SELECT 1 FROM reward_mytower_ios 
    WHERE swrve_user_id = :user_id2
  )";
$statement1 = $connection->prepare($sql1);
$statement1->bindParam(":user_id1", $data['swrve_user_id']);
$statement1->bindParam(":user_id2", $data['swrve_user_id']);
$statement1->execute();

// save reward
$sql2 = "UPDATE reward_mytower_ios "
        . "SET bolt = :bolt, karma = :karma, is_redeemed = false "
        . "WHERE swrve_user_id = :user_id  ";
$statement2 = $connection->prepare($sql2);
$statement2->bindParam(":bolt", $data['bolt']);
$statement2->bindParam(":karma", $data['karma']);
$statement2->bindParam(":user_id", $data['swrve_user_id']);
$statement2->execute();

$data['affected_row'] = $statement2->rowCount();
$data['error'] = 0;
$data['message'] = 'Success';

return $data;