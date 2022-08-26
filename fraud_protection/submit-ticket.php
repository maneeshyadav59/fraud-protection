
<?php
use WHMCS\Database\Capsule;

add_hook('TicketOpen', 1, function($vars) {
    foreach ($_POST['relatedservices'] as $key => $val) {
    	$username = $_POST[$val.'-username'];
    	$password = $_POST[$val.'-password'];

		$uid 		= $vars['userid'];
		$serviceID	= str_replace("S","",$val);
		$ticketID	= $vars['ticketid'];
		$plaintext 	= json_encode([$username,$password]);
		$hash_key 	= $uid . $ticketID . "V4%yX8" . $serviceID;

		$hashID = str_encryptaesgcm($plaintext,$hash_key,'base64');

		Capsule::table('tbl_ticket_hash')->insert([
			    'uid' 			=> $uid,
			    'ticket_id' 	=> $ticketID,
			    'service_id' 	=> $serviceID,
			    'hash_id' 		=> $hashID
			]);
    	
    }

});


add_hook('AdminAreaViewTicketPage', 1, function($vars) {
    $ticketID = $vars['ticketid'];
    $tickets = Capsule::table('tbl_ticket_hash')->where('ticket_id', $ticketID)->get();
    
    foreach($tickets as $ticket){
        $serviceID = $ticket->service_id;
        $service = Capsule::table('tblhosting')->where('id', $serviceID)->get();
        $hash_key = $ticket->uid . $ticket->ticket_id . "V4%yX8" . $serviceID;
        $hash = $ticket->hash_id;
        $cred = json_decode(str_decryptaesgcm($hash,$hash_key,'base64'));
        $username = $cred[0];
        $password = $cred[1];
        
        $ticketRow .= '<tr style="text-align:center;">
                            <td><a href="clientsservices.php?userid=10&id='.$serviceID.'">'.$service[0]->domain.'</a></td>
                            <td>'.$username.'</td>
                            <td>'.$password.'</td>
                        </tr>';
        
    }
    return'<script type="text/javascript">
            $(document).ready(function() {
               let elem = `<h1 style="margin:30px 0 0 0";>Related Services</h1>
                        <div class="tablebg">
                            <table class="datatable" id="relatedservicestbl" width="100%" border="0" cellspacing="1" cellpadding="3">
                                <thead>
                                    <tr data-original="true">
                                        <th>Services</th>
                                        <th>Username</th>
                                        <th>Password</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    '.$ticketRow.'
                                </tbody>
                            </table>
                        </div>`;
                $("#selectRelatedService").append(elem);
            })
        </script>';
});

add_hook('ClientAreaPageViewTicket', 1, function($vars) {
    $tid = $vars['tid'];
    $ticket = Capsule::table('tbltickets')->where('tid', $tid)->get();
    $tickets = Capsule::table('tbl_ticket_hash')->where('ticket_id', $ticket[0]->id)->get();
    foreach($tickets as $ticket){
        $serviceID = $ticket->service_id;
        $service = Capsule::table('tblhosting')->where('id', $ticket->service_id)->get();
        $serviceName = $service[0]->domain;
        $hash_key = $ticket->uid . $ticket->ticket_id . "V4%yX8" . $serviceID;
        $hash = $ticket->hash_id; 
        $cred = json_decode(str_decryptaesgcm($hash,$hash_key,'base64'));
        $username = $cred[0];
        $password = $cred[1];
        echo $username;
        echo '<br>';
        echo $password;
        die('gfdhh');
    }
    // global $smarty;
    // $smarty->assign('VARIABLE_COMES_FROM_HOOK',$tickets);
    // return $VARIABLE_COMES_FROM_HOOK;

});
 

function str_encryptaesgcm($plaintext, $password, $encoding = null) {
    if ($plaintext != null && $password != null) {
        $keysalt = openssl_random_pseudo_bytes(16);
        $key = hash_pbkdf2("sha512", $password, $keysalt, 20000, 32, true);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length("aes-256-gcm"));
        $tag = "";
        $encryptedstring = openssl_encrypt($plaintext, "aes-256-gcm", $key, OPENSSL_RAW_DATA, $iv, $tag, "", 16);
        return $encoding == "hex" ? bin2hex($keysalt.$iv.$encryptedstring.$tag) : ($encoding == "base64" ? base64_encode($keysalt.$iv.$encryptedstring.$tag) : $keysalt.$iv.$encryptedstring.$tag);
    }
}

function str_decryptaesgcm($encryptedstring, $password, $encoding = null) {
    if ($encryptedstring != null && $password != null) {
        $encryptedstring = $encoding == "hex" ? hex2bin($encryptedstring) : ($encoding == "base64" ? base64_decode($encryptedstring) : $encryptedstring);
        $keysalt = substr($encryptedstring, 0, 16);
        $key = hash_pbkdf2("sha512", $password, $keysalt, 20000, 32, true);
        $ivlength = openssl_cipher_iv_length("aes-256-gcm");
        $iv = substr($encryptedstring, 16, $ivlength);
        $tag = substr($encryptedstring, -16);
        return openssl_decrypt(substr($encryptedstring, 16 + $ivlength, -16), "aes-256-gcm", $key, OPENSSL_RAW_DATA, $iv, $tag);
    }
}
