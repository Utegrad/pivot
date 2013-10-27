<?php
class FfTeam{
	function __construct(){
		
	}
	
	public $Id;
	public $Name;
	public $ShortName;
	public $Abbr;
	public $LongAbbr;
	public $Owner;
	public $Logo;
	
	public static function withJson(stdClass $ffTeam){
		$instance = new self();
		$instance->loadByJson($ffTeam);
		return $instance;
	}
	protected function loadByJson(stdClass $ffTeam){
		$this->Id = $ffTeam->id;
		$this->Name = $ffTeam->name;
		$this->ShortName = $ffTeam->short_name;
		$this->Abbr = $ffTeam->abbr;
		$this->LongAbbr = $ffTeam->long_abbr;
		$this->Logo = $ffTeam->logo;
		
	}
}
?>