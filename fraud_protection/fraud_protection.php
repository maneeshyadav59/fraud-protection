<?php
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function fraud_protection_config() {
    $configarray = array(
        "name" => "Fraud Protection",
        "description" => "This is a addon module to detect all duplicates accounts.",
        "version" => "2.1.0",
        "author" => "Kuroit",
        "fields" => array(),
    );
    return $configarray;
}

function fraud_protection_activate() {
    try {
        Capsule::schema()
            ->create('hcil_ip_logs',
                function ($table) {
                    /** @var \Illuminate\Database\Schema\Blueprint $table */
                    $table->increments('id');
                    $table->text('client_id');
                    $table->text('ip');
                    $table->text('event_time');    
                    $table->text('action_type');
                }
            );
        return [
            // Supported values here include: success, error or info
            'status' => 'success',
            'description' => 'fraud protection addon module activated successfully',
        ];
    } catch (\Exception $e) {
        return [
            // Supported values here include: success, error or info
            'status' => "error",
            'description' => 'Unable to create hcil_ip_logs table : ' . $e->getMessage(),
        ];
    }
}

function fraud_protection_deactivate() {
// Undo any database and schema modifications made by your module here
    try {
        Capsule::schema()
            ->dropIfExists('hcil_ip_logs');
            return [
                // Supported values here include: success, error or info
                'status' => 'success',
                'description' => 'fraud protection addon module deactivated successfully',
            ];
    } catch (\Exception $e) {
        return [
            // Supported values here include: success, error or info
            "status" => "error",
            "description" => "Unable to drop hcil_ip_logs table: ".$e->getMessage(),
        ];
    }
}

