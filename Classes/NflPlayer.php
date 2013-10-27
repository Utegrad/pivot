<?php

class NFLPlayer{
	function __construct() {
		require_once('NflTeam.php');
		$this->NFLTeam = new NflTeam();
		require_once('FfPosition.php');
		$this->Position = new FfPosition();
	}
	
	public $Id;
	public $FullName;
	public $Pos;
	public $Status;
	public $FirstName;
	public $LastName;
	public $ByeWeek;
	public $NFLTeam;
	public $Position;
	
	public static function withJson(stdClass $player){
		$instance = new self();
		$instance->loadByJson($player);
		return $instance;
	}
	protected function loadByJson(stdClass $player){
		
		$this->FullName = $player->fullname;
		$this->FirstName = $player->firstname;
		$this->LastName = $player->lastname;
		$this->Id = $player->id;
		$this->Pos = $player->position;
		$this->ByeWeek = ( (empty($player->bye_week)) ? 0 : $player->bye_week );
		$this->NFLTeam->Abvr = $player->pro_team;
		$this->Status = $player->pro_status;
	}
}

?>