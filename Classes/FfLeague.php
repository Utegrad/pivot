<?php
/** Represents FF League data from CBS Sports API and the ff_leagues table
 * 
 */
class FfLeague{
	function __construct(){
		
	}
	
    /** @var int $Id */
	public $Id;
    /** @var string $Name */
	public $Name;
	
	public $RegularSeasonPeriods;
	
	public $PlayoffPeriods;
	
	public $NumTeams;
	/** @var string $Commissioner char(36) value from ff_owners table and CBS Sports API */
	public $Commissioner;
	
	public $SeasonStartDate;
	
	public $SeasonEndDate;
	
	
	
    /**
     * Instantiate self() with data from CBS Sports FF League Details data
     * 
     * @param stdClass $league CBS Sports Json data for FF League Details
     * @return object
     */
	public static function withLeagueJson(stdClass $league){
		$instance = new self();
		$instance->loadByLeagueJson($league);
		return $instance;
	}
	protected function loadByLeagueJson(stdClass $league){
		$this->RegularSeasonPeriods = $league->regular_season_periods;
		$this->PlayoffPeriods = $league->playoff_periods;
		$this->NumTeams = $league->num_teams;
		$this->Name = $league->name;
	}
}
?>