function fraud_protection_output($vars) {
    $pdo = Capsule::connection()->getPdo();
    $validActions = ['ip_logs','duplicates'];

    if (isset($_GET['action']) && in_array($_GET['action'],$validActions)){
        $action = $_GET['action'];
    } else {
        $action = 'ip_logs';
    }
    echo '<div class="tabs-wrapper">
            <ul class="tabs-list">
                <li class="tab '.($action=='ip_logs'?'active':'').'"><a href="addonmodules.php?module=fraud_protection&action=ip_logs">IP Logs</a></li>
                <li class="tab '.($action=='duplicates'?'active':'').'"><a href="addonmodules.php?module=fraud_protection&action=duplicates">Duplicates</a></li>   
            </ul>
        </div>';
    echo '<section class="im-content-section" style="
    padding: 10px;">';
    
    if($action == 'ip_logs') {
        echo'<div style="display: flex; justify-content: space-between;margin:35px 0px;">
                <form method="post" action="" class="form-inline" id="clear_logs_form">
                    <label>Clear Logs <small>(in days)</small></label>
                    <select class="form-control" name="log_days">
                        <option value="30" selected>30</option>
                        <option value="90">90</option>
                        <option value="180">180</option>
                        <option value="360">360</option>
                    </select>
                    <button type="submit" class="btn btn-danger" id="clear_logs_btn" name="clear_log"><i class="fa fa-trash"></i></button>
                </form>
                <form class="form-inline" method="post">
                    <input type="text" class="form-control" placeholder="Enter IP Address" name="ip_address"/>
                    <button type="submit" class="btn btn-primary" name="search_ip"><i class="fa fa-search"></i></button>
                </form>
            </div>';

        if (isset($_POST['log_days'])) {
            $days = $_POST['log_days'];
            $timeStamp = strtotime("-".$days." days");
            $result = Capsule::table('hcil_ip_logs')->where('event_time', '<', $timeStamp)->delete();
            if ($result) {
                $alertMsg = '<div class="alert alert-success">
                                <strong>Success!</strong> Logs older than <strong>'.$days.' days</strong> has been deleted successfully.
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>';
            } else {
                $alertMsg = '<div class="alert alert-warning">
                                <strong>warning!</strong> No logs found older than <strong>'.$days.' days.
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>';
            }
            echo $alertMsg;
        }


        if (isset($_GET['page']) && $_GET['page'] > 0) {
            $page = $_GET['page'];
        } else {
            $page = 1;
        }
        $limit = 50;
        $records = Capsule::table('hcil_ip_logs')->get();
        $totalRecords = count($records);
        
        $offset = ($page - 1) * $limit;
        if (isset($_POST['search_ip']) && isset($_POST['ip_address']) && $_POST['ip_address'] != '' && $_POST['ip_address'] != NULL) {
            $data = Capsule::table('hcil_ip_logs')->where('ip', $_POST['ip_address'])->limit($limit)->offset($offset)->get();
        } else {
            $data = Capsule::table('hcil_ip_logs')->limit($limit)->offset($offset)->orderBy('event_time','desc')->get();
            $totalPages = ceil($totalRecords/$limit);
        }
        $tableRow= '';
        if ($data && count($data) > 0) {
            foreach($data as $row) {
                $client = getClient($row->client_id);
                $event_time = Date("Y/m/d h:i:s A",$row->event_time);
                $tableRow .='<tr>
                                <td>'.$row->id.'</td>
                                <td><a href="clientsprofile.php?userid='.$row->client_id.'">#'.$row->client_id.' '.$client->firstname.' '.$client->lastname.'</a></td>
                                <td>'.$row->ip.'</td>
                                <td>'.$event_time.'</td>
                                <td>'.$row->action_type.'</td>
                            </tr>';
            }
            $pagination = '';
            for ($i = 1; $i <= $totalPages; $i++) {
                $pagination .= '<li class="hidden-xs '.($page==$i ? 'active' : '').'">
                                    <a href="addonmodules.php?module=fraud_protection&action=ip_logs&page='.$i.'"><strong>'.$i.'</strong></a>
                                </li>';
            }    
        } else {
            $tableRow = '<tr>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th class="text-center">NO DATA FOUND</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                        </tr>';       
        }
        echo'<table id="showlogs" class="table table-striped table-bordered text-center">
                <thead>
                    <tr>
                        <th class="text-center">ID</th>
                        <th class="text-center">Client</th>
                        <th class="text-center">IP</th>
                        <th class="text-center">Event Time</th>
                        <th class="text-center">Action Type</th>
                    </tr>
                </thead>
                <tbody>
                    '.$tableRow.'
                </tbody>
            </table>
            <div class="text-center">
                <ul class="pagination">
                    <li class="previous '.($page == 1 ? 'disabled' : '').'">
                        <a href="addonmodules.php?module=fraud_protection&action=ip_logs&page='.($page-1).'"><strong>«</strong></a>
                    </li>
                    '.$pagination.'
                    <li class="next '.($page == $totalPages ? 'disabled' : '').'">
                        <a href="addonmodules.php?module=fraud_protection&action=ip_logs&page='.($page+1).'"><strong>»</strong></a>
                    </li>
                </ul>
            </div>';
    } else if($action == 'duplicates') {
        echo'<form method="post" action="" class="form-inline text-center" style="margin:30px 0px">
                <label>Search By</label>
                <select class="form-control" name="search_by">
                    <option value="ip_address" '.($_POST['search_by'] == 'ip_address' ? 'selected' : '').'>IP Address</option>
                    <option value="account" '.($_POST['search_by'] == 'account' ? 'selected' : '').'>Accounts</option>    
                </select>
                <input type="text" class="form-control rounded" style="width:20%" name="search_value" value="'.(isset($_POST['search_value']) ? $_POST['search_value'] : '').'" placeholder="Search" required/>
                <button type="submit" class="btn btn-primary" name="search_duplicate"><i class="fas fa-search"></i></button>
            </form>';

        if (isset($_POST['search_duplicate'])) {
            $logs = [];
            if ($_POST['search_by'] == 'ip_address') {
                $field = 'ip';
                $target ='client_id';
                $title = 'IP Address';
                $search_value = $_POST['search_value'];
                $modalHeading = 'Accounts with same IP : '.$_POST['search_value'];
            } else if ($_POST['search_by'] == 'account') {
                $field = 'client_id';
                $target ='ip';
                $title = 'Account';
                $client = getClient($_POST['search_value']);
                
                $clientName = '#'.$client->id.' '.$client->firstname.' '.$client->lastname;
                $search_value = '<a href="clientsprofile.php?userid='.$client->id.'">'.$clientName.'</a>';
                $modalHeading = 'IPs with same account : #'.$client->id;
            }

            $data = Capsule::table('hcil_ip_logs')->where($field, $_POST['search_value'])->orderBy('event_time','desc')->get();
            if (count($data) > 0) {
                foreach ($data as $key) {
                    if (!in_array($key->$target, $logs)) {
                        $logs[] = $key->$target;
                    }
                }
               
                $tableRow = '<tr>
                                <td>'.Date('Y/m/d',$data[0]->event_time).'</td>
                                <td>'.$search_value.'</td>
                                <td><button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#fraudProtectionModal">'.count($logs).' Duplicates</button></td>
                            </tr>';

                $modalBody = '';
                foreach ($logs as $log) {
                    if ($_POST['search_by'] == 'account') {
                        $modalBody .= '<li> '.$log.'</li>';
                    } elseif ($_POST['search_by'] == 'ip_address') {
                        $client = getClient($log);
                        $modalBody .= '<li><a href="clientsprofile.php?userid='.$log.'"># '.$log.' '.$client->firstname.' '.$client->lastname.'</a></li>';
                    }    
                }
            } else {
                $tableRow = '<tr class="text-center">
                                <td></td>
                                <td>NO DATA FOUND</td>
                                <td></td>
                            </tr>';
            }
            
            echo '<table class="table table-striped table-bordered text-center">
                    <thead>
                        <tr>
                            <th class="text-center">Date</th>
                            <th class="text-center">'.$title.'</th>
                            <th class="text-center">Duplicates</th>
                        </tr>
                    </thead>
                    <tbody>
                        '.$tableRow.'
                    </tbody>
                </table>

                <div class="modal fade modal-add-location in" id="fraudProtectionModal" style="display: none; padding-right: 17px;">
                    <div class="modal-dialog">
                        <div class="modal-content panel-primary">
                            <div class="modal-header panel-heading">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                <h4 class="modal-title">'.$modalHeading.'</h4>
                            </div>
                            <div class="modal-body">
                            '.$modalBody.'
                            </div>
                            <div class="modal-footer panel-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal" id="btnEditLocationCancel">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>';
        }
    }
    echo '</section>';

    echo '<script type="text/javascript">
            $(clear_logs_btn).click(function (e) {
                e.preventDefault();
                let data = $("#clear_logs_form").serializeArray();
                let day = data[1]["value"];
                let confirmation = confirm("Are you sure to clear logs older than "+day+" days?");
                if (!confirmation) {
                    return false;
                } else {
                    $("#clear_logs_form").submit();
                }
            });
        </script>';  

}

function getClient($id) {
    $client = Capsule::table('tblclients')->where('id', $id)->get();
    return $client[0];
}