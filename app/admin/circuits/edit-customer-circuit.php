<?php

/**
 *	Edit circuit details
 ************************/

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

# perm check popup
if($_POST['action']=="edit") {
    $User->check_module_permissions ("circuits", User::ACCESS_RW, true, true);
}
else {
    $User->check_module_permissions ("circuits", User::ACCESS_RW, true, true);
}

# create csrf token
$csrf = $User->Crypto->csrf_cookie ("create", "circuit");

# strip tags - XSS
$_POST = $User->strip_input_tags ($_POST);

# validate action
$Admin->validate_action ($_POST['action'], true);

# fetch custom fields
$custom = $Tools->fetch_custom_fields('circuits');

# ID must be numeric
if($_POST['action']!="add" && !is_numeric($_POST['circuitid']))	{ $Result->show("danger", _("Invalid ID"), true, true); }

# fetch circuit details
if( ($_POST['action'] == "edit") || ($_POST['action'] == "delete") ) {
	$circuit = $Admin->fetch_object("circuits", "id", $_POST['circuitid']);
	// false
	if ($circuit===false)                                          { $Result->show("danger", _("Invalid ID"), true, true);  }
}
// defaults
else {
	$circuit = new StdClass ();
	$circuit->provider = 0;
}

# fetch all providers, devices, locations
$circuit_providers = $Tools->fetch_all_objects("circuitProviders", "name");
$all_devices       = $Tools->fetch_all_objects("devices", "hostname");
$all_locations     = $Tools->fetch_all_objects("locations", "name");

# no providers
if($circuit_providers===false) 	{
	$btn = $User->get_module_permissions ("circuits")>=User::ACCESS_RWA ? "<hr><a href='' class='btn btn-sm btn-default open_popup' data-script='app/admin/circuits/edit-provider.php' data-class='700' data-action='add' data-providerid='' style='margin-bottom:10px;'><i class='fa fa-plus'></i> "._('Add provider')."</a>" : "";
	$Result->show("danger", _("No circuit providers configured."."<hr>".$btn), true, true);
}

# get types
$all_types = $Tools->fetch_all_objects ("circuitTypes", "ctname");

# set readonly flag
$readonly = $_POST['action']=="delete" ? "readonly" : "";
?>

<script>
$(document).ready(function(){
     if ($("[rel=tooltip]").length) { $("[rel=tooltip]").tooltip(); }
});
</script>

<script src="js/random.js?random=<?= uniqid() ?>" type="text/javascript"></script>

<!-- header -->
<div class="pHeader"><?php print ucwords(_("$_POST[action]")); ?> <?php print _('Circuit'); ?></div>

