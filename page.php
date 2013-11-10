<?php 
// any session or preprocessing

require APP_ROOT . 'View' . DS . 'head.inc';
?>

<div id='content'>
	<?php require APP_ROOT . 'View' . DS . 'banner.php'; ?>
	
	<?php require APP_ROOT . 'View' . DS . 'main.php'; ?>
	
	<?php require APP_ROOT . 'View' . DS . 'closing.php'; ?>
</div>

<?php require APP_ROOT . 'View' . DS . 'footer.inc';?>