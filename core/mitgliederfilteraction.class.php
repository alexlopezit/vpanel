<?php

require_once(VPANEL_CORE . "/aktion.class.php");

abstract class MitgliederFilterAction extends Aktion {
	private $actionid;

	public function __construct($actionid, $label, $permission) {
		parent::__construct($label, $permission);
		$this->actionid = $actionid;
	}

	public function getActionID() {
		return $this->actionid;
	}

	protected function executeProcess($session, $process, $filter, $matcher) {
		$process->setMatcher($matcher);
		$process->setUser($session->getUser());
		// Zwischenspeichern, um ProzessID zu erhalten
		$process->save();

		// Viele Prozesse finden erst zur Laufzeit ihr Ziel
		if ($process->getFinishedPage() == null) {
			$process->setFinishedPage($session->getLink("mitglieder_filterprocess", $this->getActionID(), $process->getProcessID()));
			$process->save();
		}

		if ($session->getStorage()->getMitgliederCount($matcher) < 5) {
			$process->run();
			return array("redirect" => $process->getFinishedPage());
		} else {
			return array("redirect" => $session->getLink("processes_view", $process->getProcessID()));
		}
	}

	abstract public function execute($config, $session, $filter, $matcher);
	abstract public function show($config, $process, $matcher);
}

?>
