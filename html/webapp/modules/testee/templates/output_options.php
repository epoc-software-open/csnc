<?php
if($action->options){
	echo '<table style="width:100%;">';
	foreach ( $action->options as $key => $option ) {
		echo '<tr><td>' . $option["name"] . '</td><td><input name="option_counts['.$option["code"].']" type="text" size="5" /></td><td>'.$option["count"].'</td></tr>';
	}
	echo '</table>';
}
?>
