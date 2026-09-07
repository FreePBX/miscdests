<?PHP
// vim: set ai ts=4 sw=4 ft=php:
/*
 * License for all code of this FreePBX module can be found in the license file inside the module directory
 * Copyright (C) 2014 Schmooze Com Inc.
 * Copyright (c) 2016 - 2018 Sangoma Technologies
 */

namespace FreePBX\modules;
use BMO;
use FreePBX_Helpers;
use PDO;
class Miscdests extends FreePBX_Helpers implements BMO {
	/** @var \FreePBX */
	public $FreePBX;

	/** @var \FreePBX\Database|PDO */
	public $Database;

	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new \Exception("Not given a FreePBX Object");
		}
		$this->FreePBX = $freepbx;
		$this->Database = $freepbx->Database;
	}
	public function install() {}
	public function uninstall() {}

	public function doConfigPageInit($page) {
		$request = $_REQUEST;
		isset($request['extdisplay'])?$extdisplay = $request['extdisplay']:$extdisplay='';
		isset($request['description'])?$description = $request['description']:$description ='';
		isset($request['destdial'])?$destdial = $request['destdial']:$destdial ='';
		isset($request['view'])?$view = $request['view']:$view='';
		isset($request['action'])?$action = $request['action']:$action='';
		switch ($action) {
			case "add":
				$extdisplay = $this->add($request['description'] ?? '', $request['destdial'] ?? '');
				needreload();
				redirect_standard();
				break;
			case "delete":
				$this->del($request['extdisplay'] ?? '');
				needreload();
				redirect_standard();
				break;
			case "edit":
				$this->update($request['extdisplay'] ?? '', $request['description'] ?? '', $request['destdial'] ?? '');
				needreload();
				redirect_standard('extdisplay', 'view');
			break;
		}
	}
	public function doDialplanHook(&$ext, $engine, $priority) {
		$contextname = 'ext-miscdests';
		$fctemplate = '/\{(.+)\:(.+)\}/';
		if(is_array($destlist = $this->mdlist())) {
			foreach($destlist as $item) {
				$miscdest = $this->get($item['0'])[0] ?? null;
				if ($miscdest === null) {
					continue;
				}
				$miscid = $miscdest['id'];
				$miscdescription = $miscdest['description'];
				$miscdialdest = $miscdest['destdial'];
				// exchange {mod:fc} for the relevent feature codes in $miscdialdest
				$miscdialdest = preg_replace_callback($fctemplate, array($this, 'lookupfc'), $miscdialdest);
				// write out the dialplan details
				$ext->add($contextname, $miscid, '', new \ext_noop('MiscDest: '.$miscdescription));
				$ext->add($contextname, $miscid, '', new \ext_goto('from-internal,'.$miscdialdest.',1', ''));
			}
		}
	}
	public function getActionBar($request) {
		$buttons = array();
		switch($request['display'] ?? '') {
			case 'miscdests':
				$buttons = array(
					'delete' => array(
						'name' => 'delete',
						'id' => 'delete',
						'value' => _('Delete')
					),
					'reset' => array(
						'name' => 'reset',
						'id' => 'reset',
						'value' => _('Reset')
					),
					'submit' => array(
						'name' => 'submit',
						'id' => 'submit',
						'value' => _('Submit')
					)
				);
				if (empty($request['extdisplay'])) {
					unset($buttons['delete']);
				}
				if (!isset($request['view'])){
					$buttons = array();
				}
			break;
		}
		return $buttons;
	}
	// returns a associative arrays with keys 'destination' and 'description'
	public function destinations() {
		$results = $this->mdlist();
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

	public function getdest($exten) {
		return array('ext-miscdests,'.$exten.',1');
	}

	public function getdestinfo($dest) {
		global $active_modules;

		if (substr(trim($dest),0,14) == 'ext-miscdests,') {
			$exten = explode(',',$dest);
			$exten = $exten[1];
			$thisexten = $this->get($exten)[0] ?? null;
			if (empty($thisexten)) {
				return array();
			} else {
				//$type = isset($active_modules['announcement']['type'])?$active_modules['announcement']['type']:'setup';
				return array('description' => sprintf(_("Misc Destination: %s"),$thisexten['description']),
							 'edit_url' => 'config.php?display=miscdests&view=form&extdisplay='.urlencode($exten),
									  );
			}
		} else {
			return false;
		}
	}

	public function mdlist($all = false) {
        $db = $this->Database;
        $sql = "SELECT id, description FROM miscdests ORDER BY description";
        if ($all) {
            $sql = 'SELECT * FROM miscdests ORDER BY description';
        }
		$q = $db->prepare($sql);
		$q->execute();
		$extens = array();

		if($q){
            if($all){
                return $q->fetchAll(PDO::FETCH_ASSOC);
            }

            $results = $q->fetchAll(PDO::FETCH_ASSOC);

			foreach($results as $result){
				$extens[] = array($result['id'],$result['description']);
			}
		}
		if (!empty($extens)) {
			return $extens;
		} else {
			return null;
		}
	}
	public function getallmd($id="") {
		$db = $this->Database;
		$sql = "SELECT description FROM miscdests";
		if ($id) {
			$sql .= " where  id != :id ";
		}
		$q = $db->prepare($sql);
		if ($id) {
			$q->execute(array(":id" => $id));
		}else {
			$q->execute();
		}
		$allmd = array();
		if($q){
			$results = $q->fetchAll(PDO::FETCH_ASSOC);
			foreach($results as $result){
				$allmd[] = $result['description'];
			}
		}
		return $allmd;
	}

	public function get($id){
		$db = $this->Database;
		$sql = "SELECT id, description, destdial FROM miscdests WHERE id = ?";
		$q = $db->prepare($sql);
		$q->execute(array($id));
		if($q){
			$results = $q->fetchAll(PDO::FETCH_ASSOC);
			return $results;
		}
		return false;
	}

	public function del($id){
		$db = $this->Database;
		$sql = "DELETE FROM miscdests WHERE id = ?";
		$q = $db->prepare($sql);
		$q->execute(array($id));
		if($q){
			return $q->rowCount();
		}
		return false;
	}

	public function add($description, $destdial){
		$db = $this->Database;
		$sql = "INSERT INTO miscdests (description, destdial) VALUES (?,?)";
		$q = $db->prepare($sql);
		$q->execute(array($description,trim($destdial)));
		return $db->lastInsertId();
	}

    public function upsert($id, $description, $destdial){
        $this->Database->prepare('REPLACE INTO miscdests (`id`, `description`, `destdial`) VALUES (:id, :description, :destdial)')
            ->execute([':id' => $id, ':description' => $description, ':destdial' => $destdial]);
        return $this;
    }

	public function update($id, $description, $destdial){
		$db = $this->Database;
		$sql = "UPDATE miscdests SET description = ?, destdial = ? WHERE id = ?";
		debug('*******Update ID: ' . $id . 'description / destdial = ' . $description . ' / ' . $destdial);
		$q = $db->prepare($sql);
		$q->execute(array($description,trim($destdial),$id));
		if($q){
			debug($q->rowCount());
			return $q->rowCount();
		}
		return false;
	}

	public function lookupfc($matches) {
		$modulename = $matches[1];
		$featurename = $matches[2];

		$fcc = new \featurecode($modulename, $featurename);
		$fc = $fcc->getCodeActive();
		return $fc;
	}
	public function ajaxRequest($req, &$setting) {
			switch ($req) {
					case 'getJSON':
							return true;
					break;
					default:
							return false;
					break;
			}
	}
	public function ajaxHandler(){
		switch ($_REQUEST['command'] ?? '') {
			case 'getJSON':
				switch ($_REQUEST['jdata'] ?? '') {
					case 'grid':
						$mdl = $this->mdlist();
						$mdl = is_array($mdl)?$mdl:array();
						$ret = array();
						foreach($mdl as $k => $v){
							$ret[] = array ("id" => $v[0], 'description' => $v[1]);
						}
						return $ret;
					break;
					default:
						return false;
					break;
				}
			break;

			default:
				return false;
			break;
		}
	}
	public function getRightNav($request) {
		$html = '';
		if(isset($request['view']) && $request['view'] == 'form'){
    	$html = load_view(__DIR__.'/views/bootnav.php');
		}
    return $html;
	}
}
