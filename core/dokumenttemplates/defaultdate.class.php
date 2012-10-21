<?php

require_once(VPANEL_DOKUMENTTEMPLATES . "/default.class.php");

class DefaultDateDokumentTemplate extends DefaultDokumentTemplate {
	private $dateFormat;

	public function __construct($templateid, $label, $permission, $gliederungid, $kategorieid, $statusid, $identifierPrefix, $dateFormat, $identifierNumberLength = 3) {
		parent::__construct($templateid, $label, $permission, $gliederungid, $kategorieid, $statusid, $identifierPrefix, $identifierNumberLength);
		$this->dateFormat = $dateFormat;
	}

	private function getTimestamp($session) {
		if ($session->hasVariable("timestamp")) {
			return strtotime($session->getVariable("timestamp"));
		} else {
			return time();
		}
	}

	protected function getIdentifierPrefix($session) {
		return parent::getIdentifierPrefix($session) . date($this->dateFormat, $this->getTimestamp($session));
	}
}

?>
