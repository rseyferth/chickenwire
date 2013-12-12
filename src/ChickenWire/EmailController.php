<?php

	namespace ChickenWire;

	use ChickenWire\Core\Mime;

	class EmailController extends Controller {

		static $layout = false;

		public $message;


		public function __construct() {

			// Create the message
			$this->message = \Swift_Message::newInstance();

		}


		protected function renderMulti($template) {

			// Render both
			$this->renderHtml($template);
			$this->renderText($template);

		}


		protected function renderHtml($template) {
			$this->renderMailPart($template, Mime::HTML);
		}
		protected function renderText($template) {
			$this->renderMailPart($template, Mime::TEXT);
		}

		protected function renderMailPart($template, $mime = null) {

			// Try to find the right file
			if (is_null($mime)) $mime = Mime::HTML;
			if (is_string($mime)) $mime = new Mime($mime);

			// Is the template graced with an extension already?
			if (preg_match('/\.([a-z]{2,5})(\.php)?$/', $template, $match)) {

				// Filename already found
				$filename = $template;

				// PHP at the end?
				if (!preg_match('/\.php$/', $filename)) {
					$filename .= '.php';
				}

				// File exists?
				$filename = (VIEW_PATH . '/' . $filename);
				if (!file_exists($filename)) {
					throw new \Exception("View could not be found: " . $filename, 1);
					die;					
				}


			} else {

				// Filename without extension
				$filenameWithoutExt = $template;
				
				// Check all extensions for this MIME type
				$fullPath = VIEW_PATH . '/' . $filenameWithoutExt;
				$fileFound = false;
				foreach ($mime->getExtensions() as $ext) {

					$filename = $fullPath . '.' . $ext . '.php';
					if (file_exists($filename)) {
						$fileFound = true;
						break;
					}

				}

				// Not found?
				if ($fileFound === false) { 
					throw new \Exception("Couldn't find an appropriate view for " . $template, 1);					
				}

			}

			// Create translation functions
			$t = I18n::translateClosure();
			
			// Start buffer
			ob_start();

			// Load the file
			require($filename);

			// Get content
			$content = ob_get_contents();
			ob_end_clean();

			// Check layout
			if (static::$layout !== false) {
				
				// Try to find the layout too
				$layoutFile = LAYOUT_PATH . '/' . static::$layout;
				$fullLayoutFile = '';
				$layoutFound = false;
				foreach ($mime->getExtensions() as $ext) {
					$fullLayoutFile = $layoutFile . '.' . $ext . '.php';
					if (file_exists($fullLayoutFile)) {
						$layoutFound = true;
						break;
					}
				}

				// Use layout?
				if ($layoutFound == true) {

					// Render layout
					ob_start();
					$layout = new Layout($fullLayoutFile, $this, [
						"main" => $content
					]);
					$content = ob_get_contents();
					ob_end_clean();

				}

			}


			// Set the content!
			$this->message->addPart($content, $mime->getContentType());

		}

		protected function attach($filename) {

			$attachment = \Swift_Attachment::fromPath($filename);
			$this->message->attach($attachment);

		}


		protected function send() {

			// Get the transporter from the config
			$mailConfig = \ChickenWire\Application::getConfiguration()->mailer;
			if ($mailConfig instanceof \Closure) {
				$mailConfig = $mailConfig();
			}
			if (!is_subclass_of($mailConfig, "\Swift_Transport")) {
				$mailConfig = new \Swift_MailTransport();
			}

			// Prepare the mailer
			$mailer = \Swift_Mailer::newInstance($mailConfig);


			// Send it!
			$mailer->send($this->message);



		}

		public function image($filename) {

			// Find the file
			$filename = PUBLIC_PATH . '/img/' . $filename;
			if (file_exists($filename)) {

				// Embed inline!
				return $this->message->embed(\Swift_Image::fromPath($filename));

			} else {

				return 'about:blank';

			}

		}



	}