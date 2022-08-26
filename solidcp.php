<?php

/**

 * WHMCS SDK Sample Provisioning Module

 *

 * Provisioning Modules, also referred to as Product or Server Modules, allow

 * you to create modules that allow for the provisioning and management of

 * products and services in WHMCS.

 *

 * This sample file demonstrates how a provisioning module for WHMCS should be

 * structured and exercises all supported functionality.

 *

 * Provisioning Modules are stored in the /modules/servers/ directory. The

 * module name you choose must be unique, and should be all lowercase,

 * containing only letters & numbers, always starting with a letter.

 *

 * Within the module itself, all functions must be prefixed with the module

 * filename, followed by an underscore, and then the function name. For this

 * example file, the filename is "provisioningmodule" and therefore all

 * functions begin "solidcp_".

 *

 * If your module or third party API does not support a given function, you

 * should not define that function within your module. Only the _ConfigOptions

 * function is required.

 *

 * For more information, please refer to the online documentation.

 *

 * @see https://developers.whmcs.com/provisioning-modules/

 *

 * @copyright Copyright (c) WHMCS Limited 2017

 * @license https://www.whmcs.com/license/ WHMCS Eula

 */



if (!defined("WHMCS")) {

    die("This file cannot be accessed directly");

}



require_once dirname( __FILE__ ) . '/lib/SolidCP.php';





use WHMCS\Database\Capsule;



/**

 * Define module related meta data.

 *

 * Values returned here are used to determine module related abilities and

 * settings.

 *

 * @see https://developers.whmcs.com/provisioning-modules/meta-data-params/

 *

 * @return array

 */

function solidcp_MetaData()

{

    return array(

        'DisplayName' => 'SolidCP',

        'APIVersion' => '1.1', // Use API Version 1.1

        'RequiresServer' => true, // Set true if module requires a server to work

        'DefaultNonSSLPort' => '1111', // Default Non-SSL Connection Port

        'DefaultSSLPort' => '1112', // Default SSL Connection Port

    );

}



/**

 * Define product configuration options.

 *

 * The values you return here define the configuration options that are

 * presented to a user when configuring a product for use with the module. These

 * values are then made available in all module function calls with the key name

 * configoptionX - with X being the index number of the field from 1 to 24.

 *

 * You can specify up to 24 parameters, with field types:

 * * text

 * * password

 * * yesno

 * * dropdown

 * * radio

 * * textarea

 *

 * Examples of each and their possible configuration parameters are provided in

 * this sample function.

 *

 * @see https://developers.whmcs.com/provisioning-modules/config-options/

 *

 * @return array

 */

function solidcp_ConfigOptions($params)

{

    try{

        $servers = Capsule::table('tblservers')->where('type','solidcp')->get();

        $serverOptions=[];

        foreach($servers as $server){

            $serverOptions[$server->id] = $server->name;

        }

        $product = Capsule::table('tblproducts')->where('id',$_REQUEST['id'])->first();



    }catch(Exception $e){

        logModuleCall(

            'solidcp',

            __FUNCTION__,

            $params,

            $product

        );



    }

    return array(

        'User' => array(

            'Type' => 'text',

            'Size' => '25',

        ),

        'Cpu Cores' => array(

             'Type' => 'text',

             'Size' => '25',

         ),

        'Bandwidth' => array(

             'Type' => 'text',

             'Size' => '25',

         ),

        'Ram Memory' => array(

             'Type' => 'text',

             'Size' => '25',

         ),

         'Snapshots number' => array(

               'Type' => 'text',

               'Size' => '25',

           ),

        'Min Dynamic Ram' => array(

               'Type' => 'text',

               'Size' => '25',

           ),

        'Disk Space' => array(

               'Type' => 'text',

               'Size' => '25',

           ),

         

        'Max Dynamic Ram' => array(

             'Type' => 'text',

             'Size' => '25',

         ),

         'Disk minimum iops' => array(

               'Type' => 'text',

               'Size' => '25',

           ),



        'Buffer Dynamic Ram' => array(

              'Type' => 'text',

              'Size' => '25',

          ),

          'Disk maximum iops' => array(

               'Type' => 'text',

               'Size' => '25',

           ),

        'Priority Dynamic Ram' => array(

              'Type' => 'text',

              'Size' => '25',

          ),

         'External Ips' => array(

              'Type' => 'text',

              'Size' => '25',

           ),

         'Internal Ips' => array(

              'Type' => 'text',

              'Size' => '25',

         ),



        'DVD installed' => array(

             'Type' => 'yesno'

          ),

        'Boot From CD' => array(

             'Type' => 'yesno'

          ),

         'Num Lock' => array(

              'Type' => 'yesno'

          ),

        "Reinstall Allowed" => array(

             'Type' => 'yesno'

         ),



        'Start/Stop Allowed' => array(

             'Type' => 'yesno'

          ),

        'Pause/Resume Allowed' => array(

              'Type' => 'yesno'

           ),

        "Reboot Allowed" => array(

            'Type' => 'yesno'

        ),

        "Reset Allowed" => array(

             'Type' => 'yesno'

        ),

        "Enable Secure Boot" => array(

             'Type' => 'yesno'

        ),

        'Use location from addon' => array(

            'Type' => 'yesno'

        ),

    );

}



/**

 * Provision a new instance of a product/service.

 *

 * Attempt to provision a new instance of a given product/service. This is

 * called any time provisioning is requested inside of WHMCS. Depending upon the

 * configuration, this can be any of:

 * * When a new order is placed

 * * When an invoice for a new order is paid

 * * Upon manual request by an admin user

 *

 * @param array $params common module parameters

 *

 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/

 *

 * @return string "success" or an error message

 */

function solidcp_CreateAccount(array $params)

{

    try {

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $freeSpaces = [];

        $locations = Capsule::table("mod_solidcp_locations")->get();

        $userId = 0;

        foreach($locations as $loc){

            if( $loc->user_id == trim($params['customfields']['location']) ){

                $userId = $loc->user_id;

            }

        }

        if($params["configoption24"]){

            $servers = Capsule::table("mod_solidcp_pool")->where("user_id",$userId)->where("ips",'>',0)->where("memory",'>',0)->get();

            foreach ($servers as $server) {

                if( $server->memory >= $params["configoption4"] ){

                    $freeSpaces[$server->memory] = $server->server_id;

                }

            }

        }else{

            $servers = $api->getPackages($userId);

            if( is_array( $servers->NewDataSet->Table ) ){

                $servers = $servers->NewDataSet->Table;

            }else{

                $servers = [ $servers->NewDataSet->Table ];

            }



            foreach ($servers as $server) {

                $spaces = $api->getPackageQuotas($server->PackageID);

                $memory = $spaces['VPS2012.Ram']->remaining;

                $availableIps = $api->getPackageAvailableExternalIpAddresses($server->PackageID);

                if( $memory >= $params["configoption4"] ){

                    $freeSpaces[$memory] = $server->PackageID;

                }

            }

        }

        krsort($freeSpaces);

        $packageId = (int) reset($freeSpaces);

        if( !$packageId ){

            return "No free space available";

        }

        $operatingSystems = $api->getOperatingSystemTemplates($packageId);

        $operatingSystem = end( $operatingSystems );

        foreach( $operatingSystems as $os){

            if($os->Path == $params['customfields']['os'] ){

                $operatingSystem = $os;

            }

        }

        // Check available ips

        $availableIps = $api->getPackageAvailableExternalIpAddresses($packageId);

        if( !$availableIps){

            return "No free ip availables";

        }

        $serviceIp = ''; // Will be automatically asigned by the provisioning

        

        $hostnameArray = explode('.',$params['customfields']['hostname']);

        $hostname = $hostnameArray[0] .  "." . $params['serviceid'];

        $password = solidcp_generatePassword();

        $command = 'EncryptPassword';

        $postData = array(

            'password2' => $password,

        );

        $results = localAPI($command, $postData);

        Capsule::Table('tblhosting')->where('id',$params['serviceid'])->update(['password' => $password]);

        $serverParams = [

             'packageId' => $packageId,

             'privateAddressesNumber' => $params["configoption14"],

             'externalAddressesNumber' => $params["configoption13"],

             'hostname' => $hostname,

             'cpuCores' => $params["configoption2"],

             'ramMB' => $params["configoption4"],

             'DynamicMemoryMinimum' => $params["configoption6"],

             'DynamicMemoryMaximum' => $params["configoption8"],

             'DynamicBuffer' => $params["configoption10"],

             'DynamicPriority' => $params["configoption12"],

             'hddGB' => $params["configoption7"],

             'hddMaximumIOPS' => $params["configoption11"],

             'hddMinimumIOPS' => $params["configoption9"],

             'snapshotsNumber' => $params["configoption5"],

             'dvdInstalled' => $params["configoption15"]?1:0,

             'bootFromCD' => $params["configoption16"]?1:0,

             'numLock' => $params["configoption17"]?1:0,

             'startShutdownAllowed' => $params["configoption19"]?1:0,

             'pauseResumeAllowed' =>  $params["configoption20"]?1:0,

             'rebootAllowed' => $params["configoption21"]?1:0,

             'resetAllowed' => $params["configoption22"]?1:0,

             'reinstallAllowed' =>  $params["configoption18"]?1:0,

             'externalNetworkEnabled' => $params["configoption13"]?1:0,

             'privateNetworkEnabled' => $params["configoption14"]?1:0,

             'osTemplateFile' => $operatingSystem->Path,

             'password' => $password,

             'ip' => $serviceIp

         ];

        if( $params['customfields']['mac']){

            $serverParams['mac'] = $params['customfields']['mac'];

        }

        $response = $api->createVirtualMachine($serverParams);

        if( !$response->IsSuccess ){

            return $response->error;

        }

        logModuleCall(

            'solidcp',

            __FUNCTION__,

            $serverParams,

            serialize($response)

        );



        $vmId = $response->Value;

        $row = Capsule::table('tblcustomfields')->where('fieldname','like', 'vm_id%')->where('relid', $params['pid'])->first();

        Capsule::table('tblcustomfieldsvalues')->where('fieldid', $row->id)->where('relid', $params['serviceid'])->update(['value' => $vmId]);

        $row = Capsule::table('tblcustomfields')->where('fieldname','like', 'hostname%')->where('relid', $params['pid'])->first();

        Capsule::table('tblcustomfieldsvalues')->where('fieldid', $row->id)->where('relid', $params['serviceid'])->update(['value' => $hostname]);

        Capsule::Table('tblhosting')->where('id',$params['serviceid'])->update(['password' => $results["password"]]);

        Capsule::Table('tblhosting')->where('id',$params['serviceid'])->update(['username' => "Administrator"]);

        sleep(3);

        $ipAddresses = $api->getVirtualMachineIpAddresses($vmId);

        $info = $api->getVirtualMachine($vmId);

        $mac = $info->ExternalNicMacAddress;

        $row = Capsule::table('tblcustomfields')->where('fieldname','like', 'mac%')->where('relid', $params['pid'])->first();

        Capsule::table('tblcustomfieldsvalues')->where('fieldid', $row->id)->where('relid', $params['serviceid'])->update(['value' => $mac]);

        if( $ipAddresses[0] ){

            Capsule::Table('tblhosting')->where('id',$params['serviceid'])->update(['dedicatedip' => $ipAddresses[0]->IPAddress]);

        }

        Capsule::table("tblhosting")->where("id",$params["serviceid"])->update(["domain" => $hostnameArray[0]]);

        // Update package stock, no longer in use since the location pool is developed

         /* $stock = 0;

        foreach( $servers as $server ){

            if( $server->memory >= $params["configoption4"] ){

                $stock += (int) ($server->memory / $params["configoption4"]);

                $stock--;

            }

        }



        if ($stock < 1) {

            $stock = 0;

        }  */



        Capsule::table('tblproducts')->where('id',$params['pid'])->update(['qty' => $stock]);



        // Update addon pool stock



        $server = Capsule::table('mod_solidcp_pool')->where('server_id',$packageId)->first();

        Capsule::table('mod_solidcp_pool')->where('server_id',$packageId)->update(

            [

                'memory' => $server->memory - $params["configoption4"],

                'ips' => $server->ips - $params["configoption13"]

            ]

        );



        sleep(12);

    } catch (Exception $e) {

        // Record the error in WHMCS's module log.

        if( base64_decode($e->getMessage(), true) ){

            $message = base64_decode($e->getMessage(), true);

        }else{

            $message = $e->getMessage();

        }

        logModuleCall(

            'solidcp',

            __FUNCTION__,

            $params,

            $message

        );



        return $message;

    }



    return 'success';

}



/**

 * Suspend an instance of a product/service.

 *

 * Called when a suspension is requested. This is invoked automatically by WHMCS

 * when a product becomes overdue on payment or can be called manually by admin

 * user.

 *

 * @param array $params common module parameters

 *

 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/

 *

 * @return string "success" or an error message

 */

function solidcp_SuspendAccount(array $params)

{

    try {

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];

        $response = $api->stopVirtualMachine($virtualMachineId);

        if( !$response->IsSuccess ){

            return serialize($response);

        }

        return "success";

    } catch (Exception $e) {

        // Record the error in WHMCS's module log.

        logModuleCall(

            'solidcp',

            __FUNCTION__,

            $params,

            $e->getMessage()

        );



        return $e->getMessage();

    }



    return 'success';

}



/**

 * Un-suspend instance of a product/service.

 *

 * Called when an un-suspension is requested. This is invoked

 * automatically upon payment of an overdue invoice for a product, or

 * can be called manually by admin user.

 *

 * @param array $params common module parameters

 *

 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/

 *

 * @return string "success" or an error message

 */

function solidcp_UnsuspendAccount(array $params)

{

    try {

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];

        $response = $api->startVirtualMachine($virtualMachineId);

        return "success";



    } catch (Exception $e) {

        // Record the error in WHMCS's module log.

        logModuleCall(

            'solidcp',

            __FUNCTION__,

            $params,

            $e->getMessage()

        );



        return $e->getMessage();

    }



    return 'success';

}



