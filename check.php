<?php

defined('IS_DEVELOPMENT') OR exit('No direct script access allowed');

$swrve_user_id = isset($params[1]) ? $params[1] : "";

if (trim($swrve_user_id) == "") {
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

// check if data exists
$sql1 = "SELECT * FROM reward_mytower_ios WHERE swrve_user_id = :user_id ";
$statement1 = $connection->prepare($sql1);
$statement1->execute(array(':user_id' => $swrve_user_id));
$row = $statement1->fetch(PDO::FETCH_ASSOC);

if (isset($row['is_redeemed'])) 
{
    if ($row['is_redeemed'] == FALSE || TRUE) // bypass is_redeemed
    {
        // reset value and set flag is_redeemed
        $sql2 = "UPDATE reward_mytower_ios "
                . "SET is_redeemed = true "
                . "WHERE swrve_user_id = :user_id";
        $statement2 = $connection->prepare($sql2);
        $statement2->bindParam(":user_id", $swrve_user_id);
        $statement2->execute();

        return array(
            'swrve_user_id' => $swrve_user_id,
            'bolt' => $row['bolt'],
            'karma' => $row['karma'],
            'error' => 0,
            'message' => 'Success'
        );    
    } else {
        return array(
            'swrve_user_id' => $swrve_user_id,
            'error' => 1,
            'message' => 'Already Redeemed'
        );    
    }
} else {
    return array(
        'swrve_user_id' => $swrve_user_id,
        'error' => 1,
        'message' => 'Data not available'
    );    
}

