<p>Hallo wij zijn de index.</p>

<ul>
<?php

	foreach($this->clients as $client) {

		echo ('<li>' . $this->html->linkTo($client, $client->name) . '</li>');

	}

?>
</ul>