<div>
	<h3>Fantasy Scores Summary</h3>
	<div id="posSelect" class="filter">Include Positions:<br>
		<form>
			<label for="posAll">All</label>&nbsp;<input type="checkbox" name="posAll" value="all">
			<?php 
				$ffPositions = new FfPosition();
				foreach($ffPositions->relivantPositions as $position): ?>
				<label for="pos<?php echo $position; ?>"><?php echo $position; ?></label>&nbsp;<input type="checkbox" name="pos<?php echo $position; ?>" value="<?php echo $position; ?>"> 
			<?php endforeach; ?>
		</form>
	</div>
	<div id="fTeams" class="filter">
		Include Fantasy Teams<br><span style="font-size: smaller; ">&#40;CTRL+Click for multiple&#41;</span><br>
		<?php 
			// need an array of fantasy teams in the current league
		?>
		<form>
			<select multiple="multiple" name="fTeams" id="fTeamsSelect">
				<option value="fTeam1">Team 1</option>
				<option value="fTeam2">Team 2</option>
				<option value="fTeam3">Team 3</option>
				<option value="fTeam4">Team 4</option>
				<option value="fTeam5">Team 5</option>
				<option value="fTeam6">Team 6</option>
				<option value="fTeam8">Team 7</option>
				<option value="fTeam8">Team 8</option>
				<option value="fTeam9">Team 9</option>
				<option value="fTeam10">Team 10</option>
			</select>
		</form>	
	</div>
	<div id="summary">
	NFL Players listed by their average fantasy score.  
	<br>Include controls for sorting and selecting data here.
	</div>
</div>
<div style="clear: both;"></div>