/**

 * Terminate instance of a product/service.

 *

 * Called when a termination is requested. This can be invoked automatically for

 * overdue products if enabled, or requested manually by an admin user.

 *

 * @param array $params common module parameters

 *

 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/

 *

 * @return string "success" or an error message

 */

function solidcp_TerminateAccount(array $params)

{

    try {

        if( isset($_SESSION['adminid']) && $_SESSION['adminid'] != 1 ){

            return "Only admin users ID: 1 is able to do the termination function";

        }

        // Added random sleep

        sleep ( rand ( 2, 5) );

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];

        $response = $api->deleteVirtualMachine($virtualMachineId);

        if( !$response->IsSuccess ){

            return serialize($response);

        }

        $servers = Capsule::table("mod_solidcp_pool")->get();

        // Update stock

        $stock = 0;

        foreach( $servers as $server ){

            if( $server->memory >= $params["configoption4"] ){

                $stock += (int) ($server->memory / $params["configoption4"]);

                $stock++;

            }

        }



        if ($stock < 1) {

            $stock = 0;

        }



        $product = Capsule::table('tblproducts')->where('id',$params['pid'])->first();

        Capsule::table('tblproducts')->where('id',$params['pid'])->update(['qty' => $stock]);

        return "success";



    } catch (Exception $e) {

        // Record the error in WHMCS's module log.

        logModuleCall(

            'provisioningmodule',

            __FUNCTION__,

            $params,

            $e->getMessage(),

            $e->getTraceAsString()

        );



        return $e->getMessage();

    }



    return 'success';

}





/**

 * Additional actions an admin user can invoke.

 *

 * Define additional actions that an admin user can perform for an

 * instance of a product/service.

 *

 * @see solidcp_buttonOneFunction()

 *

 * @return array

 */

function solidcp_AdminCustomButtonArray()

{

    return array(



        "Start" => "buttonStartFunction",

        "Shutdown" => "buttonShutdownFunction",

        "Restart" => "buttonRestartFunction",

        "Reset" => "buttonResetFunction",

        "Turn Off" => "buttonStopFunction",

    );

}



/**

 * Additional actions a client user can invoke.

 *

 * Define additional actions a client user can perform for an instance of a

 * product/service.

 *

 * Any actions you define here will be automatically displayed in the available

 * list of actions within the client area.

 *

 * @return array

 */

function solidcp_ClientAreaCustomButtonArray($params)

{

    $buttons =  array(

        "Reinstall" => "actionReinstallFunction",

        "Console" => "actionConsoleFunction",

        "Logs" => "actionLogs",

        "Settings" => "actionSettings",

        "ISO" => "actionIso"

    );

    if( $params["configoption12"]){

        $buttons["Snapshots"] =  "actionSnapshots";

    }

    return $buttons;

}



/**

 * Custom function for performing an additional action.

 *

 * You can define an unlimited number of custom functions in this way.

 *

 * Similar to all other module call functions, they should either return

 * 'success' or an error message to be displayed.

 *

 * @param array $params common module parameters

 *

 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/

 * @see solidcp_AdminCustomButtonArray()

 *

 * @return string "success" or an error message

 */

function solidcp_buttonRestartFunction(array $params)

{

    try {

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];

        $response = $api->rebootVirtualMachine($virtualMachineId);

        if( !$response->IsSuccess ){

            return serialize($response);

        }

        return "success";



    } catch (Exception $e) {

        // Record the error in WHMCS's module log.

        logModuleCall(

            'solidcp',

            __FUNCTION__,

            $params,

            $e->getMessage()

        );



        return $e->getMessage();

    }



    return 'success';

}



 function solidcp_buttonResetFunction(array $params)

 {

     try {

         $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

         $virtualMachineId = $params['customfields']['vm_id'];

         $response = $api->resetVirtualMachine($virtualMachineId);

         if( !$response->IsSuccess ){

             return serialize($response);

         }

         return "success";



     } catch (Exception $e) {

         // Record the error in WHMCS's module log.

         logModuleCall(

             'solidcp',

             __FUNCTION__,

             $params,

             $e->getMessage()

         );



         return $e->getMessage();

     }



     return 'success';

 }







 function solidcp_buttonStopFunction(array $params)

 {

     try {

         $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

         $virtualMachineId = $params['customfields']['vm_id'];

         $response = $api->stopVirtualMachine($virtualMachineId);

         if( !$response->IsSuccess ){

             return serialize($response);

         }

         return "success";



     } catch (Exception $e) {

         // Record the error in WHMCS's module log.

         logModuleCall(

             'solidcp',

             __FUNCTION__,

             $params,

             $e->getMessage()

         );



         return $e->getMessage();

     }



}



  function solidcp_buttonStartFunction(array $params)

  {

      try {

          $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

          $virtualMachineId = $params['customfields']['vm_id'];

          $response = $api->startVirtualMachine($virtualMachineId);

          if( !$response->IsSuccess ){

              return serialize($response);

          }

          return "success";



      } catch (Exception $e) {

          // Record the error in WHMCS's module log.

          logModuleCall(

              'solidcp',

              __FUNCTION__,

              $params,

              $e->getMessage()

          );



          return $e->getMessage();

      }



 }







 function solidcp_buttonShutdownFunction(array $params)

 {

     try {

         $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

         $virtualMachineId = $params['customfields']['vm_id'];

         $response = $api->shutdownVirtualMachine($virtualMachineId);

         if( !$response->IsSuccess ){

             return serialize($response);

         }

         return "success";



     } catch (Exception $e) {

         // Record the error in WHMCS's module log.

         logModuleCall(

             'solidcp',

             __FUNCTION__,

             $params,

             $e->getMessage()

         );



         return $e->getMessage();

     }

}

  

  



/**

 * Custom function for performing an additional action.

 *

 * You can define an unlimited number of custom functions in this way.

 *

 * Similar to all other module call functions, they should either return

 * 'success' or an error message to be displayed.

 *

 * @param array $params common module parameters

 *

 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/

 * @see solidcp_ClientAreaCustomButtonArray()

 *

 * @return string "success" or an error message

 */

function solidcp_actionReinstallFunction(array $params)

{

    try {

        require_once 'lang/en.php';

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];

        $info = $api->getVirtualMachine($virtualMachineId);

        $operatingSystems = $api->getOperatingSystemTemplates($info->PackageId);

        return array(

            'templatefile' => "templates/reinstall",

            'vars' => array(

                'lang' => $lang,

                "operatingSystems" => $operatingSystems

            ),

        );

    } catch (Exception $e) {

        // Record the error in WHMCS's module log.

        logModuleCall(

            'solidcp',

            __FUNCTION__,

            $params,

            $e->getMessage()

        );



        return $e->getMessage();

    }



    return 'success';

}





/**

 * Client area output logic handling.

 *

 * This function is used to define module specific client area output. It should

 * return an array consisting of a template file and optional additional

 * template variables to make available to that template.

 *

 * The template file you return can be one of two types:

 *

 * * tabOverviewModuleOutputTemplate - The output of the template provided here

 *   will be displayed as part of the default product/service client area

 *   product overview page.

 *

 * * tabOverviewReplacementTemplate - Alternatively using this option allows you

 *   to entirely take control of the product/service overview page within the

 *   client area.

 *

 * Whichever option you choose, extra template variables are defined in the same

 * way. This demonstrates the use of the full replacement.

 *

 * Please Note: Using tabOverviewReplacementTemplate means you should display

 * the standard information such as pricing and billing details in your custom

 * template or they will not be visible to the end user.

 *

 * @param array $params common module parameters

 *

 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/

 *

 * @return array

 */

function solidcp_ClientArea(array $params)

