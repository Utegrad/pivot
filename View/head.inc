<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<?php foreach($App->StyleSheets as $key => $sheet): ?>
	<!-- StyleSheet: <?php echo $key; ?> -->
	<link REL="stylesheet" type="text/css" href="<?php echo $APP_URL . $sheet; ?>" />
<?php endforeach; ?>
<title>Fantasy Summary Data</title>
</head>
<body>