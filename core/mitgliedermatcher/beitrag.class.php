<?php

require_once(VPANEL_CORE . "/mitgliederfilter.class.php");

class BeitragMitgliederMatcher extends MitgliederMatcher {
	private $beitragid;
	
	public function __construct($beitragid) {
		$this->beitragid = $beitragid;
	}

	public function getBeitragID() {
		return $this->beitragid;
	}

	public function match(Mitglied $mitglied) {
		$beitrag = $mitglied->getBeitrag($this->getBeitragID());
		if ($beitrag == null) {
			return false;
		}
		return true;
	}
}

class BeitragMissingMitgliederMatcher extends MitgliederMatcher {
	private $beitragid;
	
	public function __construct($beitragid) {
		$this->beitragid = $beitragid;
	}

	public function getBeitragID() {
		return $this->beitragid;
	}

	public function match(Mitglied $mitglied) {
		$beitrag = $mitglied->getBeitrag($this->getBeitragID());
		if ($beitrag == null) {
			return false;
		}
		return $beitrag->getBezahlt() < $beitrag->getHohe();
	}
}

class BeitragPaidMitgliederMatcher extends MitgliederMatcher {
	private $beitragid;
	
	public function __construct($beitragid) {
		$this->beitragid = $beitragid;
	}

	public function getBeitragID() {
		return $this->beitragid;
	}

	public function match(Mitglied $mitglied) {
		$beitrag = $mitglied->getBeitrag($this->getBeitragID());
		if ($beitrag == null) {
			return false;
		}
		return $beitrag->getBezahlt() >= $beitrag->getHohe();
	}
}

class BeitragMissingAboveMitgliederMatcher extends MitgliederMatcher {
	private $beitragmark;

	public function __construct($beitragmark = 0) {
		$this->beitragmark = $beitragmark;
	}

	public function getBeitragMark() {
		return $this->beitragmark;
	}

	public function match(Mitglied $mitglied) {
		$beitragmissing = 0;
		foreach ($mitglied->getBeitragList() as $beitrag) {
			$beitragmissing += $beitrag->getHoehe() - $beitrag->getBezahlt();
		}
		return $beitragmissing > $this->getBeitragMark();
	}
}

class BeitragMissingBelowMitgliederMatcher extends MitgliederMatcher {
	private $beitragmark;

	public function __construct($beitragmark = 0) {
		$this->beitragmark = $beitragmark;
	}

	public function getBeitragMark() {
		return $this->beitragmark;
	}

	public function match(Mitglied $mitglied) {
		$beitragmissing = 0;
		foreach ($mitglied->getBeitragList() as $beitrag) {
			$beitragmissing += $beitrag->getHoehe() - $beitrag->getBezahlt();
		}
		return $beitragmissing <= $this->getBeitragMark();
	}
}

?>