{

    $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

    if( isset( $_GET['json'] ) ){

        switch( $_GET['setting'] ){

            case 'shutdown':

                $enabled = $_GET['value'] == 'true'?1:0;

                Capsule::table('mod_solidcp_settings')->updateOrInsert(['serviceid' => $_GET['id'],'setting' =>  $_GET['setting']], [ 'value' => $enabled]);

                break;

            case 'heartbeat':

                $enabled = $_GET['value'] == 'true'?1:0;

                Capsule::table('mod_solidcp_settings')->updateOrInsert(['serviceid' => $_GET['id'], 'setting' =>  $_GET['setting']], [ 'value' => $enabled]);

                break;

            case 'actionlogs':

                $filters = explode('|',$_REQUEST['search']['value']);

                $actionLogs = $api->getActionLogs($params['serviceid'],$filters);

                $response = [];

                $response["recordsTotal"] = count($actionLogs);

                $response["recordsFiltered"] = count($actionLogs);

                if(!$actionLogs ){

                  $response["data"]= [];

                }else{

                    foreach($actionLogs as $log){

                        $contact = Capsule::table('tblcontacts')->where('id',$log->contactid)->first();

                        if(!$contact){

                            if( $log->contactid == $params['userid']){

                                $name = "{$params['clientsdetails']['firstname']} {$params['clientsdetails']['lastname']}";

                            }

                        }else{

                            $name = "{$contact->firstname} {$contact->lastname}";

                        }

                      $response["data"][] = [

                          $log->created_at,

                          ucfirst($log->description),

                          $name

                      ];

                    }

                }

                echo json_encode($response);

                die();

                break;

        }

        echo json_encode(['success' => 1, 'message' => 'success']);

        die();

    }

    if( $params['status'] != 'Active' ){

        return array(

            'tabOverviewReplacementTemplate' => 'overview'

        );

    }

    require_once 'lang/en.php';

    $requestedAction = isset($_REQUEST['customAction']) ? $_REQUEST['customAction'] : '';



    try{

        $virtualMachineId = $params['customfields']['vm_id'];

        $hostnameUpdateSuccess = false;

        $passwordUpdateSuccess = false;

        $serverStartSuccess = false;

        $serverRebootSuccess = false;

        $serverShutdownSuccess = false;

        $serverStopSuccess  = false;

        $serverReinstallSuccess  = false;

        if( isset( $_GET["enable-external"] ) ){

            $p= [

                'externalNetworkEnabled' => true,

            ];

            $response = $api->updateVirtualMachineConfiguration($virtualMachineId,$p);

            $api->logAction($params['serviceid'],'enable external network',$params['templatevars']['loggedinuser']['userid']);

        }

        if( isset( $_GET["enable-private"] ) ){

            $p= [

                'privateNetworkEnabled' => true,

            ];

            $response = $api->updateVirtualMachineConfiguration($virtualMachineId,$p);

            $api->logAction($params['serviceid'],'enable private network',$params['templatevars']['loggedinuser']['userid']);

        }



        if( isset( $_POST["create-snapshot"] ) ){

            $response = $api->createVirtualMachineSnapshot($virtualMachineId);

            $successMessage = "Snapshot created successfully";

            $api->logAction($params['serviceid'],'create snapshot',$params['templatevars']['loggedinuser']['userid']);

        }

        if( isset( $_GET["restore-external"] ) ){

            $response = $api->restoreVirtualMachineExternal($virtualMachineId);

            if( $response->IsSuccess ){

                $successMessage = "External network restored successfully";

            }

            $api->logAction($params['serviceid'],'restore external network',$params['templatevars']['loggedinuser']['userid']);



        }

        if( isset( $_GET["restore-private"] ) ){

            $response = $api->restoreVirtualMachinePrivate($virtualMachineId);

            if( $response->IsSuccess ){

                $successMessage = "Private network restored successfully";

            }

            $api->logAction($params['serviceid'],'restore private network',$params['templatevars']['loggedinuser']['userid']);

        }









        if( isset( $_POST["ma"] ) ){

            switch( $_POST["ma"] ){

                case "enable-dvd":

                    $info = $api->getVirtualMachine($virtualMachineId);

                    $response = $api->toggleBootFromCd($virtualMachineId,!$info->BootFromCD);

                    if( $response->IsSuccess ){

                        $successMessage = "DVD enabled successfully";

                    }

                    $api->logAction($params['serviceid'],'enable DVD',$params['templatevars']['loggedinuser']['userid']);

                    break;

                case "mount-iso":

                    $response = $api->insertDVD($virtualMachineId,$_POST["iso-name"]);

                    if( $response->IsSuccess ){

                        $successMessage = "ISO mounted successfully";

                    }

                    $api->logAction($params['serviceid'],'mount iso',$params['templatevars']['loggedinuser']['userid']);

                    break;

               case "unmount-iso":

                    $response = $api->ejectDVD($virtualMachineId);

                    if( $response->IsSuccess ){

                        $successMessage = "ISO unmounted successfully";

                    }

                    $api->logAction($params['serviceid'],'unmount iso',$params['templatevars']['loggedinuser']['userid']);

                    break;



               case "rename-snapshot":

                    $response = $api->renameSnapshot($virtualMachineId,$_POST["snapshot-id"],$_POST["snapshot-name"]);

                    if( $response->IsSuccess ){

                        $successMessage = "Snapshot renamed successfully";

                    }

                    $api->logAction($params['serviceid'],'rename snapshot',$params['templatevars']['loggedinuser']['userid']);

                    break;



                case "apply-snapshot":

                    $response = $api->applySnapshot($virtualMachineId,$_POST["snapshot-id"]);

                    sleep(30);

                    if( $response->IsSuccess ){

                        $successMessage = "Checkpoint applied successfully";

                    }

                    $api->logAction($params['serviceid'],'apply snapshot',$params['templatevars']['loggedinuser']['userid']);

                    break;

                case "delete-snapshot":

                    $response = $api->deleteVirtualMachineSnapshot($virtualMachineId,$_POST["snapshot-id"]);

                    $successMessage = "Checkpoint deleted successfully";

                    sleep(30);

                    $api->logAction($params['serviceid'],'delete snapshot',$params['templatevars']['loggedinuser']['userid']);

                    break;

                case "update-hostname":

                    $hostnameArray = explode('.',$params['customfields']['hostname']);

                    $hostnameParams = [

                        'itemId' => $virtualMachineId ,

                        'hostname' => $_POST["hostname"] . '.' .  $hostnameArray[1]

                    ];

                    $response = $api->updateVirtualMachineHostname($hostnameParams);

                    if( $response->IsSuccess ){

                        $hostnameUpdateSuccess = true;

                        $row = Capsule::table('tblcustomfields')->where('fieldname','like', 'hostname%')->where('relid', $params['pid'])->first();

                        Capsule::table('tblcustomfieldsvalues')->where('fieldid', $row->id)->where('relid', $params['serviceid'])->update(['value' => $_POST["hostname"] . '.' .  $hostnameArray[1]]);

                        Capsule::table("tblhosting")->where("id",$params["serviceid"])->update(["domain" => $_POST["hostname"]]);

                    }

                    $api->logAction($params['serviceid'],'update hostname',$params['templatevars']['loggedinuser']['userid']);

                    break;

                case "update-password":

					$password = solidcp_generatePassword();

                    $passwordParams = [

                        'itemId' => $virtualMachineId ,

                        'password' => $password

                    ];

                    $response = $api->updateVirtualMachinePassword($passwordParams);

                    if( $response->IsSuccess ){

                        $passwordUpdateSuccess = true;

                        $command = 'EncryptPassword';

                        $postData = array(

                                'password2' => $password,

                            );

                        $results = localAPI($command, $postData);

						Capsule::Table('tblhosting')->where('id',$params['serviceid'])->update(['password' => $results['password']]);

                    }

                    $api->logAction($params['serviceid'],'update password',$params['templatevars']['loggedinuser']['userid']);

                    break;

                case "boot":

                    $response = $api->startVirtualMachine($virtualMachineId);

                    if( $response->IsSuccess ){

                        $successMessage = $lang['Solidcp.tab_client_actions.success_vm_start'];

                    }

                    $api->logAction($params['serviceid'],'boot',$params['templatevars']['loggedinuser']['userid']);

                    break;

                case "reset":

                    $response = $api->resetVirtualMachine($virtualMachineId);

                    if( $response->IsSuccess ){

                        $successMessage = $lang['Solidcp.tab_client_actions.success_vm_restart'];

                    }

                    $api->logAction($params['serviceid'],'restart',$params['templatevars']['loggedinuser']['userid']);

                    break;

                case "shutdown":

                    $response = $api->shutdownVirtualMachine($virtualMachineId);

                    if( $response->IsSuccess ){

                        $successMessage = $lang['Solidcp.tab_client_actions.success_vm_shutdown'];

                    }

                    $api->logAction($params['serviceid'],'shutdown',$params['templatevars']['loggedinuser']['userid']);

                    break;

                case "stop":

                    $response = $api->stopVirtualMachine($virtualMachineId);

                    if( $response->IsSuccess ){

                        $successMessage = $lang['Solidcp.tab_client_actions.success_vm_stop'];

                    }

                    $api->logAction($params['serviceid'],'stop',$params['templatevars']['loggedinuser']['userid']);

                    break;

                case "start":

                    $response = $api->startVirtualMachine($virtualMachineId);

                    if( $response->IsSuccess ){

                        $successMessage = $lang['Solidcp.tab_client_actions.success_vm_start'];

                    }

                    $api->logAction($params['serviceid'],'start',$params['templatevars']['loggedinuser']['userid']);

                    break;



                case "reinstall":

                    $hostnameArray = explode('.',$params['customfields']['hostname']);

                    $hostname = $hostnameArray[0] .  "." . $params['serviceid'];

                    $response = $api->reinstallVirtualMachineNew( $params, $virtualMachineId, $_POST['template'] );

                    if( $response->IsSuccess ){

                        $serverReinstallSuccess = true;

                        $vmId = $response->Value;

                        $row = Capsule::table('tblcustomfields')->where('fieldname','like', 'vm_id%')->where('relid', $params['pid'])->first();

                        Capsule::table('tblcustomfieldsvalues')->where('fieldid', $row->id)->where('relid', $params['serviceid'])->update(['value' => $vmId]);

                        $virtualMachineId = $vmId;

                        $command = 'EncryptPassword';

                        $postData = array(

                            'password2' => $response->AdministratorPassword,

                        );

                        $results = localAPI($command, $postData);

                        Capsule::Table('tblhosting')->where('id',$params['serviceid'])->update(['password' => $results["password"]]);

                        $servicePassword =  $response->AdministratorPassword;

                        $successMessage = $lang['Solidcp.tab_client_actions.success_vm_reinstall'] . " {$servicePassword}";

                        Capsule::Table('tblhosting')->where('id',$params['serviceid'])->update(['password' => encrypt($servicePassword)]);



                    }

                    $api->logAction($params['serviceid'],'reinstall',$params['templatevars']['loggedinuser']['userid']);

                    break;

            }

        }

        $rdp = file_get_contents('templates/rdp/fastest-windowed.txt', FILE_USE_INCLUDE_PATH);

        $rdpfw = str_replace("{VM_IP}",$params["model"]->dedicatedip,$rdp);

        $rdp = file_get_contents('templates/rdp/fastest-fullscreen.txt', FILE_USE_INCLUDE_PATH);

        $rdpff = str_replace("{VM_IP}",$params["model"]->dedicatedip,$rdp);

        $rdp = file_get_contents('templates/rdp/balanced-windowed.txt', FILE_USE_INCLUDE_PATH);

        $rdpbw = str_replace("{VM_IP}",$params["model"]->dedicatedip,$rdp);

        $rdp = file_get_contents('templates/rdp/balanced-fullscreen.txt', FILE_USE_INCLUDE_PATH);

        $rdpbf = str_replace("{VM_IP}",$params["model"]->dedicatedip,$rdp);

        $rdp = file_get_contents('templates/rdp/bestlooking-windowed.txt', FILE_USE_INCLUDE_PATH);

        $rdpblw = str_replace("{VM_IP}",$params["model"]->dedicatedip,$rdp);

        $rdp = file_get_contents('templates/rdp/bestlooking-fullscreen.txt', FILE_USE_INCLUDE_PATH);

        $rdpblf = str_replace("{VM_IP}",$params["model"]->dedicatedip,$rdp);

        



        $actionLogs = $api->getActionLogs($params['serviceid']);

        $info = $api->getVirtualMachine($virtualMachineId);

        if( !$info->Id || ( in_array($info->ProvisioningStatus, ['InProgress','DeletionProgress'] )) ){

            return array(

             'tabOverviewReplacementTemplate' => 'overview',

             'templateVariables' => array(

                 'deployed' => false

             ),

         );



        }

        //LL $details = $api->getVirtualMachineDetails($virtualMachineId);

        /*

        $logs = $api->getVirtualMachineLogs($params["customfields"]["location"],$info->PackageId,$virtualMachineId,date("c",strtotime("-1 week")),date("c"),100);

        if( !is_array($logs->NewDataSet->Table1) ){

            $logs->NewDataSet->Table1 = [$logs->NewDataSet->Table1];

        }

         */

        //$details = $api->getVirtualMachineDetails($virtualMachineId);

        /*$settings = $api->getSettings($params['serviceid']);

        $shutdown = false;

        $heartbeat = false;

        foreach($settings as $setting){

            if( ($setting->setting == 'shutdown') && ($setting->value == 1) ){

                $shutdown = true;

            } 

            if( ($setting->setting == 'heartbeat') && ($setting->value == 1) ){

                 $heartbeat = true;

            }



        }

        $ipAddresses = $api->getVirtualMachineIpAddresses($virtualMachineId);

        $package = $api->getPackage($info->PackageId);

        $console = $api->getVirtualMachineGuacamoleUrl($virtualMachineId);

        $externalNetwork = $api->getVirtualMachineIpAddresses($virtualMachineId);

        $privateNetwork = $api->getVirtualMachinePrivateIpAddresses($virtualMachineId);

        $dvd = $api->getDVD($virtualMachineId);

        $isos = $api->getIsoLibrary($virtualMachineId);

        $isoOptions = "<option value='{$isos->LibraryItem->Path}'>{$isos->LibraryItem->Name}</option>";

        $operatingSystems = $api->getOperatingSystemTemplates($info->PackageId);

        $operatingSystemsHtml = '';

        foreach($operatingSystems as $operatingSystem){

            $operatingSystems .= "<option value='{$operatingSystem->Path}'>{$operatingSystem->Name}</option>";

        }



        $snapshots = $api->getVirtualMachineSnapshots($virtualMachineId);

        if( $snapshots->VirtualMachineSnapshot  && !is_array($snapshots->VirtualMachineSnapshot) ){

            $snapshots->VirtualMachineSnapshot = [$snapshots->VirtualMachineSnapshot];

        }

        $dateFormat = \WHMCS\Database\Capsule::table('tblconfiguration')->where('setting','DateFormat')->first();

        switch($dateFormat->value){

            case 'DD/MM/YYYY':

                $dateFormat = 'd/m/Y';

                break;

            case 'DD.MM.YYYY':

                $dateFormat = 'd.m.Y';

                break;

            case 'DD-MM-YYYY':

                $dateFormat = 'd-m-Y';

                break;

            case 'MM/DD/YYYY':

                $dateFormat = 'm/d/Y';

                break;

            case 'YYYY/MM/DD':

                $dateFormat = 'Y/m/d';

                break;

            case 'YYYY-MM-DD':

                $dateFormat = 'Y-m-d';

                break;

        }

        foreach($snapshots->VirtualMachineSnapshot as $k => $snapshot){

            $snapshots->VirtualMachineSnapshot[$k]->Created = date($dateFormat . ' H:i:s', strtotime($snapshot->Created));

        }



        $snapshotsUsed = isset($snapshots->VirtualMachineSnapshot)?count($snapshots->VirtualMachineSnapshot):0;

        $snapshotsUsage = ( $snapshotsUsed / $info->SnapshotsNumber ) * 100;

        $snapshotsAvailable = $info->SnapshotsNumber - $snapshotsUsed;*/



    }catch( \SoapFault $e){

        // Record the error in WHMCS's module log.

         logModuleCall(

             'solidcp',

             __FUNCTION__,

             $params,

             $e->getMessage()

         );  

         

         // In an error condition, display an error page.

         return array(

             'tabOverviewReplacementTemplate' => 'error.tpl',

             'templateVariables' => array(

                 'usefulErrorHelper' => $e->getMessage(),

             ),  

         );  

    }



    if ($requestedAction == 'manage') {

        $serviceAction = 'get_usage';

        $templateFile = 'templates/manage.tpl';

    } else {

        $serviceAction = 'get_stats';

        $templateFile = 'templates/overview.tpl';

    }



    try {

        /*$diskUsage = ( $details->HddLogicalDisks->LogicalDisk->Size - $details->HddLogicalDisks->LogicalDisk->FreeSpace) / $details->HddLogicalDisks->LogicalDisk->Size * 100;

        $diskUsed = ( $details->HddLogicalDisks->LogicalDisk->Size - $details->HddLogicalDisks->LogicalDisk->FreeSpace);



        //$diskUsage = round( $info->HddSize - $details->HddLogicalDisks->LogicalDisk->FreeSpace );

        $packageParts = explode('-', $package->PackageName);

        $countryIso = isset($packageParts[0])?$packageParts[0]:'US';

        if (strlen($countryIso) > 2) {

            $countryIso = substr($countryIso, 0, 2);

        }

        $countriesName = [

            'CA' => 'Canada',

            'DE' => 'Germany',

            'FI' => 'Finland',

            'FR' => 'France',

            'GB' => 'United Kingdom',

            'UK' => 'United Kingdom',

            'US' => 'United States of America'

        ];





        $invoiceItem = Capsule::table("tblinvoiceitems")->where("relid",$params["serviceid"])->orderBy("id","DESC")->first();

        if( $invoiceItem ){

            $invoice = Capsule::table("tblinvoices")->where("id",$invoiceItem->invoiceid)->where("status","Unpaid")->first();

            if($invoice){

                $invoiceDueDate = $invoice->duedate;

            }else{

                $invoiceDueDate = '';

            }

        }else{

            $invoiceDueDate = '';

        }

        $contacts = Capsule::table('tblcontacts')->where('userid',$params['clientsdetails']['userid'])->get();

        $uuidArray = explode('.',$info->Name);

        $uuid = array_pop($uuidArray);

        $hostname = implode('.',$uuidArray);*/

       

        return array(

            'tabOverviewReplacementTemplate' => $templateFile,

            'templateVariables' => array(

                'info' => $info,

                'details' => $details,

                'ipaddresses' => $ipAddresses,

                'diskUsage' => $diskUsage,

                'diskUsed' => $diskUsed,

                'countryIso' => $countryIso,

                'countryName' => $packageParts[0],

                'lang' => $lang,

                'package' => $package,

                'hostnameUpdateSuccess' => $hostnameUpdateSuccess,

                'passwordUpdateSuccess' => $passwordUpdateSuccess,

                "serverReinstallSuccess" => $serverReinstallSuccess,

                "allowStart" => $params['configoption16'],

                "servicePassword" => $servicePassword,

                "console" => $console,

                "rdpfw" => $rdpfw,

                "rdpff" => $rdpff,

                "rdpbw" => $rdpbw,

                "rdpbf" => $rdpbf,

                "rdpblw" => $rdpblw,

                "rdpblf" => $rdpblf,

                "externalNetwork" => $externalNetwork,

                "privateNetwork" => $privateNetwork,

                "snapshotsUsed" => $snapshotsUsed,

                "snapshots" => $snapshots,

                "successMessage" => $successMessage,

                "isos" => $isoOptions,

                "dvd" => $dvd,

                "operatingSystems" => $operatingSystems,

                //"logs" => $logs->NewDataSet->Table1,

                "actionLogs" => $actionLogs,

                'notificationSettings' => $settings,

                "invoice" => $invoice,

                'shutdownSetting' => $shutdown,

                'heartbeatSetting' => $heartbeat,

                'deployed' => true,

                'contacts' => $contacts,

                'uuid' => $uuid,

                'hostname' => $hostname

            ),

        );

    } catch (Exception $e) {

        // Record the error in WHMCS's module log.

        logModuleCall(

            'solidcp',

            __FUNCTION__,

            $params,

            $e->getMessage()

        );



        // In an error condition, display an error page.

        return array(

            'tabOverviewReplacementTemplate' => 'error.tpl',

            'templateVariables' => array(

                'usefulErrorHelper' => $e->getMessage(),

            ),

        );

    }

}



