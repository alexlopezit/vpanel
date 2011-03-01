<?php

require_once(VPANEL_CORE . "/globalobject.class.php");
require_once(VPANEL_CORE . "/mitgliedrevision.class.php");

class Mitglied extends GlobalClass {
	private $mitgliedid;
	private $eintrittsdatum;
	private $austrittsdatum;
	
	private $revisions = array();
	private $loadedRevisions = false;
	
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

	public function isAusgetreten() {
		return $this->austrittsdatum != null;
	}

	public function isMitglied() {
		return !$this->isAusgetreten();
	}

	public function getRevisionList() {
		if (!$this->loadedRevisions) {
			$this->revisions = $this->getStorage()->getMitgliederRevisionsByMitgliedIDList($this->getMitgliedID());
			$this->loadedRevisions = true;
		}
		return $this->revisions;
	}
	
	public function &getRevision($revisionid) {
		if (!isset($this->revisions[$revisionid]) or $this->revisions[$revisionid] == null) {
			$this->revisions[$revisionid] = $this->getStorage()->getMitgliederRevision($revisionid);
		}
		return $this->revisions[$revisionid];
	}

	public function setRevision($revision) {
		$this->revisions[$revision->getRevisionID()] = $revision;
	}

	public function getLatestRevision() {
		return $this->getRevision(reset($this->getRevisionIDs()));
	}
	
	public function getRevisionIDs() {
		return array_map(create_function('$a', 'return $a->getRevisionID();'), $this->revisions);
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
	}
	
	private function getVariableValue($keyword) {
		$keyword = strtoupper($keyword);

		$revision = $this->getLatestRevision();
		$kontakt = $revision->getKontakt();
		switch ($keyword) {
		case "MITGLIEDID":
			return $this->getMitgliedID();
		case "EINTRITT":
			return date("d.m.Y", $this->getEintrittsdatum());
		case "AUSTRITT":
			return date("d.m.Y", $this->getAustrittsdatum());
		case "STRASSE":
			return $kontakt->getStrasse();
		case "HAUSNUMMER":
			return $kontakt->getHausnummer();
		case "PLZ":
			return $kontakt->getOrt()->getPLZ();
		case "ORT":
			return $kontakt->getOrt()->getLabel();
		case "STATE":
			return $kontakt->getOrt()->getState()->getLabel();
		case "COUNTRY":
			return $kontakt->getOrt()->getState()->getCountry()->getLabel();
		case "TELEFONNUMMER":
			return $kontakt->getTelefonnummer();
		case "HANDYNUMMER":
			return $kontakt->getHandynummer();
		case "EMAIL":
			return $kontakt->getEMail();
		case "MITGLIEDSCHAFT":
			return $revision->getMitgliedschaft()->getLabel();
		case "BEITRAG":
			return $revision->getBeitrag();
		}
		if ($revision->isNatPerson()) {
			$natperson = $revision->getNatPerson();
			switch ($keyword) {
			case "BEZEICHNUNG":
				return $natperson->getVorname() . " " . $natperson->getName();
			case "NAME":
				return $natperson->getName();
			case "VORNAME":
				return $natperson->getVorname();
			case "GEBURTSDATUM":
				return date("d.m.Y", $natperson->getGeburtsdatum());
			case "NATIONALITAET":
				return $natperson->getNationalitaet();
			}
		}
		if ($revision->isJurPerson()) {
			$jurperson = $revision->getJurPerson();
			switch ($keyword) {
			case "BEZEICHNUNG":
				return $jurperson->getLabel();
			case "FIRMA":
				return $natperson->getLabel();
			}
		}
		return "";
	}

	public function replaceText($text) {
		// Suche alle vorkommenden Variablen ab
		preg_match_all('/\\{(.*?)\\}/', $text, $matches);
		$keywords = array_unique($matches[1]);
		foreach ($keywords as $keyword) {
			$text = str_replace("{" . $keyword . "}", $this->getVariableValue($keyword), $text);
		}
		return $text;
	}
}

?>
