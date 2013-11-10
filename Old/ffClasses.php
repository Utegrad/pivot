<?php
abstract class FF_App {
	function __construct() {
		
	}
	public $AccessToken = "U2FsdGVkX1_fPq3nW79D6JXMISH39UkPd5fxM2U__Wosz7hZrT7HlZBgUe8rjPiEyu3AcUF53NLuOf8vMHaSppWRzwNSCZ3dIwKWIoCUPfynJvDscEQaA3m3weq4TNSe";
	public $GetURL;
	public $BaseURL = 'http://api.cbssports.com/fantasy/';
	public $Element; # the part of the URL specific to what you're trying to _GET
	public $URLSuffix;
	public $database = array( 'host' => 'localhost',
		'username' => 'FFApplication',
		'password' => 'keep0ut!',
		'database' => 'ff_stats');
	
	
}
class TestApp extends FF_App {
	function __construct() {
		$this->URLSuffix =  "?version=2.0&access_token=". $this->AccessToken ."&response_format=json";
	}
}

class NFL_Team {
	function __construct() {
	
	}
	
	public $TeamID;
	public $Name;
	public $NickName;
	public $Abvr;
}

class FF_Owner {
	function __construct() {
		
	}
	
	public $Id;
	public $Name;
}

class FF_StatsGroup {
	function __construct() {
	}
	
	public $FF_StatsGroupId;
	public $FF_StatsGroupName;
}

class FF_Position {
	function __construct() {
		$this->PositionGroup = new FF_StatsGroup();
	}
	public $PositionGroup = null;
	public $Abbr;
	public $Name;
	public $Desc;
	public $relivantPositions = array("QB", "RB", "WR", "TE", "DST", "K");
	
}

class NFL_Player{
	function __construct() {
		$this->NFLTeam = new NFL_Team();
		$this->Position = new FF_Position();
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
	
}
?>