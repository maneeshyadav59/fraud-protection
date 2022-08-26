<?php
use WHMCS\Database\Capsule;

add_hook('ClientLogin', 1, function($vars) {

    $pdo = Capsule::connection()->getPdo();
   
    $client_id = $vars['userid'];
    $last_ip = $_SERVER['REMOTE_ADDR'];
    $event_time = time();
    $action_type = 'Login';

    $statement = $pdo->prepare("INSERT INTO hcil_ip_logs (client_id, ip, event_time, action_type) values(:client_id, :ip, :event_time, :action_type)");

    $sqlParams = array(
        ':client_id'=> $client_id,
        ':ip'=> $last_ip,
        ':event_time'=> $event_time,
        ':action_type'=> $action_type,
    ); 
    $statement->execute($sqlParams);
});