function solidcp_generatePassword($min_length = 20, $max_length = 25)

{

    $pool = 'abcdefghjkmnopqrstuvwxyzABCDEFGHJKMNOPQRSTUVWXYZ0123456789!@#$%^&()';

    $pool_size = strlen($pool);

    $length = mt_rand(max($min_length, 5), min($max_length, 25));

    $password = '';

    for ($i = 0; $i < $length; $i++) {

        $password .= substr($pool, mt_rand(0, $pool_size - 1), 1);

    }

    return $password;

}





function solidcp_AdminServicesTabFieldsSave(array $params){

    try{

        $virtualMachineId = $params['customfields']['vm_id'];

        $hostnameArray = explode('.',$params['customfields']['hostname']);

        $hostname = $hostnameArray[0] .  "." . $params['serviceid'];

        $hostnameParams = [

            'itemId' => $virtualMachineId ,

            'hostname' => $params['customfields']['hostname']

        ];

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        //$response = $api->updateVirtualMachineHostname($hostnameParams); // Prevent reboot on save

    }catch(Exception $e){



    }

}



function solidcp_AdminServicesTabFields(array $params)

{

    try {

        require_once 'lang/en.php';

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];

        $success = false;

        $error = false;

        if( isset($_GET["command"])){

            switch($_GET["command"]){

                case "external-delete-ip":

                    $addresses = json_decode( base64_decode( $_GET["address-id"] ) );

                    $response = $api->deleteIpAddress($virtualMachineId,$addresses);

                    if($response->IsSuccess){

                        $success = true;

                    }else{

                        $error = $response->error;

                    }

                    break;

                case "external-add-ip":

                    $response = $api->addIpAddress($virtualMachineId,[],true,$_GET["amount"]);

                    if($response->IsSuccess){

                        $success = true;

                    }else{

                        $error = $response->error;

                    }



                    break;

                case "external-primary-ip":

                    $response = $api->setPrimaryAddress($virtualMachineId,$_GET["address-id"]);

                    $network = $api->getVirtualMachineIpAddresses($virtualMachineId);

                    if($response->IsSuccess){

                        $success = true;

                        foreach($network as $address){

                            if( $address->AddressId == $_GET["address-id"] ){

                                Capsule::table("tblhosting")->where("id",$params["serviceid"])->update( ["dedicatedip" => $address->IPAddress ] );

                            }

                        }

                    }else{

                        $error = $response->error;

                    }



                    break;

                case "internal-delete-ip":

                    $addresses = json_decode( base64_decode( $_GET["address-id"] ) );

                    $response = $api->deleteIpAddress($virtualMachineId,$addresses,false);

                    if($response->IsSuccess){

                        $success = true;

                    }else{

                        $error = $response->error;

                    }

                    break;

                case "internal-add-ip":

                     $response = $api->addIpAddress($virtualMachineId,false,false,$_GET["amount"]);

                     if($response->IsSuccess){

                         $success = true;

                     }else{

                         $error = $response->error;

                     }



                     break;

                 case "internal-primary-ip":

                     $response = $api->setPrimaryAddress($virtualMachineId,$_GET["address-id"],false);

                     if($response->IsSuccess){

                         $success = true;

                     }else{

                         $error = $response->error;

                     }



                     break;

                case "toggle-boot":

                    $info = $api->getVirtualMachine($virtualMachineId);

                    $response = $api->toggleBootFromCd($virtualMachineId,!$info->BootFromCD);

                    if($response->IsSuccess){

                        $success = true;

                    }else{

                        $error = $response->error;

                    }

                    break;

                case "mount-iso":

                    $info = $api->getVirtualMachine($virtualMachineId);

                    $response = $api->insertDVD($virtualMachineId,$_GET["iso-path"]);

                    if($response->IsSuccess){

                        $success = true;

                    }else{

                        $error = $response->error;

                    }

                    break;

                case "unmount-iso":

                    $info = $api->getVirtualMachine($virtualMachineId);

                    $response = $api->ejectDVD($virtualMachineId);

                    if($response->IsSuccess){

                        $success = true;

                    }else{

                        $error = $response->error;

                    }

                    sleep(5);

                    break;

                case "reset-password":

                    $password = solidcp_generatePassword();

                    $passwordParams = [

                        'itemId' => $virtualMachineId ,

                        'password' => $password

                    ];

                    $response = $api->updateVirtualMachinePassword($passwordParams);

                    if( $response->IsSuccess ){

                        $command = 'EncryptPassword';

                        $postData = array(

                            'password2' => $password,

                        );

                        $results = localAPI($command, $postData);

                        Capsule::Table('tblhosting')->where('id',$params['serviceid'])->update(['password' => $results['password']]);

                        $success = true;

                    }else{

                        $error = $response->error;

                    }



                    break;

                case "reinstall":

                    $info = $api->getVirtualMachine($virtualMachineId);

                    $hostnameArray = explode('.',$params['customfields']['hostname']);

                    $hostname = $hostnameArray[0] .  "." . $params['serviceid'];

                    $reinstallParams = [

                       'itemId' => $virtualMachineId,

                       'hostname' => $hostname,

                       'template' => $_GET["os"]

                    ];

                    //$response = $api->reinstallVirtualMachine( $reinstallParams );

                    $response = $api->reinstallVirtualMachineNew( $params, $virtualMachineId, $_GET["os"] );

                    if( $response->IsSuccess ){

                        $vmId = $response->Value;

                        $row = Capsule::table('tblcustomfields')->where('fieldname','like', 'vm_id%')->where('relid', $params['pid'])->first();

                        Capsule::table('tblcustomfieldsvalues')->where('fieldid', $row->id)->where('relid', $params['serviceid'])->update(['value' => $vmId]);

                        $virtualMachineId = $vmId;

                        $command = 'EncryptPassword';

                        $postData = array(

                        'password2' => $response->AdministratorPassword,

                        );

                        $results = localAPI($command, $postData);

                        Capsule::Table('tblhosting')->where('id',$params['serviceid'])->update(['password' => $results["password"]]);

                        $row = Capsule::table('tblcustomfields')->where('fieldname','like', 'hostname%')->where('relid', $params['pid'])->first();

                        $success = true;

                        sleep(20);

                    }else{

                        $error = $response->error;

                    }

                    break;

                case "console":

                    $response = $api->getVirtualMachineGuacamoleUrl($virtualMachineId);

                    $console = false;

                    $success = true;

                    $console = $response->response;

                    break;

                case "enable-internal":

                    $p= [

                        'privateNetworkEnabled' => true,

                    ];

                    $response = $api->updateVirtualMachineConfiguration($virtualMachineId,$p);

                    if( $response->IsSuccess ){

                        $success = true;

                    }else{

                        $error = $response->error;

                    }

                    break;

                case "enable-external":

                    $p = [

                        'externalNetworkEnabled' => true,

                    ];

                    $response = $api->updateVirtualMachineConfiguration($virtualMachineId,$p);

                    if( $response->IsSuccess ){

                        $success = true;

                    }else{

                        $error = $response->error;

                    }

                    break;

                case "disable-private":

                    $p = [

                        'privateNetworkEnabled' => false,

                    ];

                    $response = $api->updateVirtualMachineConfiguration($virtualMachineId,$p);

                    if( $response->IsSuccess ){

                        $success = true;

                    }else{

                        $error = $response->error;

                    }

                    break;

                case "restore-external":

                     $response = $api->restoreVirtualMachineExternal($virtualMachineId);

                     if( $response->IsSuccess ){

                         $success = true;

                     }else{

                         $error = $response->error;

                     }



                    break;

                case "restore-private":

                     $response = $api->restoreVirtualMachinePrivate($virtualMachineId);

                     if( $response->IsSuccess ){

                         $success = true;

                     }else{

                         $error = $response->error;

                     }

                    break;





                case "disable-external":

                    $p = [

                        'externalNetworkEnabled' => false,

                    ];

                    $response = $api->updateVirtualMachineConfiguration($virtualMachineId,$p);

                    if( $response->IsSuccess ){

                        $success = true;

                    }else{

                        $error = $response->error;

                    }

                    break;

                case "update-settings":

                    $data = json_decode(base64_decode($_GET["data"]));

                    $p = [

                        'CpuCores' => $data->cpus,

                        'ramMB' => $data->ram,

                        'hddGB' => $data->disk,

                        'snapshots' => $params["configoption5"],

                        'hddMinimumIOPS' => $data->diskminiops,

                        'hddMaximumIOPS' => $data->diskmaxiops,

                        'dvdInstalled' => $params["configoption15"]?1:0,

                        'numLock' => $params["configoption17"]?1:0,

                        'startShutdownAllowed' => $params["configoption16"]?1:0,

                        'pauseResumeAllowed' => $params["configoption20"]?1:0,

                        'rebootAllowed' => $params["configoption21"]?1:0,

                        'resetAllowed' => $params["configoption22"]?1:0,

                        'reinstallAllowed' => $params["configoption18"]?1:0,

                        'externalNetworkEnabled' => $params["configoption13"]?1:0,

                        'privateNetworkEnabled' => $params["configoption14"]?1:0,

                        'bootFromCD' => $params["configoption16"]?1:0,

                        'DynamicMemoryMinimum' => $data->mindram,

                        'DynamicMemoryMaximum' => $data->maxdram,

                        'DynamicBuffer' => $data->bram,

                        'DynamicPriority' => $data->pram,

                     ];

                    $response = $api->updateVirtualMachineConfiguration($virtualMachineId,$p);

                    if( $response->IsSuccess ){

                        $success = true;

                    }else{

                        $error = $response->error;

                    }

                    break;



            }

        }

        if($params['status'] == 'Active'){

            $info = $api->getVirtualMachine($virtualMachineId);

            $dvd = $api->getDVD($virtualMachineId);

            $isos = $api->getIsoLibrary($virtualMachineId);

            $isoOptions = "<option value='{$isos->LibraryItem->Path}'>{$isos->LibraryItem->Name}</option>";

            $mountStatus = !$dvd->Name?"None":$dvd->Name;

            $bootFromCD = $info->BootFromCD?'Yes':'NO';

            $snapshots = $api->getVirtualMachineSnapshots($virtualMachineId);

            $snapshotsUsed = isset($snapshots->VirtualMachineSnapshot)?count($snapshots->VirtualMachineSnapshot):0;

            $details = $api->getVirtualMachineDetails($virtualMachineId);

            $cpuFree = 100 - $details->CpuUsage;

            $snapshotsUsage = ( $snapshotsUsed / $info->SnapshotsNumber ) * 100;

            $snapshotsAvailable = $info->SnapshotsNumber - $snapshotsUsed;

            $network = $api->getVirtualMachineIpAddresses($virtualMachineId);

            $privateNetwork = $api->getVirtualMachinePrivateIpAddresses($virtualMachineId);

            $space = $api->getPackage($info->PackageId);

            switch( $details->Heartbeat ){

                case 'Ok':

                    $status = $lang["Solidcp.tab_client_actions.heartbeat_{$details->Heartbeat}"];

                    break;

                case 'NoContact':

                    $status = $lang["Solidcp.tab_client_actions.heartbeat_{$details->Heartbeat}"];

                    break;

                default:

                    //$status = $lang["Solidcp.tab_client_actions.heartbeat_{$details->Heartbeat}"];

                    $status = "None";

                    break;

            }





            // Javascript code

            $html = "";



            if( $details->State == "Running" ){

                $statusColor = "green";

            }else{

                $statusColor = "red";

            }



            if($details->Heartbeat == 'Ok'){

               $heartbeatColor = "green";

            }elseif($details->Heartbeat == 'NoContact'){

                $heartbeatColor = "red";

            }elseif($details->Heartbeat == 'None'){

                $heartbeatColor = "red";

            }else{

                $heartbeatColor = "yellow";

            }

            $details->Uptime = round( $details->Uptime / 1000);

            $dtF = new \DateTime('@0');

            $dtT = new \DateTime("@$details->Uptime");

            $uptime =  $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');

            $ramUsage = round(($details->RamUsage / $details->RamSize) * 100);

            $ramFree = $details->RamSize - $details->RamUsage;



            $diskUsage = ( $details->HddLogicalDisks->LogicalDisk->Size - $details->HddLogicalDisks->LogicalDisk->FreeSpace) / $details->HddLogicalDisks->LogicalDisk->Size * 100;

            $diskU = ( $details->HddLogicalDisks->LogicalDisk->Size - $details->HddLogicalDisks->LogicalDisk->FreeSpace);

            

            $statusHtml = "

            <div>

                <div class='row col-md-12'>

                    <div>

                        <label class='col-md-2'>Status</label>

                    </div>

                    <div> 

                        <span style='color:{$statusColor}'>{$details->State}</span>

                    </div>

                </div>

                <div class='row col-md-12'>

                    <div>

                        <label class='col-md-2'>Heartbeat</label>

                    </div>

                    <div>

                        <span style='color:{$heartbeatColor}'>{$details->Heartbeat}</span>

                    </div>

                </div>

                <div class='row col-md-12'>

                    <div>

                        <label class='col-md-2'>Uptime</label>

                    </div>

                    <div>

                        <span>{$uptime}</span>

                    </div>

                </div>

                <div class='row col-md-12'>

                    <div>

                        <label class='col-md-2'>Creation time</label>

                    </div>

                    <div>

                        <span>{$details->CreationTime}</span>

                    </div>

                </div>

                <div class='row col-md-12'>

                    <div>

                        <label class='col-md-2'>Node</label>

                    </div>

                    <div>

                        <span>{$space->PackageName}</span>&nbsp;

                        <a href='https://{$params['serverhostname']}/Default.aspx?pid=SpaceVPS2012&SpaceID={$space->PackageId}' class='btn btn-success' target='_blank'>Manage</a>

                    </div>

                </div>

                <div class='row col-md-12'>

                    <div>

                        <label class='col-md-2'>Hostname</label>

                    </div>

                    <div>

                        <span>{$details->Name}</span>&nbsp;

                        <a href='https://{$params['serverhostname']}/Default.aspx?pid=SpaceVPS2012&mid=219&ctl=vps_general&ItemID={$virtualMachineId}&SpaceID={$space->PackageId}' class='btn btn-success' target='_blank'>Manage</a>

                    </div>

                </div>

          

                <div class='row col-md-12'>

                    <div class='col-md-2'>

                        <label>Cpu Usage</label>

                    </div>&nbsp;

                    <div class='col-md-5'>

                        <div class='progress' style='margin-left:-15px;'>

                            <div class='progress-bar' role='progressbar' style='width: {$details->CpuUsage}%' aria-valuenow='{$info->CpuCores}' aria-valuemin='0' aria-valuemax='100'></div>

                        </div>

                    </div>

                    <div class='col-md-4'>

                        <span>{$details->CpuUsage}% used on {$info->CpuCores} cores </span>

                    </div>

                </div>

                <div class='row col-md-12'>

                    <div class='col-md-2'>

                        <label>RAM</label>

                    </div>&nbsp;

                    <div class='col-md-5'>

                        <div class='progress' style='margin-left:-15px;'>

                            <div class='progress-bar' role='progressbar' style='width: {$ramUsage}%' aria-valuenow='{$ramUsage}' aria-valuemin='0' aria-valuemax='100'></div>

                        </div>

                    </div>

                    <div class='col-md-4'>

                        <span>{$details->RamUsage} GB of {$details->RamSize} GB Used / {$ramFree} GB Free</span>

                    </div>

                </div>



                <div class='row col-md-12'>

                    <div class='col-md-2'>

                        <label>Disk usage</label>

                    </div>&nbsp;

                    <div class='col-md-5'>

                        <div class='progress' style='margin-left:-15px;'>

                            <div class='progress-bar' role='progressbar' style='width: {$diskUsage}%' aria-valuenow='{$diskUsage}' aria-valuemin='0' aria-valuemax='100'></div>

                        </div>

                    </div>

                    <div class='col-md-4'>

                        <span>{$diskU} GB of {$details->HddLogicalDisks->LogicalDisk->Size} GB Used / {$details->HddLogicalDisks->LogicalDisk->FreeSpace} GB Free</span>

                    </div>

                </div>

                <div class='row col-md-12'>

                    <div>

                        <label class='col-md-2'>Max dynamic memory</label>

                    </div>

                    <div>

                        <span>{$details->DynamicMemory->Maximum}</span>

                    </div>

                </div>

                <div class='row col-md-12'>

                     <div>

                         <label class='col-md-2'>Min dynamic memory</label>

                     </div>

                     <div>

                         <span>{$details->DynamicMemory->Minimum}</span>

                     </div>

                 </div>

                <div class='row col-md-12'>

                     <div>

                         <label class='col-md-2'>Buffer dynamic memory</label>

                     </div>

                     <div>

                         <span>{$details->DynamicMemory->Buffer}</span>

                     </div>

                 </div>

                 <div class='row col-md-12'>

                     <div>

                        <label class='col-md-2'>Priority dynamic memory</label>

                     </div>

                     <div>

                         <span>{$details->DynamicMemory->Priority}</span>

                     </div>

                 </div>

                 <div class='row col-md-12'>

                     <div>

                         <label class='col-md-2'>Disk Minimum IOPS</label>

                     </div>

                     <div>

                         <span>{$details->HddMinimumIOPS}</span>

                     </div>

                 </div>

                 <div class='row col-md-12'>

                     <div>

                        <label class='col-md-2'>Disk MaximumIOPS</label>

                     </div>

                     <div>

                         <span>{$details->HddMaximumIOPS}</span>

                     </div>

                 </div>



                 <div class='row col-md-12'>

                     <div>

                        <label class='col-md-2'>Root Folder</label>

                    </div>

                     <div>

                         <span>{$info->RootFolderPath}</span>

                     </div>

                 </div>

                 <div class='row col-md-12'>

                    <div class='col-md-2'>

                        <label>Snapshots usage</label>

                    </div>&nbsp;

                    <div class='col-md-5'>

                        <div class='progress' style='margin-left:-15px;'>

                            <div class='progress-bar' role='progressbar' style='width: {$snapshotsUsage}%' aria-valuenow='{$snapshotsUsage}' aria-valuemin='0' aria-valuemax='100'></div>

                        </div>

                    </div>

                    <div class='col-md-4'>

                        <span>{$snapshotsUsed}  of {$info->SnapshotsNumber}  Used / {$snapshotsAvailable}  Free</span>

                    </div>

                </div>

                <div class='row col-md-12'>

                    <div>

                       <label class='col-md-2'>Boot From CD</label>

                   </div>

                    <div>

                        <span>{$bootFromCD}</span>

                        <a href='clientsservices.php?command=toggle-boot&userid=" . $params["userid"] . "&id=" . $params["serviceid"] . "' class='btn btn-success'>Toggle</a>

                    </div>

                </div>

                <div class='row col-md-12'>

                     <div>

                        <label class='col-md-2'>Current CD</label>

                    </div>

                     <div>

                         <span>{$mountStatus}</span>

                     </div>

                 </div>



                <div class='row col-md-12'>

                    <div>

                       <label class='col-md-2'>ISO</label>

                   </div>

                    <div>

                        <a href='#' id='mount-iso-link' class='btn-link'>Mount ISO</a> | <a href='clientsservices.php?command=unmount-iso&userid=" . $params["userid"] . "&id=" . $params["serviceid"] . "' class='btn-link'>Unmount ISO</a>

                    </div>

                </div>







            </div>

            ";

            $externalNetworkHtml = ""; 

            if( $info->ExternalNetworkEnabled ){

            $externalNetworkHtml = "<table class='table'><tr><th><input type='checkbox' id='all-external'></th><th>Ip Address</th><th>Subnet Mask</th><th>Gateway</th><th>Primary</th></tr>";

            $totalExternalIps = count($network);

            foreach( $network as $interface ){

                $primary = ($interface->IsPrimary)?"Yes":"No";

                $externalNetworkHtml .= "

                 <tr>

                ";

                if(!$interface->IsPrimary){

                    $externalNetworkHtml .= "<tr><td><input type='checkbox' name='external-ip-id[]'  value='{$interface->AddressId}'> </td>";

                }else{

                     $externalNetworkHtml .= "<td></td>";

                }

                $externalNetworkHtml .= "

                     <td>{$interface->IPAddress}</td>

                     <td>{$interface->SubnetMask}</td>

                     <td>{$interface->DefaultGateway}</td>

                     <td>{$primary}</td>

                 </tr>

               "; 

            }

            //href='clientsservices.php?command=external-delete-ip&address-id=&userid={$params['userid']}&id={$params['serviceid']}'

            $externalNetworkHtml .= "

            </table>

            <div class='row col-md-12'>

                <div class='col-md-10'>

                    <input type='number' min='1' value='1' id='external-ip-amount' style='width:35px;'> <a class='btn btn-info' href='#' id='add-external-ip'>Add IP</a>

                    <a class='btn btn-success' href='#' id='set-external-primary-ip'>Set as Primary</a>

                    <a class='btn btn-danger' href='#' id='delete-external-ip'>Delete</a>

                    <a class='btn btn-danger' id='disable-external-network' >Disable</a>

                    <a class='btn btn-danger' id='restore-external-network'  href='clientsservices.php?userid=" . $params['userid'] . '&id=' . $params['serviceid'] . "&command=restore-external'> Restore</a>

                </div>

                <div>

                    <strong>{$totalExternalIps} IP addresses</strong>

                </div>

            </div>

            ";

            }

            $privateNetworkHtml = '';

            if( $info->PrivateNetworkEnabled ){

            $privateNetworkHtml = "<table class='table'><tr><th><input type='checkbox' id='all-private'></th><th>Ip Address</th><th>Subnet Mask</th><th>Gateway</th><th>Primary</th></tr>";

            $totalPrivateIps = count($privateNetwork);

            foreach( $privateNetwork as $interface ){

                 $primary = ($interface->IsPrimary)?"Yes":"No";

                 if(!$interface->IsPrimary){

                    $privateNetworkHtml .= "<tr><td><input type='checkbox' name='internal-ip-id[]'  value='{$interface->AddressId}'> </td>";

                 }else{

                    $privateNetworkHtml .= "<td></td>";

                 }

                 $privateNetworkHtml .= "

                      <td>{$interface->IPAddress}</td>

                      <td>{$interface->SubnetMask}</td>

                      <td>{$interface->DefaultGateway}</td>

                      <td>{$primary}</td>

                  </tr>

                ";

             }

             $privateNetworkHtml .= "

             </table>

             <div class='row col-md-12'>

                 <div class='col-md-10'>

                 <input type='number' min='1' value='1' id='internal-ip-amount' style='width:35px;'>

                 <a class='btn btn-info' href='#' id='add-internal-ip'>Add IP</a>

                 <a class='btn btn-success' href='#' id='set-internal-primary-ip'>Set as Primary</a>

                 <a class='btn btn-danger' href='#' id='delete-internal-ip'>Delete</a>

                 <a class='btn btn-danger' id='disable-private-network' >Disable</a>

                 <a class='btn btn-danger' id='restore-private-network' href='clientsservices.php?userid=" . $params['userid'] . '&id=' . $params['serviceid'] . "&command=restore-private'>Restore</a>

                 </div>

                 <div>

                     <strong>{$totalPrivateIps} IP addresses</strong>

                 </div>

             </div>";

            }

            $operatingSystems = $api->getOperatingSystemTemplates($info->PackageId);

            $operatingSystemsHtml = '';

            foreach($operatingSystems as $operatingSystem){

                $operatingSystemsHtml .= "<option value='{$operatingSystem->Path}'>{$operatingSystem->Name}</option>";

            }

            $html .= '

                 <!-- Modal Reinstall -->

                 <div class="modal fade" id="modalModuleReinstall" role="dialog" aria-labelledby="ModuleCreateLabel" aria-hidden="true" style="display: none;">

                     <div class="modal-dialog">

                        <div class="modal-content panel panel-primary">

                            <div id="modalModuleCreateHeading" class="modal-header panel-heading">

                                <button type="button" class="close" data-dismiss="modal">

                                <span aria-hidden="true">×</span>

                                <span class="sr-only">Close</span>

                                </button>

                                <h4 class="modal-title" id="ModuleCreateLabel">Confirm Module Command</h4>

                            </div>

                           <div id="modalModuleCreateBody" class="modal-body panel-body">

                               Are you sure you want to run the reinstall function?

                               <hr>

                               <label>Operating System</label>

                               <select id="operating-system-option" class="form-control">

                                   ' . $operatingSystemsHtml . '

                               </select>

                          </div>

                          <div id="modalModuleCreateFooter" class="modal-footer panel-footer">

                              <button type="button" id="ModuleReinstall-Yes" class="btn btn-primary">

                                  Yes

                             </button><button type="button" id="ModuleCreate-No" class="btn btn-default" data-dismiss="modal">

                                 No

                             </button>

                          </div>

                     </div>

                 </div>

               </div>

               <!-- Modal iso -->

               <div class="modal fade" id="modalIso" role="dialog" aria-labelledby="ModuleCreateLabel" aria-hidden="true" style="display: none;">

                     <div class="modal-dialog">

                        <div class="modal-content panel panel-primary">

                            <div id="modalIsoHeading" class="modal-header panel-heading">

                                <button type="button" class="close" data-dismiss="modal">

                                 <span aria-hidden="true">×</span>

                                <span class="sr-only">Close</span>

                                </button>

                                <h4 class="modal-title" id="ModuleIsoLabel">Confirm Module Command</h4>

                            </div>

                           <div id="modalModuleCreateBody" class="modal-body panel-body">

                               Mount ISO

                               <hr>

                               <label>ISO</label> 

                               <select id="iso-path" class="form-control">

                                   ' . $isoOptions . '

                               </select>

                          </div>

                          <div id="modalModuleISOFooter" class="modal-footer panel-footer">

                              <a id="ModuleIso-Yes" class="btn btn-primary" href="#">

                                  Mount

                             </a>

                             <button type="button" id="ModuleCreate-No" class="btn btn-default" data-dismiss="modal">

                                 Cancel

                             </button>

                          </div>

                     </div>

                 </div>

               </div>

               <!-- Modal reset password -->

              <div class="modal fade" id="modalModuleResetPassword" role="dialog" aria-labelledby="ModuleCreateLabel" aria-hidden="true" style="display: none;">

                     <div class="modal-dialog">

                        <div class="modal-content panel panel-primary">

                            <div id="modalModuleCreateHeading" class="modal-header panel-heading">

                                <button type="button" class="close" data-dismiss="modal">

                                <span aria-hidden="true">×</span>

                                <span class="sr-only">Close</span>

                                </button>

                                <h4 class="modal-title" id="ModuleCreateLabel">Confirm Module Command</h4>

                            </div>

                           <div id="modalModuleCreateBody" class="modal-body panel-body">

                               Are you sure you want to run the reset password function?

                          </div>

                          <div id="modalModuleCreateFooter" class="modal-footer panel-footer">

                              <button type="button" id="ModuleReset-Yes" class="btn btn-primary" onclick="#">

                                  Yes

                             </button><button type="button" id="ModuleCreate-No" class="btn btn-default" data-dismiss="modal">

                                 No

                             </button>

                          </div>

                     </div>

                 </div>

               </div>

               <!-- Modal disable external network -->

               <div class="modal fade" id="modalModuleExternalDisable" role="dialog" aria-labelledby="ModuleCreateLabel" aria-hidden="true" style="display: none;">

                     <div class="modal-dialog">

                        <div class="modal-content panel panel-primary">

                            <div id="modalModuleCreateHeading" class="modal-header panel-heading">

                                <button type="button" class="close" data-dismiss="modal">

                                <span aria-hidden="true">×</span>

                                <span class="sr-only">Close</span>

                                </button>

                                <h4 class="modal-title" id="ModuleCreateLabel">Confirm Module Command</h4>

                            </div>

                           <div id="modalModuleCreateBody" class="modal-body panel-body">

                               Are you sure you want disable the external network?

                          </div>

                          <div id="modalModuleCreateFooter" class="modal-footer panel-footer">

                              <a id="ModuleReinstall-Yes" class="btn btn-primary" href="clientsservices.php?userid=' . $params['userid'] . '&id=' . $params['serviceid'] . '&command=disable-external">

                                  Yes

                             </a><button type="button" id="ModuleCreate-No" class="btn btn-default" data-dismiss="modal">

                                 No

                             </button>

                          </div>

                     </div>

                 </div>

               </div>



               <!-- Modal disable internal network -->

               <div class="modal fade" id="modalModulePrivateDisable" role="dialog" aria-labelledby="ModuleCreateLabel" aria-hidden="true" style="display: none;">

                     <div class="modal-dialog">

                        <div class="modal-content panel panel-primary">

                            <div id="modalModuleCreateHeading" class="modal-header panel-heading">

                                <button type="button" class="close" data-dismiss="modal">

                                <span aria-hidden="true">×</span>

                                <span class="sr-only">Close</span>

                                </button>

                                <h4 class="modal-title" id="ModuleCreateLabel">Confirm Module Command</h4>

                            </div>

                           <div id="modalModuleCreateBody" class="modal-body panel-body">

                               Are you sure you want disable the private network?

                          </div>

                          <div id="modalModuleCreateFooter" class="modal-footer panel-footer">

                              <a id="ModuleReinstall-Yes" class="btn btn-primary" href="clientsservices.php?userid=' . $params['userid'] . '&id=' . $params['serviceid'] . '&command=disable-private">

                                  Yes

                             </a><button type="button" id="ModuleCreate-No" class="btn btn-default" data-dismiss="modal">

                                 No

                             </button>

                          </div>

                     </div>

                 </div>

               </div>



               <!-- Modal settings  -->

               <div class="modal fade" id="modalSettings" role="dialog" aria-labelledby="ModuleCreateLabel" aria-hidden="true" style="display: none;">

                     <div class="modal-dialog">

                        <div class="modal-content panel panel-primary">

                            <div id="modalIsoHeading" class="modal-header panel-heading">

                                <button type="button" class="close" data-dismiss="modal">

                                 <span aria-hidden="true">×</span>

                                <span class="sr-only">Close</span>

                                </button>

                                <h4 class="modal-title" id="ModuleIsoLabel">Confirm Module Command</h4>

                            </div>

                           <div id="modalModuleCreateBody" class="modal-body panel-body">

                               Update Settings

                               <hr>

                               <div class="row">

                                   <div class="col-md-6">

                                       <label>Cpu cores</label> 

                                       <input type="number" min="1" name="cpu" class="form-control" value=' . $info->CpuCores .  '>

                                    </div>

                                    <div class="col-md-6">

                                        <label>RAM</label> 

                                        <input type="number" min="512" name="ram" class="form-control" value=' . $details->RamSize .  '>

                                    </div>

                                </div>

                                <div class="row">

                                   <div class="col-md-6">

                                       <label>Min Dynamic Ram</label> 

                                       <input type="number" min="0" name="mindram" class="form-control" value=' . $details->DynamicMemory->Minimum.  '>

                                    </div>

                                    <div class="col-md-6">

                                        <label>Max Dynamic Ram</label> 

                                        <input type="number" min="0" name="maxdram" class="form-control" value=' . $details->DynamicMemory->Maximum.  '>

                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-md-6">

                                        <label>Buffer Dynamic Ram</label> 

                                        <input type="number" min="0" name="bufferram" class="form-control" value=' . $details->DynamicMemory->Buffer.  '>

                                    </div>

                                    <div class="col-md-6">

                                        <label>Priority Dynamic Ram</label> 

                                        <input type="number" min="0" name="priorityram" class="form-control" value=' . $details->DynamicMemory->Priority.  '>

                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-md-6">

                                        <label>Disk Space</label> 

                                        <input type="number" min="10" name="diskspace" class="form-control" value=' . $details->HddLogicalDisks->LogicalDisk->Size .  '>

                                    </div>

                                    <div class="col-md-6">

                                        <label>Disk minimum iops</label> 

                                        <input type="number" min="0" name="diskminiops" class="form-control" value=' . $details->HddMinimumIOPS .  '>

                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-md-6">

                                        <label>Disk maximum iops</label> 

                                        <input type="number" min="0" name="diskmaxiops" class="form-control" value=' . $details->HddMaximumIOPS .  '>

                                    </div>

                                </div>

                          </div>

                          <div id="modalModuleISOFooter" class="modal-footer panel-footer">

                              <a id="ModuleSettings-Yes" class="btn btn-primary" href="#">

                                  Apply

                             </a>

                             <button type="button" id="ModuleCreate-No" class="btn btn-default" data-dismiss="modal">

                                 Cancel

                             </button>

                          </div>

                     </div>

                 </div>

               </div>







               <script>

                   $( document ).ready(function() {

                       $("#delete-external-ip").click(function(e){

                           e.preventDefault();

                           var searchIDs = $("input[name=\"external-ip-id[]\"]:checkbox:checked").map(function(){

                               return $(this).val();

                           }).get();

                           var addresses = btoa(JSON.stringify(searchIDs));

                           var location = "clientsservices.php?command=external-delete-ip&address-id=" + addresses + "&userid=' . $params["userid"] . '&id=' . $params["serviceid"] . '";

                           window.location = location;

                       });

                       $("#add-external-ip").click(function(e){

                           e.preventDefault();

                           var amount = $("#external-ip-amount").val();

                           var location = "clientsservices.php?command=external-add-ip&userid=' . $params["userid"] . '&id=' . $params["serviceid"] . '&amount=" + amount;

                           window.location = location;

                       });

                       $("#set-external-primary-ip").click(function(e){

                            e.preventDefault();

                            var ipAddressId = $("input[name=\"external-ip-id[]\"]:checked").val();

                            var location = "clientsservices.php?command=external-primary-ip&address-id=" + ipAddressId + "&userid=' . $params["userid"] . '&id=' . $params["serviceid"] . '";

                            window.location = location;

                       });

                       $("#delete-internal-ip").click(function(e){

                           e.preventDefault();

                           var searchIDs = $("input[name=\"internal-ip-id[]\"]:checkbox:checked").map(function(){

                               return $(this).val();

                           }).get();

                           var addresses = btoa(JSON.stringify(searchIDs));

                           var location = "clientsservices.php?command=internal-delete-ip&address-id=" + addresses + "&userid=' . $params["userid"] . '&id=' . $params["serviceid"] . '";

                           window.location = location;

                       });

                       $("#add-internal-ip").click(function(e){

                           e.preventDefault();

                           var amount = $("#internal-ip-amount").val();

                           var location = "clientsservices.php?command=internal-add-ip&userid=' . $params["userid"] . '&id=' . $params["serviceid"] .  '&amount=" + amount;

                           window.location = location;

                       });

                       $("#set-internal-primary-ip").click(function(e){

                            e.preventDefault();

                            var ipAddressId = $("input[name=\"internal-ip-id[]\"]:checked").val();

                            var location = "clientsservices.php?command=internal-primary-ip&address-id=" + ipAddressId + "&userid=' . $params["userid"] . '&id=' . $params["serviceid"] . '";

                            window.location = location;

                       });

                       $("#mount-iso-link").click(function(e){

                           e.preventDefault();

                           $("#modalIso").modal("show");

                       });

                       $("#ModuleIso-Yes").click(function(e){

                           e.preventDefault();

                           var isoPath = $("#iso-path").val();

                           var url = "clientsservices.php?command=mount-iso&userid=' . $params["userid"] . '&id=' . $params["serviceid"] . '&iso-path=" + isoPath;

                           window.location = url;

                       });

                       $("#ModuleReset-Yes").click(function(e){

                           e.preventDefault();

                           var location = "clientsservices.php?command=reset-password&userid=' . $params["userid"] . '&id=' . $params["serviceid"] . '";

                           window.location = location;

                       });

                       $("#reset-password-button").click(function(e){

                            e.preventDefault();

                            $("#modalModuleResetPassword").modal("show");

                       });

                       $("#reinstall-button").click(function(e){

                            e.preventDefault();

                            $("#modalModuleReinstall").modal("show");

                       });

                       $("#ModuleReinstall-Yes").click(function(e){

                           e.preventDefault();

                           var os = $("#operating-system-option").val();

                           var url = "clientsservices.php?command=reinstall&userid=' . $params["userid"] . '&id=' . $params["serviceid"] . '&os=" + os;

                           window.location = url;

                       });

                       $("#console-button").click(function(e){

                           e.preventDefault();

                           var location = "clientsservices.php?command=console&userid=' . $params["userid"] . '&id=' . $params["serviceid"] . '";

                           window.location = location;

                       });

                       $("#all-external").click(function(e){

                           var checkBoxes = $("input[name=\"external-ip-id[]\"]");

                           checkBoxes.prop("checked", $(this).prop("checked"));

                       });

                       $("#all-private").click(function(e){

                          var checkBoxes = $("input[name=\"internal-ip-id[]\"]");

                          checkBoxes.prop("checked", $(this).prop("checked"));

                       });

                       $("#disable-external-network").click(function(e){

                        e.preventDefault();

                           $("#modalModuleExternalDisable").modal("show");

                       });

                       $("#disable-private-network").click(function(e){

                        e.preventDefault();

                           $("#modalModulePrivateDisable").modal("show");

                       });

                       $("#settings-button").click(function(e){

                            e.preventDefault();

                            $("#modalSettings").modal("show");

                       });

                       $("#ModuleSettings-Yes").click(function(e){

                           e.preventDefault();

                           var settings = {};

                           settings.cpus = $( "input[name=\'cpu\']" ).val();

                           settings.ram = $( "input[name=\'ram\']" ).val();

                           settings.mindram = $( "input[name=\'mindram\']" ).val();

                           settings.maxdram = $( "input[name=\'maxdram\']" ).val();

                           settings.bram = $( "input[name=\'bufferram\']" ).val();

                           settings.pram = $( "input[name=\'priorityram\']" ).val();

                           settings.disk = $( "input[name=\'diskspace\']" ).val();

                           settings.diskminiops = $( "input[name=\'diskminiops\']" ).val();

                           settings.diskmaxiops = $( "input[name=\'diskmaxiops\']" ).val();



                           var data = btoa(JSON.stringify(settings));

                           var location = "clientsservices.php?command=update-settings&userid=' . $params["userid"] . '&id=' . $params["serviceid"] . '&data=" + data;

                           window.location = location;

                       });



 



                   });

               </script>

            ';

            if($success){

                 $html .= "<div class='alert alert-success'>Action completed successfully</div>";

            }

            if($error){

                $html .= "<div class='alert alert-danger'>{$error}</div>";

            }

            if($console){

                $html .= "<div class='alert alert-warning'>Click <a href='{$console}' target='blank_'>HERE</a> to access the console</div>";

            }

            if( ($details->State == "Running") && ($details->Heartbeat == 'Ok')  ){   

                $html .= "

                    <a href='#' class='btn btn-default' id='reset-password-button'>Reset password</a>

                ";

            }

            $html .="

                <a href='#' class='btn btn-default' id='console-button'>Console</a>

                <a href='#' class='btn btn-default' id='reinstall-button'>Reinstall</a>

                <a href='#' class='btn btn-default' id='settings-button'>Overwrite Specifications</a>

            ";

            return [

                "" => $html,

                "VM Information <a href='clientsservices.php?userid=" . $params["userid"] . "&id=" . $params["serviceid"] ."'><i class='fas fa-sync-alt' style='color:green;'></i></a>" => $statusHtml,

                "External Network" => $info->ExternalNetworkEnabled?$externalNetworkHtml:"<a class='btn btn-success' href='clientsservices.php?userid={$params['userid']}&id={$params['serviceid']}&command=enable-external'>Enable</a>",

                "Private Network" => $info->PrivateNetworkEnabled?$privateNetworkHtml:"<a class='btn btn-success' href='clientsservices.php?userid={$params['userid']}&id={$params['serviceid']}&command=enable-internal'>Enable</a>",

            ];

            

        }

    } catch (Exception $e) {

        logModuleCall(

            'solidcp',

            __FUNCTION__,

            $params,

            $e->getMessage()

        );

        return [

            "Error <a href='clientsservices.php?userid=" . $params["userid"] . "&id=" . $params["serviceid"] ."'><i class='fas fa-sync-alt' style='color:green;'></i></a>" => $e->getMessage(),

        ];

    }

    return array();

}





