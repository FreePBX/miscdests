<?php /* $Id: $ */

// returns a associative arrays with keys 'destination' and 'description'
function miscdests_destinations() {
	$results = miscdests_list();

	// return an associative array with destination and description
	if (isset($results)) {
		foreach($results as $result){
				$extens[] = array('destination' => 'ext-miscdests,'.$result['0'].',1', 'description' => $result['1']);
		}
		return $extens;
	} else {
		return null;
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
			if(is_array($destlist = miscdests_list())) {
				
				foreach($destlist as $item) {
					$miscdest = miscdests_get($item['0']);
					
					$miscid = $miscdest['id'];
					$miscdescription = $miscdest['description'];
					$miscdialdest = $miscdest['destdial'];

					$ext->add($contextname, $miscid, '', new ext_noop('MiscDest: '.$miscdescription));
					$ext->add($contextname, $miscid, '', new ext_dial('Local/'.$miscdialdest.'@from-internal', ''));
					
				}
			}

		break;
	}
}

function miscdests_list() {
	$results = sql("SELECT id, description FROM miscdests ORDER BY description","getAll",DB_FETCHMODE_ASSOC);
	foreach($results as $result){
		$extens[] = array($result['id'],$result['description']);
	}

	if (isset($extens)) {
		return $extens;
	} else {
		return null;
	}
}

function miscdests_get($id){
	$results = sql("SELECT id, description, destdial FROM miscdests WHERE id = $id","getRow",DB_FETCHMODE_ASSOC);
	return $results;
}

function miscdests_del($id){
	$results = sql("DELETE FROM miscdests WHERE id = $id","query");
}

function miscdests_add($description, $destdial){
	$results = sql("INSERT INTO miscdests (description, destdial) VALUES (".sql_formattext($description).",".sql_formattext($destdial).")");
}

function miscdests_update($id, $description, $destdial){
	$results = sql("UPDATE miscdests SET description = ".sql_formattext($description).", destdial = ".sql_formattext($destdial)." WHERE id = ".$id);
}

?>