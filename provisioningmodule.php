<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function provisioningmodule_MetaData() {
    return array(
        'DisplayName' => 'Demo Provisioning Module',
        'APIVersion' => '1.1',
        'DefaultNonSSLPort' => '1234',
        'DefaultSSLPort' => '4321',
        'ServiceSingleSignOnLabel' => 'Login to Panel as User',
        'AdminSingleSignOnLabel' => 'Login to Panel as Admin',
        'ListAccountsUniqueIdentifierDisplayName' => 'Domain',
        'ListAccountsUniqueIdentifierField' => 'domain',
        'ListAccountsProductField' => 'configoption1',
    );
}

function provisioningmodule_ConfigOptions() {
    return [
        "username" => [
            "FriendlyName" => "UserName",
            "Type" => "text", # Text Box
            "Size" => "25", # Defines the Field Width
            "Description" => "Textbox",
            "Default" => "Example",
        ],
        "password" => [
            "FriendlyName" => "Password",
            "Type" => "password", # Password Field
            "Size" => "25", # Defines the Field Width
            "Description" => "Password",
            "Default" => "Example",
        ],
        "usessl" => [
            "FriendlyName" => "Enable SSL",
            "Type" => "yesno", # Yes/No Checkbox
            "Description" => "Tick to use secure connections",
        ],
        "package" => [
            "FriendlyName" => "Package Name",
            "Type" => "dropdown", # Dropdown Choice of Options
            "Options" => "Starter,Advanced,Ultimate",
            "Description" => "Sample Dropdown",
            "Default" => "Advanced",
        ],
        "packageWithNVP" => [
            "FriendlyName" => "Package Name v2",
            "Type" => "dropdown", # Dropdown Choice of Options
            "Options" => [
                'package1' => 'Starter',
                'package2' => 'Advanced',
                'package3' => 'Ultimate',
            ],
            "Description" => "Sample Dropdown",
            "Default" => "package2",
        ],
        "disk" => [
            "FriendlyName" => "Disk Space",
            "Type" => "radio", # Radio Selection of Options
            "Options" => "100MB,200MB,300MB",
            "Description" => "Radio Options Demo",
            "Default" => "200MB",
        ],
        "comments" => [
            "FriendlyName" => "Notes",
            "Type" => "textarea", # Textarea
            "Rows" => "3", # Number of Rows
            "Cols" => "50", # Number of Columns
            "Description" => "Description goes here",
            "Default" => "Enter notes here",
        ],
    ];
}
function provisioningmodule_CreateAccount($params)
{
    echo '<pre>';
    print_r($params);
    die('123');
}