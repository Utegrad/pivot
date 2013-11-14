<?php
/** Represents FF Owner data from CBS Sports API and the ff_owners table
 * 
 */
class FfOwner{
	function __construct(){
		require_once('FfTeam.php');
		$this->FfTeam = new FfTeam();
	}
	
    /** @var string $Id char(36) value from ff_owners table and CBS Sports API */
	public $Id;
    /** @var string $Name */
	public $Name;
    /** @var FfTeam object from Classes/FfTeam.php */
	public $FfTeam;
	/** @var bool $Commissioner is this owner a commissioner for the league */
	public $Commissioner;
	
    /**
     * Instantiate self() with data from CBS Sports FF Owners data
     * 
     * @param stdClass $owner CBS Sports Json data for FF Owners
     * @return object
     */
	public static function withJson(stdClass $owner){
		$instance = new self();
		$instance->loadByJson($owner);
		return $instance;
	}
	protected function loadByJson(stdClass $owner){
		$this->Id = $owner->id;
		$this->Name = $owner->name;
		$this->Commissioner = $owner->commissioner;
	}
}
?>