function solidcp_buttonReinstallFunction($params){

    try{

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];

        $info = $api->getVirtualMachine($virtualMachineId);

        $operatingSystems = $api->getOperatingSystemTemplates($info->PackageId);

        foreach( $operatingSystems as $os){

            if($os->Name == $params['customfields']['os'] ){

                $operatingSystem = $os->Path;

            }

        }

        $hostnameArray = explode('.',$params['customfields']['hostname']);

        $hostname = $hostnameArray[0] .  "." . $params['serviceid'];

        $reinstallParams = [

            'itemId' => $virtualMachineId,

            'hostname' => $hostname,

            'template' => $operatingSystem

        ];

        $response = $api->reinstallVirtualMachine( $reinstallParams );

        if( $response->IsSuccess ){

            $vmId = $response->Value;

            $row = Capsule::table('tblcustomfields')->where('fieldname','like', 'vm_id%')->where('relid', $params['pid'])->first();

            Capsule::table('tblcustomfieldsvalues')->where('fieldid', $row->id)->where('relid', $params['serviceid'])->update(['value' => $vmId]);

            $virtualMachineId = $vmId;

            $command = 'EncryptPassword';

            $postData = array(

                'password2' => $response->AdministratorPassword,

            );

            $results = localAPI($command, $postData);

            Capsule::Table('tblhosting')->where('id',$params['serviceid'])->update(['password' => $results["password"]]);

            $row = Capsule::table('tblcustomfields')->where('fieldname','like', 'hostname%')->where('relid', $params['pid'])->first();



            return "success";



        }else{

            return $response->error;

        }



    } catch (Exception $e) {

         logModuleCall(

             'solidcp',

             __FUNCTION__,

             $params,

             $e->getMessage()

         );

         return $e->getMessage();

     }

     return 'success';



}



