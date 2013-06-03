<?php

	namespace ChickenWire\Util;

	/**
	 * Html Helper class
	 *
	 * The Html helper class can be used to generate HTML code. In your 
	 * views (or in the Controller) you can use $this->html.
	 *
	 * The return values are often of the type \HtmlObject\Element. See
	 * <a href="https://github.com/Anahkiasen/html-object">html-object library</a>
	 * for more information.
	 * 
	 * @link https://github.com/Anahkiasen/html-object html-object library
	 *
	 * @package ChickenWire
	 */
	class Html extends \ChickenWire\Core\Singleton
	{

		static $_instance;


		public function formFor(\ChickenWire\Model $record, $options = array())
		{

			// No action?
			if (!array_key_exists("action", $options)) {

				// A new record?
				if ($record->is_new_record()) {
					$options['action'] = Url::instance()->index($record);
					$options['method'] = "post";
				} else {
					$options['action'] = Url::instance()->update($record);
					$options['method'] = "put";
				}

			}

			// Default options
			$options = array_merge(array(
				"record" => $record
			), $options);
				


			// Create form
			$form = new \ChickenWire\Form\Form($options);

			return $form;

		}


		
		/**
		 * Create a new Link element
		 *
		 * <code>
		 * echo $this->html->link('http://www.google.com/', 'Google', array("target" => "_blank"));
		 * </code>
		 * <code>
		 * <a href="http://www.google.com/" target="_blank">Google</a>
		 * </code>
		 * <br>
		 * 
		 * <code>
		 * $person = People::find(1);
		 * echo $this->html->link($person, $person->name);
		 * </code>
		 * <code>
		 * <a href="/people/1">John Doe</a>
		 * </code>
		 * <br>
		 *
		 * <code>
		 * $person = People::find(1);		  
		 * echo $this->html->link($this->url->edit($person), "Edit " . $person->name);
		 * </code>
		 * <code>
		 * <a href="/people/1/edit">John Doe</a>
		 * </code>
		 * 
		 * @param  string|\ChickenWire\Model 	A url or a Model instance to link to. When you pass a Model instance, the Url helper will be used to resolve it to a 'show' action url.
		 * @param  string 						The text/elements to put inside the link.
		 * @param  array  						An array of attributes to add to the element.
		 * @return \HtmlObject\Link            The Link element.
		 */
		public function link($target, $caption = null, $attributes = array())
		{

			// Check if target needs to be resolved
			if (is_object($target)) {

				// Try to get a link
				$target = Url::instance()->show($target);

			}

			// Create element
			$link = new \HtmlObject\Link($target, $caption, $attributes);
			return $link;

		}

		/**
		 * Create new delete Link element
		 *
		 * **Note** This requires the ChickenWire front-end Javascript library to be included in your page.
		 * 
		 * @param  \ChickenWire\Model|string 	The Model instance to create the delete link for, or a string containing a url.
		 * @param  string 						The text/elements to put inside the link.
		 * @param  string|false 				The message to confirm the deletion. When false, no message will be shown.
		 * @param  array  						An array of attributes to add to the element.
		 * @return \HtmlObject\Link 		The Link element.
		 */
		public function deleteLink($target, $caption = null, $confirmMessage = 'Are you sure?', $attributes = array())
		{

			// Check if target needs to be resolved
			if (is_array($target) || is_object($target)) {

				// Try to get a link
				$target = Url::instance()->delete($target);

			}

			// Set method to delete
			$attributes = array_merge(array(
				"data-method" => "delete"
			), $attributes);

			// Confirm?
			if (is_null($confirmMessage)) {
				$attributes['data-confirm'] = 'Are you sure?';
			} elseif ($confirmMessage !== false) {
				$attributes['data-confirm'] = $confirmMessage;
			}

			// Create element
			$link = new \HtmlObject\Link($target, $caption, $attributes);
			return $link;

		}





		/**
		 * Create a listing for the given array of items, using a callback for each item
		 *
		 * For example, in a view you might do:
		 * <code>
		 * $this->html->listingFor($this->people, function($person) {
		 *  return $person->name;
		 * });
		 * </code>
		 * <code>
		 * <ul>
		 *  <li>John Doe</li>
		 *  <li>Phil Spector</li>
		 * </ul>
		 * </code>
		 * <br>
		 *
		 * Or a little more complex:
		 * <code>
		 * $this->html->listingFor($this->people, function($index, $person) {
		 *  return $this->html->link($person, $index . ": " . $person->name);  
		 * });
		 * </code>
		 * <code>
		 * <ul>
		 *  <li><a href="/people/1">0: John Doe</a></li>
		 *  <li><a href="/people/2">1: Phil Spector</a></li>
		 * </ul>
		 * </code>
		 * <br>
		 *
		 * And, if you want to modify the list item itself:
		 * <code>
		 * $this->html->listingFor($this->people, function($person) {
		 *  return $this->html->li($person->name, array("class" => "person " . $person->gender));
		 * });
		 * </code>
		 * <code>
		 * <ul>
		 *  <li class="person male">John Doe</li>
		 *  <li class="person female">Jane Doe</li>
		 * </ul>
		 * </code>
		 * 
		 * @param  array        Array of items to iterate
		 * @param  \Closure 	The callback function to call for each item
		 * @param  array        Array of HTML attributes to apply to the listing
		 * @param  string   	The HTML element for the list
		 * @param  string   	The HTML element for the items
		 * @return \HtmlObject\Element
		 */
		public function listingFor($items, \Closure $callback, $attributes = array(), $element = 'ul', $childElement = 'li')
		{

			// Create the element
			$list = \HtmlObject\Element::$element('', $attributes);

			// Get closure info
			$closureMethod = Reflection::getClosureMethod($callback);
			$closureArgs = Reflection::getClosureParams($closureMethod);

			// Loop through items
			$index = 0;
			foreach ($items as $item) {

				// Invoke the closure
				$li = Reflection::invokeClosure($callback, 
					array($index, $item), 
					array($item));

				// Is it an element?
				if (is_string($li)) {

					// Create li containing that...
					$realLi = \HtmlObject\Element::$childElement($li);
					$list->addChild($realLi);

				} elseif (is_subclass_of($li, "\\HtmlObject\\Traits\\Tag")) {

					// An li tag?
					if ($li->getTag() !== $childElement) {

						// Wrap it!
						$realLi = \HtmlObject\Element::$childElement($li);
						$list->addChild($realLi);

					} else {

						// Add it as it is
						$list->addChild($li);

					}

				} else {

					// Not good.
					throw new \Exception("The ulFor callback needs to return a HTMLObject\\Element, or a string containing HTML.", 1);
					
				}



				$index++;
			}
			return $list;

		}

		
		public function ulFor($items, \Closure $callback, $attributes = array())
		{
			return $this->listingFor($items, $callback, $attributes, 'ul', 'li');
		}
		public function olFor($items, \Closure $callback, $attributes = array())
		{
			return $this->listingFor($items, $callback, $attributes, 'ol', 'li');
		}


		/**
		 * Create a new listing
		 *
		 * **Simple list**
		 * <code>
		 * $this->html->listing(array('item 1', 'item 2', 'item 3'));
		 * </code>
		 * <code>
		 * $this->html->ul(array('item 1', 'item 2', 'item 3'));
		 * </code>
		 * <code>
		 * <ul>
		 *  <li>item 1</li>
		 *  <li>item 2</li>
		 *  <li>item 3</li>
		 * </ul>
		 * </code>
		 * <br>
		 * 
		 * **Ordered list**
		 * <code>
		 * $this->html->listing(array('item 1', 'item 2', 'item 3'), array(), 'ol');
		 * </code>
		 * <code>
		 * $this->html->ol(array('item 1', 'item 2', 'item 3'));
		 * </code>
		 * <code>
		 * <ol>
		 *  <li>item 1</li>
		 *  <li>item 2</li>
		 *  <li>item 3</li>
		 * </ol>
		 * </code>
		 * <br>
		 * 
		 * **Custom list**
		 * <code>
		 * $this->html->listing(array('item 1', 'item 2', 'item 3'), array('class' => 'listing'), 'div', 'span');
		 * </code>
		 * <code>
		 * <div class="listing">
		 *  <span>item 1</span>
		 *  <span>item 2</span>
		 *  <span>item 3</span>
		 * </div>
		 * </code>
		 *
		 * 
		 * @param  array  		Array of strings or elements to add as list items.
		 * @param  array  		Attributes to add the to the listing element
		 * @param  string 		The HTML element to use for the listing.
		 * @param  string 		The HTML element to use for the list items.
		 * @return \HtmlObject\Element 	The created element.
		 */
		public function listing($contents = array(), $attributes = array(), $element = 'ul', $childElement = 'li')
		{

			// Create list
			$list = \HtmlObject\Element::$element('', $attributes);

			// Array?
			if (!is_null($contents) && !is_array($contents)) {
				$contents = array($contents);
			}

			// Loop contents
			foreach ($contents as $li)  {

				// Is it an element?
				if (is_string($li)) {

					// Create li containing that...
					$realLi = \HtmlObject\Element::$childElement($li);
					$list->addChild($realLi);

				} elseif (is_subclass_of($li, "\\HtmlObject\\Traits\\Tag")) {

					// An li tag?
					if ($li->getTag() !== $childElement) {

						// Wrap it!
						$realLi = \HtmlObject\Element::$childElement($li);
						$list->addChild($realLi);

					} else {

						// Add it as it is
						$list->addChild($li);

					}

				} else {

					// Not good.
					throw new \Exception("Invalid contents for listing. ", 1);					

				}

			}



			return $list;

		}

		/**
		 * Create a UL listing (shortcut for listing)
		 * @param  array  		Array of strings or elements to add as list items.
		 * @param  array  		Attributes to add the to the listing element
		 * @return \HtmlObject\Element
		 */
		public function ul($contents = null, $attributes = array())
		{
			return $this->listing($contents, $attributes, 'ul', 'li');
		}

		/**
		 * Create a OL listing (shortcut for listing)
		 * @param  array  		Array of strings or elements to add as list items.
		 * @param  array  		Attributes to add the to the listing element
		 * @return \HtmlObject\Element
		 */
		public function ol($contents = null, $attributes = array())
		{
			return $this->listing($contents, $attributes, 'ol', 'li');	
		}


		/**
		 * Create a new list item
		 * @param  string|Element    The contents of the list item
		 * @param  array  			Attributes to add to the element
		 * @return \HtmlObject\Element             The created element
		 */
		public function li($contents = '', $attributes = array())
		{

			$li = \HtmlObject\Element::li($contents, $attributes);
			return $li;

		}


	}


?>