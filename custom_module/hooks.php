<?php
use WHMCS\View\Menu\Item as MenuItem;
use WHMCS\Database\Capsule;

add_hook('ClientAreaPrimaryNavbar', 1, function (MenuItem $primaryNavbar)
{
    foreach (Capsule::table('custom_module')->where('add_to_navbar', '1')->get() as $client) {
        $custom = $client->title;
        // var_dump($custom);
        $client = Menu::context('client');
        $primaryNavbar->addChild('custompage',array('label' => Lang::trans($custom),'uri' => 'https://dev.kuroit.co.uk/index.php?m=custom_module','order' => 99,));
        if (!$client && !is_null($primaryNavbar->getChild('Home'))) {
            $primaryNavbar->getChild('Home')->setUri('clientarea.php');
        }
    }
});

add_hook('UserLogin', 1, function($vars) {

    $pdo = Capsule::connection()->getPdo();
   
    $client_id = $vars['user']['id'];
    $last_ip = $vars['user']['last_ip'];
    $event_time = $vars['user']['last_login'];

    // $statement = $pdo->prepare("INSERT INTO hcil_ip_logs (client_id, ip, event_time, action_type) values(:client_id, :ip, :event_time, :action_type)");

    // $sqlParams = array(
    //     ':client_id'=> $client_id,
    //     ':ip'=> $last_ip,
    //     ':event_time'=> $event_time,
    //     ':action_type'=> 'login',
    // ); 
    // $sts = $statement->execute($sqlParams);

    $statement = $pdo->prepare("SELECT client_id,ip,COUNT(ip) FROM hcil_ip_logs GROUP BY ip HAVING COUNT(ip) > 1");
    $statement->execute();

    $sts = $statement->fetchAll(PDO::FETCH_ASSOC);
    // echo '<pre>';
    // print_r($sts);
    // die('ghghg');

});

// add_hook('OrderPaid', 1, function($vars) {
//     echo '<pre>';
//     var_dump($vars);
//     echo '</pre>';

// });



