<?php
namespace FreePBX\modules\Miscdests;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
	public function runRestore(){
		$configs = $this->getConfigs();
		foreach ($configs ?? [] as $miscdests) {
			if (!isset($miscdests['id'], $miscdests['description'], $miscdests['destdial'])) {
				continue;
			}
				$this->FreePBX->Miscdests->upsert((int)$miscdests['id'], $miscdests['description'], $miscdests['destdial']);
		}
	}
	public function processLegacy($pdo, $data, $tables, $unknownTables){
		$this->restoreLegacyDatabase($pdo);
	}
}
