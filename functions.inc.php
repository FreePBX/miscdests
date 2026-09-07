<?php /* $Id: $ */
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

// returns a associative arrays with keys 'destination' and 'description'
function miscdests_destinations() {
	$results = miscdests_list();
	$extens = array();

	// return an associative array with destination and description
	if (isset($results)) {
		foreach($results as $result){
			$extens[] = array('destination' => 'ext-miscdests,'.$result['0'].',1', 'description' => $result['1']);
		}
		return !empty($extens) ? $extens : null;
	} else {
		return null;
	}
}

function miscdests_getdest($exten) {
	return array('ext-miscdests,'.$exten.',1');
}

function miscdests_getdestinfo($dest) {
	global $active_modules;

	if (substr(trim($dest),0,14) == 'ext-miscdests,') {
		$exten = explode(',',$dest);
		$exten = $exten[1];
		$thisexten = miscdests_get($exten);
		if (empty($thisexten)) {
			return array();
		} else {
			//$type = isset($active_modules['announcement']['type'])?$active_modules['announcement']['type']:'setup';
			return array(
				'description' => sprintf(_("Misc Destination: %s"),$thisexten['description']),
				'edit_url' => 'config.php?display=miscdests&view=form&extdisplay='.urlencode($exten),
			);
		}
	} else {
		return false;
	}
}

/* 	Generates dialplan for conferences
	We call this with retrieve_conf
*/
function miscdests_get_config($engine) {
	global $ext;  // is this the best way to pass this?

	switch($engine) {
		case "asterisk":
			$contextname = 'ext-miscdests';
			$fctemplate = '/\{(.+)\:(.+)\}/';
			
			if(is_array($destlist = miscdests_list())) {
				
				foreach($destlist as $item) {
					$miscdest = miscdests_get($item['0']);
					if (empty($miscdest)) {
						continue;
					}
					
					$miscid = $miscdest['id'];
					$miscdescription = $miscdest['description'];
					$miscdialdest = $miscdest['destdial'];

					// exchange {mod:fc} for the relevent feature codes in $miscdialdest
					$miscdialdest = preg_replace_callback($fctemplate, "miscdests_lookupfc", $miscdialdest);

					// write out the dialplan details
					$ext->add($contextname, $miscid, '', new ext_noop('MiscDest: '.$miscdescription));
					$ext->add($contextname, $miscid, '', new ext_goto('from-internal,'.$miscdialdest.',1', ''));
					
				}
			}

		break;
	}
}

function miscdests_list() {
	$results = sql("SELECT id, description FROM miscdests ORDER BY description","getAll",DB_FETCHMODE_ASSOC);
	if (!is_array($results)) {
		return null;
	}
	$extens = array();
	foreach($results as $result){
		$extens[] = array($result['id'],$result['description']);
	}

	if (!empty($extens)) {
		return $extens;
	} else {
		return null;
	}
}

function miscdests_get($id){
	global $db;

	$stmt = $db->prepare("SELECT id, description, destdial FROM miscdests WHERE id = ?");
	$stmt->execute(array($id));
	return $stmt->fetch(PDO::FETCH_ASSOC);
}

function miscdests_del($id){
	global $db;

	$stmt = $db->prepare("DELETE FROM miscdests WHERE id = ?");
	return $stmt->execute(array($id));
}

function miscdests_add($description, $destdial){
	global $db;

	$stmt = $db->prepare("INSERT INTO miscdests (description, destdial) VALUES (?, ?)");
	$stmt->execute(array($description, $destdial));
	return $db->lastInsertId();
}

function miscdests_update($id, $description, $destdial){
	global $db;

	$stmt = $db->prepare("UPDATE miscdests SET description = ?, destdial = ? WHERE id = ?");
	return $stmt->execute(array($description, $destdial, $id));
}

function miscdests_lookupfc($matches) {
	$modulename = $matches[1];
	$featurename = $matches[2];

	$fcc = new featurecode($modulename, $featurename);
	$fc = $fcc->getCodeActive();
	return $fc;
}
?>
