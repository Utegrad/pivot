<?php echo "Overall Average of all scores : ". round($populationAvg,2) ."<br>
		(excludes data from defensive position players)."; ?>
<p>
<table class="DataTable">
	<tr class='row rowHead'>
		<th class='headingCell'>Name</th>
		<th class='headingCell'>Pos</th>
		<th class='headingCell'>NFL Team</th>
		<th class='headingCell'>Bye Week</th>
		<th class='headingCell'>FF Team</th>
		<th class='headingCell'>Avg. Score</th>
		<th class='headingCell'>Rating</th>
	</tr>
	<?php $rowShade = 0; ?>
	<?php  foreach($playerSummarys as $player): ?>
		<?php if ($rowShade == 0) {
			echo "<tr class='row lightRow'>";
			$rowShade = 1;
		}
		else{
			echo "<tr class='row darkRow'>";
			$rowShade = 0;
		}
		?>	
		<td class='dataCell'><?php echo htmlspecialchars($player['fullName']); ?>&nbsp;</td>
		<td class='dataCell'><?php echo htmlspecialchars($player['pos']); ?>&nbsp;</td>
		<td class='dataCell'><?php echo htmlspecialchars($player['nflTeam']->Abvr); ?>&nbsp;</td>
		<td class='dataCell'><?php echo htmlspecialchars($player['bye']); ?>&nbsp;</td>
		<td class='dataCell'><?php echo htmlspecialchars($player['fTeam']->Abbr); ?>&nbsp;</td>
		<td class='dataCell' <?php gbi($player['scale']); ?>>
			<?php echo htmlspecialchars(round($player['playerAvg'],2)); ?>&nbsp;
		</td>
		<td class='dataCell'><?php echo htmlspecialchars($player['rating']); ?>&nbsp;</td>
	</tr>
		
	<?php endforeach; ?>
	
</table>
</p>