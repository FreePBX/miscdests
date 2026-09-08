<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015 Sangoma Technologies.
extract($request, EXTR_SKIP);
$extdisplay = $extdisplay ?? '';
if($extdisplay){
	$thisMiscDest = $md->get($extdisplay);
	$thisMiscDest = $thisMiscDest[0] ?? array();
	$description = $thisMiscDest['description'] ?? '';
	$destdial = $thisMiscDest['destdial'] ?? '';
}
?>
<form autocomplete="off" class="fpbx-submit" name="editMD" action="config.php?display=miscdests" onsubmit="return editMD_onsubmit()" method="post" data-fpbx-delete="config.php?display=miscdests&amp;extdisplay=<?php echo $extdisplay ?>&amp;action=delete" role="form">
	<input type="hidden" name="display" value="miscdests">
	<input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add') ?>">
	<input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
	<input type="hidden" name="view" value="form">
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3 control-label">
						<label for="description"><?php echo _("Description:")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="description"></i>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control" id="description" name="description" value="<?php echo isset($description)?$description:''?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="description-help" class="help-block fpbx-help-block"><?php echo _("Give this Misc Destination a brief name to help you identify it.")?></span>
		</div>
	</div>
</div>
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3 control-label">
						<label for="destdial"><?php echo _("Dial:")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="destdial"></i>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control" id="destdial" name="destdial" value="<?php echo isset($destdial)?$destdial:''?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="destdial-help" class="help-block fpbx-help-block"><?php echo _("Enter the number this destination will simulate dialing, exactly as you would dial it from an internal phone. When you route a call to this destination, it will be as if the caller dialed this number from an internal phone.") ?></span>
		</div>
	</div>
</div>
</form>
<script>
var miscdests = <?php print json_encode(\FreePBX::Miscdests()->getallmd($extdisplay)); ?>;
</script>
