<?php

class NFLPlayer{
	function __construct() {
		require_once('NflTeam.php');
		$this->NFLTeam = new NflTeam();
		require_once('FfPosition.php');
		$this->Position = new FfPosition();
        require_once('FfTeam.php');
        $this->FfTeam = new FfTeam();
	}
	
    /** @var int $Id nfl_players id */
	public $Id;
	public $FullName;
    /** @var string $Pos position abbr from CBS Sports */
	public $Pos;
	public $Status;
	public $FirstName;
	public $LastName;
    /** @var int $ByeWeek bye_week data from CBS Sports */
	public $ByeWeek;
    /** @var NflTeam $NFLTeam object from Classes/NflTeam.php */
	public $NFLTeam;
    /** @var FfPostion $Position object from Classes/FfPosition.php */
	public $Position;
    /** @var FfTeam object from Classes/FfTeam.php */
    public $FfTeam;
    
	/** Allows creation of object by passing Json data to method
     * @return object self()
     * @param stdClass Json data from CBS Sports API data from Utility.php CBSSports::GetData() 
     */
	public static function withJson(stdClass $player){
		$instance = new self();
		$instance->loadByJson($player);
		return $instance;
	}
	protected function loadByJson(stdClass $player){
		
		$this->FullName =         $player->fullname;
		$this->FirstName =        $player->firstname;
		$this->LastName =         $player->lastname;
		$this->Id =               $player->id;
		$this->Pos =              $player->position;
		$this->ByeWeek = ( (empty($player->bye_week)) ? 0 : $player->bye_week );
		$this->NFLTeam->Abvr =    $player->pro_team;
		$this->Status =           $player->pro_status;
	}
    
    /**
     * Loads FF Team values to contained object with Json nfl_player_profile data
     * @param stdClass profile nfl_player_profile object from cURL / CBS Sports
     */
    public function loadFfTeamByJson(stdClass $profile){
        $this->FfTeam->Id =         $profile->owned_by_team->id;
        $this->FfTeam->Abbr =       $profile->owned_by_team->abbr;
        $this->FfTeam->LongAbbr =   $profile->owned_by_team->long_abbr;
        $this->FfTeam->Name =       $profile->owned_by_team->name;
        $this->FfTeam->ShortName =  $profile->owned_by_team->short_name;
        $this->FfTeam->Logo =       $profile->owned_by_team->logo;
    }
}

?>