function solidcp_actionConsoleFunction($params){

    try{

        require_once 'lang/en.php';

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];

        $console = $api->getVirtualMachineGuacamoleUrl($virtualMachineId);

         return array(

             'templatefile' => "templates/console",

             'vars' => array(

                 'lang' => $lang,

                 'console' => $console

             ),

         );



    }catch (Exception $e) {

        logModuleCall(

          'solidcp',

          __FUNCTION__,

          $params,

          $e->getMessage()

      );

      return $e->getMessage();

    }

    return 'success';

}



 function solidcp_actionSnapshots($params){

     try{

         require_once 'lang/en.php';

         $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

         $virtualMachineId = $params['customfields']['vm_id'];

         $error = '';

         $success = false;

         if( isset($_POST["create_snapshot"]) ){

             $request = $api->createVirtualMachineSnapshot($virtualMachineId);

             if( isset($request->error) && $request->error){

                 $error = $request->error;

             }else{

                 $success = true;

             }

             sleep(5);

         }

         if( isset($_POST["delete_snapshot"]) ){

              $request = $api->deleteVirtualMachineSnapshot($virtualMachineId,$_POST["snapshotId"]);

              if( isset($request->error) && $request->error){

                  $error = $request->error;

              }else{

                  $success = true;

              }

              sleep(5);

         }

         if( isset($_POST["restore_snapshot"]) ){

               $request = $api->restoreVirtualMachineSnapshot($virtualMachineId,$_POST["snapshotId"]);

               if( isset($request->error) && $request->error){

                   $error = $request->error;

               }else{

                   $success = true;

               }

               sleep(5);

         }



         $request = $api->getVirtualMachineSnapshots($virtualMachineId);

         $snapshots = isset($request->VirtualMachineSnapshot)?$request->VirtualMachineSnapshot:[];

         if(!is_array($snapshots)){

             $snapshots = [$snapshots];

         }

          return array(

              'templatefile' => "templates/snapshots",

              'vars' => array(

                  'lang' => $lang,

                  'snapshots' => $snapshots,

                  "error" => $error,

                  "success" => $success

              ),

          );



     }catch (Exception $e) {

         logModuleCall(

           'solidcp',

           __FUNCTION__,

           $params,

           $e->getMessage()

       );

       return $e->getMessage();

     }

     return 'success';

 }







 function solidcp_ChangePackage(array $params)

 {

     try {

         $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

         $virtualMachineId = $params['customfields']['vm_id'];

         try{

             $response = $api->shutdownVirtualMachine($virtualMachineId);

         }catch(Exception $e){

         }

         if( $response->IsSuccess ){

             $params = [

                 'CpuCores' => $params["configoption2"],

                 'ramMB' => $params["configoption4"],

                 'hddGB' => $params["configoption7"],

                 'snapshots' => $params["configoption5"],

                 'hddMinimumIOPS' => $params["configoption9"],

                 'hddMaximumIOPS' => $params["configoption11"],

                 'dvdInstalled' => $params["configoption15"]?1:0,

                 'numLock' => $params["configoption17"]?1:0,

                 'startShutdownAllowed' => $params["configoption16"]?1:0,

                 'pauseResumeAllowed' => $params["configoption20"]?1:0,

                 'rebootAllowed' => $params["configoption21"]?1:0,

                 'resetAllowed' => $params["configoption22"]?1:0,

                 'reinstallAllowed' => $params["configoption18"]?1:0,

                 'externalNetworkEnabled' => $params["configoption13"]?1:0,

                 'privateNetworkEnabled' => $params["configoption14"]?1:0,

                 'bootFromCD' => $params["configoption16"]?1:0,

                 'DynamicMemoryMinimum' => $params["configoption6"],

                 'DynamicMemoryMaximum' => $params["configoption8"],

                 'DynamicBuffer' => $params["configoption10"],

                 'DynamicPriority' => $params["configoption12"],

              ];

              

             $response = $api->updateVirtualMachineConfiguration($virtualMachineId,$params);

             logModuleCall(

                'solidcp',

                __FUNCTION__,

                $params,

                serialize( $response )

             );

             if( $response->IsSuccess ){

                 $api->startVirtualMachine($virtualMachineId);

                 return "success";

             }else{

                  return $response->error;

            }

         }else{

             return $response->error;

         }





     } catch (Exception $e) {

         // Record the error in WHMCS's module log.

         logModuleCall(

             'solidcp',

             __FUNCTION__,

             $params,

             $e->getMessage()

         );

         return $e->getMessage();

     }

     return 'success';

 }



 function solidcp_actionLogs($params){

    try{

        require_once 'lang/en.php';

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];

        $response = $api->getVirtualMachineLogs($virtualMachineId);

        return array(

               'templatefile' => "templates/logs",

               'vars' => array(

                   'lang' => $lang,

                   'logs' => $logs,

               ),

           );





    } catch (Exception $e) {

          // Record the error in WHMCS's module log.

          logModuleCall(

              'solidcp',

              __FUNCTION__,

              $params,

              $e->getMessage()

          );

          return $e->getMessage();

      }



 }



