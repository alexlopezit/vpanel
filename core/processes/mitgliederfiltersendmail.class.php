<?php

require_once(VPANEL_PROCESSES . "/mitgliederfilter.class.php");

class MitgliederFilterSendMailProcess extends MitgliederFilterProcess {
	private $templateid;

	private $template;

	public static function factory(Storage $storage, $row) {
		$process = parent::factory($storage, $row);
		$process->setTemplate($row["template"]);
		return $process;
	}

	public function getTemplate() {
		return $this->template;
	}

	public function setTemplate($template) {
		$this->template = $template;
	}

	protected function getData() {
		$data = parent::getData();
		$data["template"] = $this->getTemplate();
		return $data;
	}

	protected function runProcessStep($mitglied) {
		global $config;
		$mail = $this->getTemplate()->generateMail($mitglied);
		$config->sendMail($mail);
	}
}

?>
