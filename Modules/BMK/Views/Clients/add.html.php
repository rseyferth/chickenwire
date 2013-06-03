<h1>Nieuwe klant</h1>

<?php

	$form = $this->html->formFor($this->client);

	$form->textField(array(
		"name" => "name",
		"label" => "Client"
	));

	$form->submitButton(array("value" => "Toevoegen"));

	echo $form;

?>