function solidcp_actionIso($params){

    try{

        require_once 'lang/en.php';

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];

        $sucess = false;

        $error = false;

        if(isset($_POST['mount-iso'])){

            $isoPath = $_POST["iso-path"];

            $response = $api->insertDVD($virtualMachineId,$isoPath);

            if($response->IsSuccess){

                $success = true;

            }else{

               $error = $response->error;

            }

        }

        if(isset($_POST['unmount-iso'])){

            $isoPath = $_POST["iso-path"];

            $response = $api->ejectDVD($virtualMachineId);

            if($response->IsSuccess){

                $success = true;

            }else{

               $error = $response->error;

            }

        }



        return array(

               'templatefile' => "templates/iso",

               'vars' => array(

                   'lang' => $lang,

                   "success" => $success,

                   "error" => $error

               ),

           );



     } catch (Exception $e) {

           // Record the error in WHMCS's module log.

           logModuleCall(

               'solidcp',

               __FUNCTION__,

               $params,

               $e->getMessage()

           );

           return $e->getMessage();

       }



  }





 function solidcp_actionSettings($params){

     try{

         require_once 'lang/en.php';

         $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

         $virtualMachineId = $params['customfields']['vm_id'];

         if(isset($_POST['notifications'])){

             if(isset($_POST['enable-notification'])){



                 Capsule::table("mod_solidcp_notifications")->where("serviceid",$params["serviceid"])->delete();

                 Capsule::table("mod_solidcp_notifications")->insert(["serviceid" =>$params["serviceid"]]);

             }else{

                 Capsule::table("mod_solidcp_notifications")->where("serviceid",$params["serviceid"])->delete();

             }

             if(isset($_POST['enable-dvd'])){

                 $api->ejectVirtualMachineDVD($virtualMachineId);

             }

         }

         $dvd = $api->getVirtualMachineDVD($virtualMachineId);

         $notifications = Capsule::table("mod_solidcp_notifications")->where("serviceid",$params["serviceid"])->first();

        $rdp = file_get_contents('templates/rdp/fastest-windowed.txt', FILE_USE_INCLUDE_PATH);

        $rdpfw = str_replace("{VM_IP}",$params["model"]->dedicatedip,$rdp);

        $rdp = file_get_contents('templates/rdp/fastest-fullscreen.txt', FILE_USE_INCLUDE_PATH);

        $rdpff = str_replace("{VM_IP}",$params["model"]->dedicatedip,$rdp);

        $rdp = file_get_contents('templates/rdp/balanced-windowed.txt', FILE_USE_INCLUDE_PATH);

        $rdpbw = str_replace("{VM_IP}",$params["model"]->dedicatedip,$rdp);

        $rdp = file_get_contents('templates/rdp/balanced-fullscreen.txt', FILE_USE_INCLUDE_PATH);

        $rdpbf = str_replace("{VM_IP}",$params["model"]->dedicatedip,$rdp);

        $rdp = file_get_contents('templates/rdp/bestlooking-windowed.txt', FILE_USE_INCLUDE_PATH);

        $rdpblw = str_replace("{VM_IP}",$params["model"]->dedicatedip,$rdp);

        $rdp = file_get_contents('templates/rdp/bestlooking-fullscreen.txt', FILE_USE_INCLUDE_PATH);

        $rdpblf = str_replace("{VM_IP}",$params["model"]->dedicatedip,$rdp);

         

         return array(

                'templatefile' => "templates/settings",

                'vars' => array(

                    'lang' => $lang,

                    'notifications' => $notifications,

                    'dvd' => $dvd,

                    'rdpfw' => $rdpfw,

                    'rdpff' => $rdpff,

                    'rdpbw' => $rdpbw,

                    'rdpbf' => $rdpbf,

                    'rdpblw' => $rdpblw,

                    'rdpblf' => $rdpblf,

                ),

            );



      } catch (Exception $e) {

            // Record the error in WHMCS's module log.

            logModuleCall(

                'solidcp',

                __FUNCTION__,

                $params,

                $e->getMessage()

            );

            return $e->getMessage();

        }



   }





function solidcp_TestConnection($params){

    try{

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $users = $api->getUsers();

        $success = true;

        $errorMsg = '';

    }catch(Exception $e){

        logModuleCall(

            'solidcp',

            __FUNCTION__,

            $params,

            $e->getMessage()

        );

        $success = false;

        $errorMsg = $e->getMessage();

    }



    return array(

        'success' => $success,

        'error' => $errorMsg,

    );

}



function solidcp_adminButtonConsole($params){

    try{

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];

        $console = $api->getVirtualMachineGuacamoleUrl($virtualMachineId);

        if( !$console->response ){

            return "Console not available";

        }else{

            return "Console link: {$console->response}";

        }



    }catch(Exception $e){

        return $e->getMessage();

    }

}



function solidcp_getdetails($params) {



    try {

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];

        

        $info = $api->getVirtualMachine($virtualMachineId);

        $isos = $api->getIsoLibrary($virtualMachineId);

        $dvd = $api->getDVD($virtualMachineId);

        $snapshots = $api->getVirtualMachineSnapshots($virtualMachineId);

        $details = $api->getVirtualMachineDetails($virtualMachineId);



        $data = [];



        #$data['info'] = $info;

        $data['dvd'] = $dvd;

        $data['isos'] = $isos;

        $data['isoOptions'] = $isos->LibraryItem->Name;

        $data['mountStatus'] = !$dvd->Name?"None":$dvd->Name;

        $data['bootFromCD'] = $info->BootFromCD?'Yes':'NO';

        $data['snapshots'] = $snapshots;

        $data['snapshotsUsed'] = isset($snapshots->VirtualMachineSnapshot)?count($snapshots->VirtualMachineSnapshot):0;

        //trimming some params:

        unset($details->Id);

        unset($details->TypeId);

        unset($details->PackageId);

        unset($details->ServiceId);

        unset($details->Name);

        unset($details->ProvisioningStatus);

        unset($details->DynamicMemory);

        unset($details->HddSize);

        unset($details->HddMaximumIOPS);

        unset($details->HddMinimumIOPS);

        unset($details->NumLockEnabled);

        unset($details->StartTurnOffAllowed);

        unset($details->PauseResumeAllowed);

        unset($details->RebootAllowed);

        unset($details->ResetAllowed);

        unset($details->ReinstallAllowed);

        unset($details->LegacyNetworkAdapter);

        unset($details->RemoteDesktopEnabled);

        unset($details->ExternalNetworkEnabled);

        unset($details->PrivateNetworkEnabled);

        unset($details->ManagementNetworkEnabled);

        unset($details->Status);

        unset($details->ReplicationState);

        unset($details->EnableSecureBoot);

        unset($details->defaultaccessvlan);

        unset($details->NeedReboot);

        

        $created_date = new DateTime($details->CreatedDate);

        $created_date->setTimezone(new DateTimeZone('GMT+7'));

        $details->CreatedDate = $created_date->format("c");



        $creation_time = new DateTime($details->CreationTime, new DateTimeZone('GMT-7'));

        $creation_time->setTimezone(new DateTimeZone('GMT+7'));

        $details->CreationTime = $creation_time->format("c");

        

        $data['details'] = $details;

        $data['cpuFree'] = 100 - $details->CpuUsage;

        $data['snapshotsUsage'] = ( $data['snapshotsUsed'] / $info->SnapshotsNumber ) * 100;

        $data['snapshotsAvailable'] = $info->SnapshotsNumber - $snapshotsUsed;

        $network = $api->getVirtualMachineIpAddresses($virtualMachineId);

        if (isset($network[0])){

            unset($network[0]->AddressId);

            unset($network[0]->NATAddress);

        }

        $data['network'] = $network;



    } catch (Exception $e) {

        return $e->getMessage();

    };



    return [

        "result" => "success",

        "data" => $data,

    ];

}





function solidcp_postpower($params) {



    try {

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];

        

        $power_action = $_POST['power_action'];

        switch ($power_action) {

            case 'start':

                $response = $api->startVirtualMachine($virtualMachineId);

                if( !$response->IsSuccess ){

                    return serialize($response);

                }

                $message = "Server has been successfully started";

                break;

            case 'restart':

                $response = $api->rebootVirtualMachine($virtualMachineId);

                if( !$response->IsSuccess ){

                    return serialize($response);

                }

                $message = "Server has been successfully restarted";

                break;

            case 'shutdown':

                $response = $api->shutdownVirtualMachine($virtualMachineId);

                if( !$response->IsSuccess ){

                    return serialize($response);

                }

                $message = "Server has been successfully shutdown";

                break;

            case 'turnoff':

                $response = $api->stopVirtualMachine($virtualMachineId);

                if( !$response->IsSuccess ){

                    return serialize($response);

                }

                $message = "Server has been successfully turned off";

                break;

            case 'reset':

                $response = $api->resetVirtualMachine($virtualMachineId);

                if( !$response->IsSuccess ){

                    return serialize($response);

                }

                $message = "Server has been successfully reset";

                break;

        }



    } catch (Exception $e) {

        return $e->getMessage();

    };



    $api->logAction($params['serviceid'], "$power_action", 0);



    return [

        "result" => "success",

        "data" => $message,

    ];

}





