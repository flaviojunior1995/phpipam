<?php

# Check we have been included and not called directly
require( dirname(__FILE__) . '/../../../functions/include-only.php' );

?>
<ul class='nav nav-tabs' style='margin-top:0px;margin-bottom:20px;'>
	<li role='presentation' <?php if(!isset($_GET['subnetId'])||is_numeric($_GET['subnetId'])) print " class='active'"; ?>>
		<a href='<?php print create_link($_GET['page'], "circuits"); ?>'><?php print _("Network Circuits"); ?></a>
	</li>
	<li role='presentation' <?php if(@$_GET['subnetId']=="dwdm") print " class='active'"; ?>>
		<a href='<?php print create_link($_GET['page'], "circuits", "dwdm"); ?>'><?php print _("DWDM Circuits"); ?></a>
	</li>
	<li role='presentation' <?php if(@$_GET['subnetId']=="darkfiber") print " class='active'"; ?>>
		<a href='<?php print create_link($_GET['page'], "circuits", "darkfiber"); ?>'><?php print _("DarkFiber Circuits"); ?></a>
	</li>
	<li role='presentation' <?php if(@$_GET['subnetId']=="customers") print " class='active'"; ?>>
		<a href='<?php print create_link($_GET['page'], "circuits", "customers"); ?>'><?php print _("Customer Circuits"); ?></a>
	</li>
	<li role='presentation' <?php if(@$_GET['subnetId']=="logical") print " class='active'"; ?>>
		<a href='<?php print create_link($_GET['page'], "circuits", "logical"); ?>'><?php print _("Logical Circuits"); ?></a>
	</li>
	<li role='presentation' <?php if(@$_GET['subnetId']=="providers") print " class='active'"; ?>>
		<a href='<?php print create_link($_GET['page'], "circuits", "providers"); ?>'><?php print _("Circuit providers"); ?></a>
	</li>
 	<?php if($User->is_admin(false)) { ?>
 	<li role='presentation' <?php if(@$_GET['subnetId']=="options") print " class='active'"; ?>>
		<a href='<?php print create_link($_GET['page'], "circuits", "options"); ?>'><?php print _("Options"); ?></a>
	</li>
    <?php } ?>
</ul>