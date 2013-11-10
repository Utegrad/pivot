<?php
$totalCount = 12304;
$bin = 300;
for ($i = 0; $i < $totalCount; $i++) {
	if ($i % $bin == 0) {
		echo "\n$i marks interval";
	}
	if ($i == ($totalCount - 1)) {
		echo "\n$i: equals last item.";
	}
}

?>