function solidcp_resetpassword($params) {

    try {

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];



        $password = solidcp_generatePassword();

        $passwordParams = [

            'itemId' => $virtualMachineId,

            'password' => $password

        ];

        $response = $api->updateVirtualMachinePassword($passwordParams);

        if( $response->IsSuccess ){

            $command = 'EncryptPassword';

            $postData = array(

                'password2' => $password,

            );

            $results = localAPI($command, $postData);

            Capsule::Table('tblhosting')->where('id',$params['serviceid'])->update(['password' => $results['password']]);

            $success = true;

            $api->logAction($params['serviceid'],'password reset successfull', 0);

        }else{

            return $response->error;

        }

    } catch (Exception $e) {

        return $e->getMessage();

    };



    return [

        "result" => "success",

        "data" => $password,

    ];

}





function solidcp_consoleurl($params) {

    try {

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];

        $console = $api->getVirtualMachineGuacamoleUrl($virtualMachineId);

        if (!isset($console->response)){

            return "couldn't retrieve console URL";

        }

        $api->logAction($params['serviceid'],'console URL returned', 0);

    } catch (Exception $e) {

        return $e->getMessage();

    };



    return [

        "result" => "success",

        "data" => $console->response,

    ];

}





function solidcp_getavailableos($params) {

    try {

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];

        

        $info = $api->getVirtualMachine($virtualMachineId);

        $operatingSystems = $api->getOperatingSystemTemplates($info->PackageId);

        $data = [];

        foreach($operatingSystems as $operatingSystem){

            $data[] = [

                'template' => $operatingSystem->Path,

                'description' => $operatingSystem->Name,

            ];

        }

    } catch (Exception $e) {

        return $e->getMessage();

    };



    return [

        "result" => "success",

        "data" => $data,

    ];

}





function solidcp_restoreexternalnetwork($params) {

    try {

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];

        $response = $api->restoreVirtualMachineExternal($virtualMachineId);

        if( $response->IsSuccess ){

            $api->logAction($params['serviceid'],'restore external network', 0);

        }else{

            return $response->error;

        }

    } catch (Exception $e) {

        return $e->getMessage();

    };



    return [

        "result" => "success",

    ];

}



function solidcp_changehostname($params) {

    try {

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        

        $virtualMachineId = $params['customfields']['vm_id'];

        $hostnameArray = explode('.',$params['customfields']['hostname']);

        $hostnameParams = [

            'itemId' => $virtualMachineId ,

            'hostname' => $_POST["hostname"] . '.' . $hostnameArray[1]

        ];





        $response = $api->updateVirtualMachineHostname($hostnameParams);

        if ( $response->IsSuccess ) {

            $hostnameUpdateSuccess = true;

            $row = Capsule::table('tblcustomfields')->where('fieldname','like', 'hostname%')->where('relid', $params['pid'])->first();

            Capsule::table('tblcustomfieldsvalues')->where('fieldid', $row->id)->where('relid', $params['serviceid'])->update(['value' => $_POST["hostname"] . '.' .  $hostnameArray[1]]);

            Capsule::table("tblhosting")->where("id",$params["serviceid"])->update(["domain" => $_POST["hostname"]]);

            $api->logAction($params['serviceid'], 'update hostname', 0);

        } else {

            return $response->error;

        }

    } catch (Exception $e) {

        return $e->getMessage();

    };



    return [

        "result" => "success",

    ];

}





function solidcp_reinstallos($params) {

    try {

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];



        $new_os_template = $_POST['template'];

        $new_os_found = FALSE;



        $info = $api->getVirtualMachine($virtualMachineId);

        $operatingSystems = $api->getOperatingSystemTemplates($info->PackageId);

        foreach($operatingSystems as $operatingSystem){

            if ($new_os_template == $operatingSystem->Path){

                $new_os_found = TRUE;

            }

        }



        if (!$new_os_found){

            return "Cannot find OS template in the available list";

        }

        

        $response = $api->reinstallVirtualMachineNew($params, $virtualMachineId, $new_os_template);

        if( $response->IsSuccess ){

            $vmId = $response->Value;

            $row = Capsule::table('tblcustomfields')

                ->where('fieldname','like', 'vm_id%')

                ->where('relid', $params['pid'])

                ->first();

            Capsule::table('tblcustomfieldsvalues')

                ->where('fieldid', $row->id)

                ->where('relid', $params['serviceid'])

                ->update(['value' => $vmId]);



            $servicePassword =  $response->AdministratorPassword;

            Capsule::Table('tblhosting')

                ->where('id',$params['serviceid'])

                ->update(['password' => encrypt($servicePassword)]);



            $row = Capsule::table('tblcustomfields')

                ->where('fieldname', 'os')

                ->where('relid', $params['pid'])

                ->first();

            Capsule::table('tblcustomfieldsvalues')

                ->where('fieldid', $row->id)

                ->where('relid', $params['serviceid'])

                ->update(['value' => $new_os_template]);



            $api->logAction($params['serviceid'], 'reinstall '.$new_os_template, 0);

        } else {

            return $response->error;

        }



    } catch (Exception $e) {

        return $e->getMessage();

    };



    return [

        "result" => "success",

        "data" => $response->AdministratorPassword,

    ];

}





//this function loads certain tabs contents depending on request

//requests are coming from service page and coming through /modules/servers/solidcp/lazyload.php

function solidcp_lazyload($params) {



    global $_LANG;



    $tab = $_POST['tabname'];



    try {

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];



        require_once 'lang/en.php';



        $data = [];//common variables for templates

        $data['serviceid'] = $params['serviceid'];

        $data['lang'] = $lang;

        $data['LANG'] = $_LANG;

        switch ($tab) {

            case 'network':

                $info = $api->getVirtualMachine($virtualMachineId);

                $data['info'] = $info;

                $data['externalNetwork'] = $api->getVirtualMachineIpAddresses($virtualMachineId);



                break;



            case 'overview':

                $info = $api->getVirtualMachine($virtualMachineId);

                $package = $api->getPackage($info->PackageId);

                $ipAddresses = $api->getVirtualMachineIpAddresses($virtualMachineId);

                $details = $api->getVirtualMachineDetails($virtualMachineId);

                $actionLogs = $api->getActionLogs($params['serviceid']);

                

                $diskUsage = ( $details->HddLogicalDisks->LogicalDisk->Size - $details->HddLogicalDisks->LogicalDisk->FreeSpace) / $details->HddLogicalDisks->LogicalDisk->Size * 100;

                $diskUsed = ( $details->HddLogicalDisks->LogicalDisk->Size - $details->HddLogicalDisks->LogicalDisk->FreeSpace);



                //$diskUsage = round( $info->HddSize - $details->HddLogicalDisks->LogicalDisk->FreeSpace );

                $packageParts = explode('-', $package->PackageName);

                $countryIso = isset($packageParts[0])?$packageParts[0]:'US';

                if (strlen($countryIso) > 2) {

                    $countryIso = substr($countryIso, 0, 2);

                }

                $countriesName = [

                    'CA' => 'Canada',

                    'DE' => 'Germany',

                    'FI' => 'Finland',

                    'FR' => 'France',

                    'GB' => 'United Kingdom',

                    'UK' => 'United Kingdom',

                    'US' => 'United States of America'

                ];





                $invoiceItem = Capsule::table("tblinvoiceitems")->where("relid",$params["serviceid"])->orderBy("id","DESC")->first();

                if( $invoiceItem ){

                    $invoice = Capsule::table("tblinvoices")->where("id",$invoiceItem->invoiceid)->where("status","Unpaid")->first();

                    if($invoice){

                        $invoiceDueDate = $invoice->duedate;

                    }else{

                        $invoiceDueDate = '';

                    }

                }else{

                    $invoiceDueDate = '';

                }

                $contacts = Capsule::table('tblcontacts')->where('userid',$params['clientsdetails']['userid'])->get();

                $uuidArray = explode('.',$info->Name);

                $uuid = array_pop($uuidArray);

                $hostname = implode('.',$uuidArray);





                //billing section

                $service = new WHMCS\Service($params['serviceid'], $params['clientsdetails']['userid']);

                $data['regdate'] = fromMySQLDate($service->getData("regdate"), 0, 1, "-");

                $data['recurringamount'] = formatCurrency($service->getData("amount"));

                $data['billingcycle'] = $service->getBillingCycleDisplay();

                $data['nextduedate'] = fromMySQLDate($service->getData("nextduedate"), 0, 1, "-");

                $data['pendingcancellation'] = $service->hasCancellationRequest();





                $data['info'] = $info;

                $data['package'] = $package;

                $data['details'] = $details;

                $data['countryName'] = $packageParts[0];

                $data['ipAddresses'] = $ipAddresses;

                $data['uuid'] = $uuid;

                $data['hostname'] = $hostname;

                $data['invoice'] = $invoice;

                $data['diskUsage'] = $diskUsage;

                $data['diskUsed'] = $diskUsed;

                $data['actionLogs'] = $actionLogs;



                break;



            case "checkpoints":

                $info = $api->getVirtualMachine($virtualMachineId);

                $snapshots = $api->getVirtualMachineSnapshots($virtualMachineId);

                if( $snapshots->VirtualMachineSnapshot  && !is_array($snapshots->VirtualMachineSnapshot) ){

                    $snapshots->VirtualMachineSnapshot = [$snapshots->VirtualMachineSnapshot];

                }

                $dateFormat = \WHMCS\Database\Capsule::table('tblconfiguration')->where('setting','DateFormat')->first();

                switch($dateFormat->value){

                    case 'DD/MM/YYYY':

                        $dateFormat = 'd/m/Y';

                        break;

                    case 'DD.MM.YYYY':

                        $dateFormat = 'd.m.Y';

                        break;

                    case 'DD-MM-YYYY':

                        $dateFormat = 'd-m-Y';

                        break;

                    case 'MM/DD/YYYY':

                        $dateFormat = 'm/d/Y';

                        break;

                    case 'YYYY/MM/DD':

                        $dateFormat = 'Y/m/d';

                        break;

                    case 'YYYY-MM-DD':

                        $dateFormat = 'Y-m-d';

                        break;

                }

                foreach($snapshots->VirtualMachineSnapshot as $k => $snapshot){

                    $snapshots->VirtualMachineSnapshot[$k]->Created = date($dateFormat . ' H:i:s', strtotime($snapshot->Created));

                }



                $snapshotsUsed = isset($snapshots->VirtualMachineSnapshot)?count($snapshots->VirtualMachineSnapshot):0;

                $snapshotsUsage = ( $snapshotsUsed / $info->SnapshotsNumber ) * 100;

                $snapshotsAvailable = $info->SnapshotsNumber - $snapshotsUsed;

                

                $data['info'] = $info;

                $data['snapshotsUsed'] = $snapshotsUsed;

                $data['snapshots'] = $snapshots;



                break;



            case 'images':

                $info = $api->getVirtualMachine($virtualMachineId);

                $dvd = $api->getDVD($virtualMachineId);

                $isos = $api->getIsoLibrary($virtualMachineId);

                $isoOptions = "<option value='{$isos->LibraryItem->Path}'>{$isos->LibraryItem->Name}</option>";



                $data['info'] = $info;

                $data['dvd'] = $dvd;

                $data['isos'] = $isoOptions;



                break;



            case 'reinstall':

                $info = $api->getVirtualMachine($virtualMachineId);

                $operatingSystems = $api->getOperatingSystemTemplates($info->PackageId);

                $operatingSystemsHtml = '';

                foreach($operatingSystems as $operatingSystem){

                    $operatingSystemsHtml .= "<option value='{$operatingSystem->Path}'>{$operatingSystem->Name}</option>";

                }



                $data['info'] = $info;

                $data['operatingSystemsHtml'] = $operatingSystemsHtml;



                break;



            case 'settings':

                $details = $api->getVirtualMachineDetails($virtualMachineId);

                $settings = $api->getSettings($params['serviceid']);

                $shutdown = false;

                $heartbeat = false;

                foreach($settings as $setting) {

                    if( ($setting->setting == 'shutdown') && ($setting->value == 1) ){

                        $shutdown = true;

                    } 

                    if( ($setting->setting == 'heartbeat') && ($setting->value == 1) ){

                         $heartbeat = true;

                    }

                }



                $data['details'] = $details;

                $data['shutdownSetting'] = $shutdown;

                $data['heartbeatSetting'] = $heartbeat;



                break;



            case 'actionlogs':

                $actionLogs = $api->getActionLogs($params['serviceid']);



                $data['actionLogs'] = $actionLogs;

                

                break;

        }



        //get the email body first

        $template_path = dirname(__FILE__)."/templates/tabs/".$tab."_contents.tpl";

        $tpl_contents = file_get_contents($template_path);

        $local_smarty = new \Smarty();

        $local_smarty->assign($data);

        $template_rendered = $local_smarty->fetch('string:'.$tpl_contents);



    } catch (Exception $e) {

        return $e->getMessage();

    };



    return [

        "result" => "success",

        "data" => $template_rendered,

    ];

}





function solidcp_servicestatus($params) {



    try {

        $api = new \SolidCP\Api($params["serverhostname"],$params["serverusername"],$params["serverpassword"],$params["serverport"],$params["serversecure"]);

        $virtualMachineId = $params['customfields']['vm_id'];



        $details = $api->getVirtualMachineDetails($virtualMachineId);

        

        $data = [];

        $data['ipAddress'] = $params["model"]->dedicatedip;

        

        if ($details->State == 'Running') {

            $data['state'] = 'Online';

            

            $console = $api->getVirtualMachineGuacamoleUrl($virtualMachineId);

            if (isset($console->response)){

                $data['console'] = $console->response;

            }

        } else {

            $data['state'] = 'Offline';

        }



    } catch (Exception $e) {

        return $e->getMessage();

    };



    return [

        "result" => "success",

        "data" => $data,

    ];

}