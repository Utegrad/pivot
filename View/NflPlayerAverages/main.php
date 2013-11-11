<h2>This is the main section</h2>
<?php echo "Avg: $avg"; ?>
<p><?php echo "StdDev: $sd"; ?></p>
<p><?php  echo "Access Token: $accessToken"; ?></p>

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
	<?php  foreach($nflPlayerAvgs as $player): ?>
		<?php if ($rowShade == 0) {
			echo "<tr class='row lightRow'>";
			$rowShade = 1;
		}
		else{
			echo "<tr class='row darkRow'>";
			$rowShade = 0;
		}
		?>	
		<td class='dataCell'><?php echo htmlspecialchars($player['player_name']); ?>&nbsp;</td>
		<td class='dataCell'><?php echo htmlspecialchars($player['pos']); ?>&nbsp;</td>
		<td class='dataCell'><?php echo htmlspecialchars($player['nfl_team']); ?>&nbsp;</td>
		<td class='dataCell'><?php echo htmlspecialchars($player['bye']); ?>&nbsp;</td>
		<td class='dataCell'><?php echo htmlspecialchars($player['current_ff_team']); ?>&nbsp;</td>
		<td class='dataCell' <?php gbi($player['scale']); ?>>
			<?php echo htmlspecialchars($player['avg_score']); ?>&nbsp;
		</td>
		<td class='dataCell'><?php echo htmlspecialchars($player['rating']); ?>&nbsp;</td>
	</tr>
		
	<?php endforeach; ?>
	
</table>
