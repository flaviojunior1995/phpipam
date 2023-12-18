<?php

/**
 * Edit provider result
 ***************************/

/* functions */
require_once( dirname(__FILE__) . '/../../../functions/functions.php' );

# initialize user object
$Database 	= new Database_PDO;
$User 		= new User ($Database);
$Admin	 	= new Admin ($Database, false);
$Tools	 	= new Tools ($Database);
$Result 	= new Result ();

# verify that user is logged in
$User->check_user_session();
# check maintaneance mode
$User->check_maintaneance_mode ();

# perm check popup
if($_POST['action']=="edit") {
    $User->check_module_permissions ("circuits", User::ACCESS_RW, true, false);
}
else {
    $User->check_module_permissions ("circuits", User::ACCESS_RWA, true, false);
}

# validate csrf cookie
$User->Crypto->csrf_cookie ("validate", "circuit", $_POST['csrf_cookie']) === false ? $Result->show("danger", _("Invalid CSRF cookie"), true) : "";

# validate action
$Admin->validate_action ($_POST['action'], true);
# get modified details
$circuit = $Admin->strip_input_tags($_POST);

# IDs must be numeric
if($circuit['action']!="add" && !is_numeric($circuit['id']))					{ $Result->show("danger", _("Invalid ID"), true); }
if(!is_numeric($circuit['provider']))											{ $Result->show("danger", _("Invalid ID"), true); }

# Circuit ID must be present
if($circuit['cid'] == "") 													{ $Result->show("danger", _('Circuit ID is mandatory').'!', true); }

# validate provider
if($Tools->fetch_object("circuitProviders","id",$circuit['provider'])===false) { $Result->show("danger", _('Invalid provider').'!', true); }

# validate type
$all_types = $Tools->fetch_all_objects ("circuitTypes", "ctname");
$type_id_array = [];
foreach($all_types as $t){ array_push($type_id_array, $t->id); }

if(!in_array($circuit['type'], $type_id_array))									{ $Result->show("danger", _('Invalid type').'!', true); }

# status
$statuses = array ("Active", "Inactive", "Reserved");
if(!in_array($circuit['status'], $statuses))									{ $Result->show("danger", _('Invalid status').'!', true); }

#Check if circuit is part of a larger circuit
if($_POST['action'] == 'delete'){
	$logical_circuit_array = $Tools->fetch_all_logical_circuits_using_circuit($circuit['id']);
	if(!empty($logical_circuit_array))  		{ $Result->show("danger", _('Circuit is currently used in a larger logical circuit').'!', true); }

}

# NETWORK PARAMETERS CHECK BEGIN

# Validade vlan Point A
if( !(($circuit['svlandevice1'] >= 0) && ($circuit['svlandevice1']) <= 4096 && is_numeric($circuit['svlandevice1']) || $circuit['svlandevice1'] == "" ) )
{ $Result->show("danger", _("Invalid S-VLAN Point A"), true); }

if( !(($circuit['cvlandevice1'] >= 0) && ($circuit['cvlandevice1']) <= 4096 && is_numeric($circuit['cvlandevice1']) || $circuit['cvlandevice1'] == "" ) )
{ $Result->show("danger", _("Invalid C-VLAN Point A"), true); }

# Validade vlan Point B
if( !(($circuit['svlandevice2'] >= 0) && ($circuit['svlandevice2']) <= 4096 && is_numeric($circuit['svlandevice2']) || $circuit['svlandevice2'] == "" ) )
{ $Result->show("danger", _("Invalid S-VLAN Point B"), true); }

if( !(($circuit['cvlandevice2'] >= 0) && ($circuit['cvlandevice2']) <= 4096 && is_numeric($circuit['cvlandevice2']) || $circuit['cvlandevice2'] == "" ) )
{ $Result->show("danger", _("Invalid C-VLAN Point B"), true); }

# Validade PWID
if( !(($circuit['pwid'] >= 0) && ($circuit['pwid']) <= 4294967295 && is_numeric($circuit['pwid']) || $circuit['pwid'] == "" ) )
{ $Result->show("danger", _("Invalid PWID"), true); }

# Validate RD
if( !(preg_match('/^([0-9]|[1-9][0-9]{1,3}|[1-5][0-9]{4}|6[0-4][0-9]{3}|65[0-4][0-9]{2}|655[0-2][0-9]|6553[0-5]):([0-9]|[1-9][0-9]{1,8}|[1-3][0-9]{9}|4[01][0-9]{8}|42[0-8][0-9]{7}|429[0-3][0-9]{6}|4294[0-8][0-9]{5}|42949[0-5][0-9]{4}|429496[0-6][0-9]{3}|4294967[01][0-9]{2}|42949672[0-8][0-9]|429496729[0-5])$/', $circuit['route_distinguisher']) || $circuit['route_distinguisher'] == "" ) )
{ $Result->show("danger", _("Invalid RD"), true); }

