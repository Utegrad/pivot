<?php
	class FfPosition{	
	function __construct() {
		require_once('FfStatsGroup.php');
		$this->PositionGroup = new FfStatsGroup();
	}
	
	public $PositionGroup = null;
	public $Abbr;
	public $Name;
	public $Desc;
	public $relivantPositions = array("QB", "RB", "WR", "TE", "DST", "K");
	
	public static function withJson(stdClass $position){
		$instance = new self();
		$instance->loadByJson($position);
		return $instance;
	}
	
	protected function loadByJson(stdClass $position){
		$this->Abbr = $position->abbr;
		$this->Name = $position->name;
		$this->Desc = $position->description;
		switch($position->stats_group){
			case 'Offensive':
				$this->PositionGroup->FF_StatsGroupName = $position->stats_group;
				$this->PositionGroup->FF_StatsGroupId = 3;
				break;
			case 'Defensive':
				$this->PositionGroup->FF_StatsGroupName = $position->stats_group;
				$this->PositionGroup->FF_StatsGroupId = 4;
				break;
			default:
				$this->PositionGroup->FF_StatsGroupName = 'Other';
				$this->PositionGroup->FF_StatsGroupId = 3;
				break;
		}
		
	}
	}
?>