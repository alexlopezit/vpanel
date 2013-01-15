<?php

require_once(VPANEL_DOKUMENTTEMPLATES . "/identifier.class.php");

class SelectPrefixDateDokumentTemplate extends IdentifierDokumentTemplate {
	private $prefixes;
	private $dateFormat;
	private $dateField;

	public function __construct($templateid, $label, $permissoin, $gliederungid, $prefixes, $dateFormat, $dateField, $identifierNumberLength = 3) {
		parent::__construct($templateid, $label, $permissoin, $gliederungid, $identifierNumberLength);
		$this->prefixes = $prefixes;
		$this->dateFormat = $dateFormat;
		$this->dateField = $dateField;
	}

	public function getDateFieldLabel() {
		return $this->dateField;
	}

	public function getPrefixOptions() {
		return array_map(create_function('$a', 'return $a["label"];'), $this->prefixes);
	}

	private function getTimestamp($session) {
		return $session->getTimestampVariable("timestamp");
	}

	private function getSelectedOption($session, $value) {
		return $this->prefixes[$session->getVariable("option")][$value];
	}

	private function getSelectedPrefix($session) {
		return $this->getSelectedOption($session, "prefix");
	}

	protected function getIdentifierPrefix($session) {
		return $this->getSelectedPrefix($session) . date($this->dateFormat, $this->getTimestamp($session));
	}

	public function getDokumentKategorieID($session) {
		return $this->getSelectedOption($session, "kategorieid");
	}

	public function getDokumentStatusID($session) {
		return $this->getSelectedOption($session, "statusid");
	}

	public function getDokumentFlags($session) {
		return $this->getSelectedOption($session, "flags");
	}

	public function getDokumentLabel($session) {
		return $session->getVariable("label");
	}

	public function getDokumentFile($session) {
		return $session->getFileVariable("file");
	}

	public function getDokumentData($session) {
		return array();
	}

	public function getDokumentKommentar($session) {
		return $session->getVariable("kommentar");
	}
}

?>