# Validate DWDM Channel
if( !(($circuit['dwdmchannel'] >= 21) && ($circuit['dwdmchannel']) <=60  && is_numeric($circuit['dwdmchannel']) || $circuit['dwdmchannel'] == "" ) )
{ $Result->show("danger", _("Invalid DWDM Channel"), true); }

# NETWORK PARAMETERS CHECK END


# process device / location
if($circuit['device1']=="0") {
	$circuit['device1']   = 0;
	$circuit['location1'] = 0;
}
elseif(strpos($circuit['device1'],"device_")!==false) {
	$deviceId = str_replace("device_", "", $circuit['device1']);
	if($Tools->fetch_object("devices","id",$deviceId)===false) 			    { $Result->show("danger", _('Invalid device A').'!', true); }
	// save
	$circuit['device1']   = $deviceId;
	$circuit['location1'] = 0;
}
else {
	$locationId = str_replace("location_", "", $circuit['device1']);
	if($Tools->fetch_object("locations","id",$locationId)===false) 			 { $Result->show("danger", _('Invalid location A').'!', true); }
	// save
	$circuit['device1']   = 0;
	$circuit['location1'] = $locationId;
}

if($circuit['device2']=="0") {
	$circuit['device2']   = 0;
	$circuit['location2'] = 0;
}
elseif(strpos($circuit['device2'],"device_")!==false) {
	$deviceId = str_replace("device_", "", $circuit['device2']);
	if($Tools->fetch_object("devices","id",$deviceId)===false) 			     { $Result->show("danger", _('Invalid device B').'!', true); }
	// save
	$circuit['device2']   = $deviceId;
	$circuit['location2'] = 0;
}
else {
	$locationId = str_replace("location_", "", $circuit['device2']);
	if($Tools->fetch_object("locations","id",$locationId)===false) 			 { $Result->show("danger", _('Invalid location B').'!', true); }
	// save
	$circuit['device2']   = 0;
	$circuit['location2'] = $locationId;
}


# fetch custom fields
$custom = $Tools->fetch_custom_fields('circuits');
if(sizeof($custom) > 0) {
	foreach($custom as $myField) {

		//replace possible ___ back to spaces
		$myField['nameTest'] = str_replace(" ", "___", $myField['name']);
		if(isset($circuit[$myField['nameTest']])) { $circuit[$myField['name']] = $circuit[$myField['nameTest']];}

		//booleans can be only 0 and 1!
		if($myField['type']=="tinyint(1)") {
			if($circuit[$myField['name']]>1) {
				$circuit[$myField['name']] = 0;
			}
		}
		//not null!
		if($myField['Null']=="NO" && strlen($circuit[$myField['name']])==0) { $Result->show("danger", $myField['name']." "._("can not be empty")."!", true); }

		# save to update array
		$update[$myField['name']] = $circuit[$myField['nameTest']];
	}
}

# set update values
$values = array(
	"id"        => $circuit['id'],
	"cid"       => $circuit['cid'],
	"ctype"     => $circuit['ctype'],
	"provider"  => $circuit['provider'],
	"type"      => $circuit['type'],
	"capacity"  => $circuit['capacity'],
	"status"    => $circuit['status'],
	"device1"   => $circuit['device1'],
	"location1" => $circuit['location1'],
	"intdevice1" => $circuit['intdevice1'],
	"cvlandevice1" => $circuit['cvlandevice1'],
	"svlandevice1" => $circuit['svlandevice1'],
	"device2"   => $circuit['device2'],
	"location2" => $circuit['location2'],
	"intdevice2" => $circuit['intdevice2'],
	"cvlandevice2" => $circuit['cvlandevice2'],
	"svlandevice2" => $circuit['svlandevice2'],
	"pwid" => $circuit['pwid'],
	"route_distinguisher" => $circuit['route_distinguisher'],
	"dwdmchannel" => $circuit['dwdmchannel'],
	"address1" => $circuit['address1'],
	"address2" => $circuit['address2'],
	"partner" => $circuit['partner'],
	"partner_info" => $circuit['partner_info'],
	"comment"   => $circuit['comment']
	);
# custom fields
if(isset($update)) {
	$values = array_merge($values, $update);
}
# append customerId
if($User->settings->enableCustomers=="1" && $User->get_module_permissions ("customers")>=User::ACCESS_RW) {
	if (is_numeric($_POST['customer_id'])) {
	       $values['customer_id'] = $_POST['customer_id'] > 0 ? $_POST['customer_id'] : NULL;
	}
}

# update
if(!$Admin->object_modify("circuits", $circuit['action'], "id", $values))	{}
else																	{ $Result->show("success", _("Circuit")." ".$circuit["action"]." "._("successful")."!", false); }
