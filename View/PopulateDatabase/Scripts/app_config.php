<?php
// populate table
require_once '../../../bootstrap.php';

$result = Api::TouchToken('U2FsdGVkX18sl4I1Gh0SqlhsdkUHsDkBDDE7g9qdCA47-FBs_prQvE7kzJb9lhocLWDdjWhb60ibkRrjR63G4lRbVvnokVn_XN5s4iHoi2aPiRwyXFTKNoUno1PYvxg4');
if ($result === FALSE) {
	echo "<h2>Problem adding token to the database</h2>";
	echo "<p><a href='". $APP_URL ."/View/PopulateDatabase/index.php'>Index</a></p>";
}
else{
	header('Location: '. $APP_URL .'/View/PopulateDatabase/index.php' );
}

?>
