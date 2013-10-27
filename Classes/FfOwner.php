<?php
class FfOwner{
	function __construct(){
		require_once('FfTeam.php');
		$this->FfTeam = new FfTeam();
	}
	
	public $Id;
	public $Name;
	public $FfTeam;
	
	public static function withJson(stdClass $owner){
		$instance = new self();
		$instance->loadByJson($owner);
		return $instance;
	}
	protected function loadByJson(stdClass $owner){
		$this->Id = $owner->id;
		$this->Name = $owner->name;
	}
}
?>