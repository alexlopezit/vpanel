<?php

require_once(VPANEL_CORE . "/globalobject.class.php");
require_once(VPANEL_CORE . "/mitgliedrevision.class.php");

class Mitglied extends GlobalClass {
	private $mitgliedid;
	private $eintrittsdatum;
	private $austrittsdatum;

	private $beitraglist;

	private $revisions;
	private $latestRevision;

	public static function factory(Storage $storage, $row) {
		$mitglied = new Mitglied($storage);
		$mitglied->setMitgliedID($row["mitgliedid"]);
		$mitglied->setGlobalID($row["globalid"]);
		$mitglied->setEintrittsdatum($row["eintritt"]);
		$mitglied->setAustrittsdatum($row["austritt"]);
		return $mitglied;
	}

	public function getMitgliedID() {
		return $this->mitgliedid;
	}

	public function setMitgliedID($mitgliedid) {
		$this->mitgliedid = $mitgliedid;
	}

	public function getEintrittsdatum() {
		return $this->eintrittsdatum;
	}

	public function setEintrittsdatum($eintrittsdatum) {
		$this->eintrittsdatum = $eintrittsdatum;
	}

	public function getAustrittsdatum() {
		return $this->austrittsdatum;
	}

	public function setAustrittsdatum($austrittsdatum) {
		$this->austrittsdatum = $austrittsdatum;
	}

	public function hasAustrittsdatum() {
		return $this->austrittsdatum != null;
	}

	public function isAusgetreten() {
		return $this->austrittsdatum != null && $this->austrittsdatum < time();
	}

	public function isMitglied() {
		return !$this->isAusgetreten();
	}

	public function getRevisionList() {
		if ($this->revisions === null) {
			$this->revisions = array();
			foreach ($this->getStorage()->getMitgliederRevisionsByMitgliedIDList($this->getMitgliedID()) as $revision) {
				$this->addRevision($revision);
			}
		}
		return $this->revisions;
	}

	public function &getRevision($revisionid) {
		$this->getRevisionList();
		if (!isset($this->revisions[$revisionid]) or $this->revisions[$revisionid] == null) {
			$this->revisions[$revisionid] = $this->getStorage()->getMitgliederRevision($revisionid);
		}
		return $this->revisions[$revisionid];
	}

	public function addRevision($revision) {
		$this->getRevisionList();
		$this->revisions[$revision->getRevisionID()] = $revision;
		if (!isset($this->latestRevision) || $revision->getTimestamp() > $this->latestRevision->getTimestamp()) {
			$this->latestRevision = $revision;
		}
	}

	public function getLatestRevision() {
		if (!isset($this->latestRevision)) {
			$this->latestRevision = $this->getRevision(end($this->getRevisionIDs()));
		}
		return $this->latestRevision;
	}

	public function setLatestRevision($revision) {
		$this->latestRevision = $revision;
	}

	public function getRevisionIDs() {
		return array_map(create_function('$a', 'return $a->getRevisionID();'), $this->getRevisionList());
	}

	public function getBeitragList() {
		if ($this->beitraglist === null) {
			$this->beitraglist = array();
			foreach ($this->getStorage()->getMitgliederBeitragByMitgliedList($this->getMitgliedID()) as $beitrag) {
				$this->beitraglist[$beitrag->getBeitragID()] = $beitrag;
			}
		}
		return $this->beitraglist;
	}

	public function hasBeitrag($beitragid) {
		$this->getBeitragList();
		return isset($this->beitraglist[$beitragid]);
	}

	public function getBeitrag($beitragid) {
		if (! $this->hasBeitrag($beitragid)) {
			$this->beitraglist[$beitragid] = new MitgliedBeitrag($this->getStorage());
			$this->beitraglist[$beitragid]->setMitglied($this);
			$this->beitraglist[$beitragid]->setBeitragID($beitragid);
		}
		return $this->beitraglist[$beitragid];
	}

	public function delBeitrag($beitragid) {
		$this->getBeitragList();
		unset($this->beitraglist[$beitragid]);
	}

	public function getPaidBeitrag() {
		return array_reduce($this->getBeitragList(), create_function('$value, $beitrag', 'return $value + $beitrag->getBuchungenHoehe();'), 0);
	}

	public function getSchulden() {
		return array_reduce($this->getBeitragList(), create_function('$value, $beitrag', 'return $value + $beitrag->getRemainingHoehe();'), 0);
	}

	public function save(Storage $storage = null) {
		if ($storage === null) {
			$storage = $this->getStorage();
		}
		$this->setMitgliedID( $storage->setMitglied(
			$this->getMitgliedID(),
			$this->getGlobalID(),
			$this->getEintrittsdatum(),
			$this->getAustrittsdatum() ));
		// TODO revisions speichern ?

		if ($this->beitraglist !== null) {
			foreach ($this->beitraglist as $beitrag) {
				$beitrag->save($storage);
			}
		}
	}
}

?>
