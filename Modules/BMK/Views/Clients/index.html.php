<h1>Index</h1>

<?php

	echo $this->html->ulFor($this->clients, function($client) {

		return $this->html->link($this->url->edit($client), $client->name);
		
	});

?>

<?php echo $this->html->link($this->url->addClient, "Nieuwe klant."); ?><br />
