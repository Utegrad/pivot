<!--  include javascript here -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

</body>

<?php 
if(isset($database->conn)){
	$database->conn->close();
}
?>

</html>