<!-- content -->
<div class="pContent">

	<form id="circuitManagementEdit">
	<table class="table table-noborder table-condensed">

	<!-- name -->
	<tr>
		<td><?php print _('Circuit ID'); ?></td>
		<td>
			<input readonly='readonly' id='circuitid' type="text" name="cid" style='width:200px;' class="form-control input-sm" placeholder="<?php print _('ID'); ?>" value="<?php if(isset($circuit->cid)) print $Tools->strip_xss($circuit->cid); ?>" <?php print $readonly; ?>>
			<?php
			if( ($_POST['action'] == "edit") || ($_POST['action'] == "delete") ) {
				print '<input type="hidden" name="id" value="'. $_POST['circuitid'] .'">'. "\n";
			} ?>
			<input type="hidden" name="action" value="<?php print $_POST['action']; ?>">
			<input type="hidden" name="csrf_cookie" value="<?php print $csrf; ?>">
      			<input type='button' value='Random' class='button' onclick="javascript:randomCircuit('circuitid',9,<?php echo "'".$configValues['CONFIG_USER_ALLOWEDRANDOMCHARS']."'" ?>)" />
		</td>
	</tr>

  <!-- ctype -->
	<tr hidden>
		<td><?php print _('Circuit Type'); ?></td>
		<td>
			<input type="text" name="ctype" style='width:200px;'  class="form-control input-sm" value="customer" <?php print $readonly; ?>>
		</td>
	</tr>

	<!-- provider -->
	<tr>
		<td><?php print _('Provider'); ?></td>
		<td>
			<select value="1" name="provider" class="form-control input-w-auto input-sm">
				<option selected value="1">TELECALL-BR </option>
			</select>
		</td>
	</tr>

	<!-- type -->
	<tr>
		<td><?php print _('Circuit type'); ?></td>
		<td>
			<select name="type" class="form-control input-w-auto input-sm">
				<?php
				foreach ($all_types as $type) {
					$selected = $circuit->type == $type->id ? "selected" : "";
					print "<option value='$type->id' $selected>$type->ctname</option>";
				}
				?>
			</select>
		</td>
	</tr>

	<!-- capacity -->
	<tr>
		<td><?php print _('Capacity'); ?></td>
		<td>
			<input type="text" name="capacity" style='width:200px;'  class="form-control input-sm" placeholder="<?php print _('Capacity'); ?>" value="<?php if(isset($circuit->capacity)) print $Tools->strip_xss($circuit->capacity); ?>" <?php print $readonly; ?>>
		</td>
	</tr>

	<!-- Status -->
	<tr>
		<td><?php print _('Status'); ?></td>
		<td>
			<select name="status" class="form-control input-w-auto input-sm">
				<?php
				// statuses array
				$statuses = array ("Active", "Inactive", "Reserved");

				foreach ($statuses as $v) {
					$selected = $circuit->status == $v ? "selected" : "";
					print "<option value='$v' $selected>$v</option>";
				}
				?>
			</select>
		</td>
	</tr>

	<?php
    // customers
    if($User->settings->enableCustomers==1 && $User->get_module_permissions ("customers")>=User::ACCESS_R) {
        // fetch customers
        $customers = $Tools->fetch_all_objects ("customers", "title");
        // print
        print '<tr>' . "\n";
        print ' <td class="middle">'._('Customer').'</td>' . "\n";
        print ' <td>' . "\n";
        print ' <select name="customer_id" class="form-control input-sm input-w-auto">'. "\n";

        //blank
        print '<option disabled="disabled">'._('Select Customer').'</option>';
        print '<option value="0">'._('None').'</option>';

        if($customers!=false) {
            foreach($customers as $customer) {
                if ($customer->id == $circuit->customer_id)    	{ print '<option value="'. $customer->id .'" selected>'.$customer->title.'</option>'; }
                else                                         	{ print '<option value="'. $customer->id .'">'.$customer->title.'</option>'; }
            }
        }

        print ' </select>'. "\n";
        print ' </td>' . "\n";
        print '</tr>' . "\n";
    }
	?>

	<!-- devices, locations -->
	<tr>
		<td colspan="2"><hr></td>
	</tr>

	<tr>
		<td><?php print _("Point A"); ?></td>
		<td>
			<select name="device1" class="form-control input-w-auto input-sm">
				<option value="0"><?php print _("None"); ?></option>
				<optgroup label="Devices">
					<?php
					if($all_devices!==false) {
						foreach ($all_devices as $d) {
							$selected = $circuit->device1 == $d->id ? "selected" : "";
							print "<option value='device_$d->id' $selected>$d->hostname</option>";
						}
					}
					?>
				</optgroup>
				<?php if($User->settings->enableLocations=="1") { ?>
				<?php } ?>
			</select>
	<tr>
    		<td>
			<?php print _('Interface Point A'); ?>
		</td>
    		<td>
			<input type="text" name="intdevice1" style='width:200px;' class="form-control input-sm" placeholder="<?php print _('Interface Point A'); ?>" value="<?php if(isset($circuit->intdevice1)) print $Tools->strip_xss($circuit->intdevice1); ?>" <?php print $readonly; ?>>
        	</td>
	</tr>
	<tr>
    		<td>
			<?php print _('S-Vlan Point A'); ?>
		</td>
    		<td>
    			<input type="text" name="svlandevice1" style='width:200px;'  class="form-control input-sm" placeholder="<?php print _('S-Vlan Point A'); ?>" value="<?php if(isset($circuit->svlandevice1)) print $Tools->strip_xss($circuit->svlandevice1); ?>" <?php print $readonly; ?>>
		</td>
	</tr>
	<tr>
    	<td>
			<?php print _('C-Vlan Point A'); ?>
		</td>
    		<td>
    			<input type="text" name="cvlandevice1" style='width:200px;'  class="form-control input-sm" placeholder="<?php print _('C-Vlan Point A'); ?>" value="<?php if(isset($circuit->cvlandevice1)) print $Tools->strip_xss($circuit->cvlandevice1); ?>" <?php print $readonly; ?>>
		</td>
	</tr>

	<tr>
		<td><?php print _("Point B"); ?></td>
		<td>
			<select name="device2" class="form-control input-w-auto input-sm">
				<option value="0"><?php print _("None"); ?></option>
				<optgroup label="Devices">
					<?php
					if($all_devices!==false) {
						foreach ($all_devices as $d) {
							$selected = $circuit->device2 == $d->id ? "selected" : "";
							print "<option value='device_$d->id' $selected>$d->hostname</option>";
						}
					}
					?>
				</optgroup>
				<?php if($User->settings->enableLocations=="1") { ?>
				<?php } ?>
			</select>
      <tr>
    		<td>
			<?php print _('Interface Point B'); ?>
		</td>
    		<td>
    			<input type="text" name="intdevice2" style='width:200px;'  class="form-control input-sm" placeholder="<?php print _('Interface Point B'); ?>" value="<?php if(isset($circuit->intdevice2)) print $Tools->strip_xss($circuit->intdevice2); ?>" <?php print $readonly; ?>>
    		</td>
    	</tr>
	<tr>
    		<td>
			<?php print _('S-Vlan Point B'); ?>
		</td>
    		<td>
    			<input type="text" name="svlandevice2" style='width:200px;'  class="form-control input-sm" placeholder="<?php print _('S-Vlan Point B'); ?>" value="<?php if(isset($circuit->svlandevice2)) print $Tools->strip_xss($circuit->svlandevice2); ?>" <?php print $readonly; ?>>
    		</td>
    	</tr>
	<tr>
    		<td>
			<?php print _('C-Vlan Point B'); ?>
		</td>
    		<td>
    			<input type="text" name="cvlandevice2" style='width:200px;'  class="form-control input-sm" placeholder="<?php print _('C-Vlan Point B'); ?>" value="<?php if(isset($circuit->cvlandevice2)) print $Tools->strip_xss($circuit->cvlandevice2); ?>" <?php print $readonly; ?>>
		</td>
	</tr>

	</td>
	</tr>
     	<tr>
    		<td><?php print _('Pseudowire Identifier'); ?></td>
    		<td>
    			<input type="text" name="pwid" style='width:200px;'  class="form-control input-sm" placeholder="<?php print _('PWID'); ?>" value="<?php if(isset($circuit->pwid)) print $Tools->strip_xss($circuit->pwid); ?>" <?php print $readonly; ?>>
    		</td>
    	</tr>
     	<tr>
    		<td><?php print _('Route Distinguisher'); ?></td>
    		<td>
    			<input type="text" name="route_distinguisher" style='width:200px;'  class="form-control input-sm" placeholder="<?php print _('Route Distinguisher'); ?>" value="<?php if(isset($circuit->route_distinguisher)) print $Tools->strip_xss($circuit->route_distinguisher); ?>" <?php print $readonly; ?>>
    		</td>
    	</tr>

        <!-- Partner -->
        <tr>
                <td colspan="2"><hr></td>
        </tr>
        <tr>
                <td><?php print _('Partner'); ?></td>
                <td>
                        <select name="partner" class="form-control input-w-auto input-sm">
                                <?php
                                // statuses array
                                $partner = array ("", "Yes", "No");

                                foreach ($partner as $v) {
                                        $selected = $circuit->partner == $v ? "selected" : "";
                                        print "<option value='$v' $selected>$v</option>";
                                }
                                ?>
                        </select>
                </td>
        </tr>
        <tr>
                <td><?php print _('Partner Info'); ?></td>
                <td>
                        <textarea name="partner_info" class="form-control input-sm" <?php print $readonly; ?>><?php if(isset($circuit->partner_info)) print $circuit->partner_info; ?></textarea>
                </td>
        </tr>

	<!-- comment -->
	<tr>
		<td colspan="2"><hr></td>
	</tr>
	<tr>
		<td><?php print _('Comment'); ?></td>
		<td>
			<textarea name="comment" class="form-control input-sm" <?php print $readonly; ?>><?php if(isset($circuit->comment)) print $circuit->comment; ?></textarea>
		</td>
	</tr>


	<!-- Custom -->
	<?php
	if(sizeof($custom) > 0) {

		print '<tr>';
		print '	<td colspan="2"><hr></td>';
		print '</tr>';

		# count datepickers
		$timepicker_index = 0;

		# all my fields
		foreach($custom as $field) {
			// readonly
			$disabled = $readonly == "readonly" ? true : false;
    		// create input > result is array (required, input(html), timepicker_index)
    		$custom_input = $Tools->create_custom_field_input ($field, $circuit, $timepicker_index, $disabled);
    		$timepicker_index = $custom_input['timepicker_index'];
            // print
			print "<tr>";
			print "	<td>".ucwords($Tools->print_custom_field_name ($field['name']))." ".$custom_input['required']."</td>";
			print "	<td>".$custom_input['field']."</td>";
			print "</tr>";
		}
	}


	?>

	</table>
	</form>
</div>

<!-- footer -->
<div class="pFooter">
	<div class="btn-group">
		<button class="btn btn-sm btn-default hidePopups"><?php print _('Cancel'); ?></button>
		<button class="btn btn-sm btn-default submit_popup <?php if($_POST['action']=="delete") { print "btn-danger"; } else { print "btn-success"; } ?>" data-script="app/admin/circuits/edit-customer-circuit-submit.php" data-result_div="circuitManagementEditResult" data-form='circuitManagementEdit'>
			<i class="fa <?php if($_POST['action']=="add") { print "fa-plus"; } else if ($_POST['action']=="delete") { print "fa-trash-o"; } else { print "fa-check"; } ?>"></i>
			<?php print ucwords(_($_POST['action'])); ?>
		</button>
	</div>

	<!-- result -->
	<div class='circuitManagementEditResult' id="circuitManagementEditResult"></div>
</div>
