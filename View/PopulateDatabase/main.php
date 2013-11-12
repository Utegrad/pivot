<h2>This is the main section</h2>

<table class="DataTable">
  <tr>
    <th class="headingCell">Action</th>
    <th class="headingCell">Current Data</th>
  </tr>
  <?php foreach ($tables as $table => $script): ?>
  	<tr>
  		<td class='dataCell'><ul><li><a href="<?php echo $APP_URL .'View/PopulateDatabase/Scripts/'. $table .'.php'; ?>"><?php echo $table; ?></a></li></ul></td>
  		<td class='dataCell'><?php echo $script; ?></td>
  	</tr>
  <?php endforeach; ?>
</table>

<?php
	
?>
