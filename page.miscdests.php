<?php /* $Id: $ */
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015 Sangoma Technologies.
//
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

$md = FreePBX::create()->Miscdests;
$header = _("Misc Destinations");
$helptext = _("Misc Destinations are for adding destinations that can be used by other FreePBX modules, generally used to route incoming calls. If you want to create feature codes that can be dialed by internal users and go to various destinations, please see the <strong>Misc Applications</strong> module.").' '._('If you need access to a Feature Code, such as *98 to dial voicemail or a Time Condition toggle, these destinations are now provided as Feature Code Admin destinations. For upgrade compatibility, if you previously had configured such a destination, it will still work but the Feature Code short cuts select list is not longer provided.<br/><br/>');
$heading = $header;
$request = $_REQUEST;
$view = isset($_REQUEST['view'])?$_REQUEST['view']:'';
$extdisplay = $request['extdisplay'] ?? '';
$usagehtml = '';
switch ($view) {
	case 'form':
		if($extdisplay){
			$heading = _("Edit Misc Destination");
			$usagehtml = FreePBX::View()->destinationUsage(miscdests_getdest($extdisplay));
		}else{
			$heading = _("Add Misc Destination");
		}
		$content = load_view(__DIR__.'/views/form.php', array('request' => $request, 'md' => $md));
	break;
	default:
		$content = load_view(__DIR__.'/views/grid.php', array('request' => $request, 'md' => $md));
	break;
}

?>

<div class="container-fluid">
	<h1><?php echo $heading ?? ''; ?></h1>
	<?php echo $usagehtml?>
	<div class="well well-info">
		<?php echo $helptext?>
	</div>
	<div class = "display full-border">
		<div class="row">
			<div class="col-sm-12">
				<div class="fpbx-container">
					<div class="display full-border">
						<?php echo $content ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
