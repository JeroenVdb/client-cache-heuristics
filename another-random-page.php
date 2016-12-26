<?php
	$cachebusterString = '';
	if ($_GET['cachebuster']) {
		$cachebusterString = '?cachebuster=' . $_GET['cachebuster'];
	}
?>


<a href="http://jeroenvdb.be/check-resource-origin/<?php echo $cachebusterString ?>">go back</a>
