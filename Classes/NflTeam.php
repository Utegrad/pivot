<?php
class NflTeam{
	function __construct() {
	
	}
	public $TeamID;
	public $Name;
	public $NickName;
	public $Abvr;
	
	public static function withJson(stdClass $team){
		$instance = new self();
		$instance->loadByJson($team);
		return $instance;
	}
	protected function loadByJson(stdClass $team){
		$this->Name = $team->name;
		$this->NickName = $team->nickname;
		$this->Abvr = $team->abbr;
	}
}
?>