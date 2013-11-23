<?php echo "Overall Average of all scores : ". round($populationAvg,2) ."<br>
		(excludes data from defensive position players)."; ?>
<p>
<table class="DataTable">
	<tr class='row rowHead'>
		<th colspan="8">Player Data</th>
		<th colspan="2">Next Week Projection</th>
	</tr>
	<tr class='row rowHead'>
		<th class='headingCell, firstCol'>Name</th>
		<th class='headingCell'>Pos</th>
		<th class='headingCell'>NFL Team</th>
		<th class='headingCell'>Bye Week</th>
		<th class='headingCell'>FF Team</th>
		<th class='headingCell'>Avg. Score</th>
		<th class='headingCell'>Rating</th>
		<th class='headingCell'>Owned</th>
		<th class='headingCell'>Proj</th>
		<th class='headingCell'>Vs. Opp</th>
	</tr>
	<?php $rowShade = 0; ?>
	<?php  foreach($playerSummarys as $player): ?>
		<?php 
			
			if ($rowShade == 0) {
			echo "<tr class='row lightRow'>";
			$rowShade = 1;
		}
		else{
			echo "<tr class='row darkRow'>";
			$rowShade = 0;
		}
		?>	
		<td class='dataCell, firstCol'><?php echo htmlspecialchars($player['fullName']); ?>&nbsp;</td>
		<td class='dataCell'><?php echo htmlspecialchars($player['pos']); ?>&nbsp;</td>
		<td class='dataCell'><?php echo htmlspecialchars($player['nflTeam']->Abvr); ?>&nbsp;</td>
		<td class='dataCell'><?php echo htmlspecialchars($player['bye']); ?>&nbsp;</td>
		<?php 
			if (empty($player['fTeam']->Abbr)) {
				echo "<td class='dataCell' style='background-color: #FFFF00;'>";
				echo "F&#47;A";
			}
			else{
				echo "<td class='dataCell'>";
				echo htmlspecialchars($player['fTeam']->Abbr);
			}
			 ?>&nbsp;
		</td>
		<td class='dataCell' <?php gbi($player['scale']); ?>>
			<?php echo htmlspecialchars(round($player['playerAvg'],2)); ?>&nbsp;
		</td>
		<td class='dataCell'><?php echo htmlspecialchars($player['rating']); ?>&nbsp;</td>
		<td class='dataCell'><?php echo ""; ?>99&#37; </td>
		<td class='dataCell'>15&nbsp;</td>
		<td class='dataCell'>31st</td>
	</tr>
		
	<?php endforeach; ?>
	
</table>
</p>