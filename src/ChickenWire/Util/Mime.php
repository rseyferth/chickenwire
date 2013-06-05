<?php

	namespace ChickenWire\Util;



	class Mime
	{

		const ALL = "ALL";
		const TEXT = "TEXT";
		const HTML = "HTML";
		const JS = "JS";
		const ICS = "ICS";
		const CSV = "CSV";
		const XML = "XML";
		const YAML = "YAML";
		const RSS = "RSS";
		const ATOM = "ATOM";
		const JSON = "JSON";
		const CSS = "CSS";
		
	/*	const SOAP = "SOAP";

		const PDF = "PDF";
		const ZIP = "ZIP";
		const GZIP = "GZIP";

		const JPEG = "JPEG";
		const GIF = "GIF";
		const PNG = "PNG";
		const SVG = "SVG";
		const TIFF = "TIFF";


		const MP4_AUDIO = "MP4_AUDIO";
		const MP3 = "MP3";
		const OGG_AUDIO = "OGG_AUDIO";

		const MP4_VIDEO = "MP4_VIDEO";
		const OGG_VIDEO = "OGG_VIDEO";
		const QUICKTIME = "QUICKTIME";
		const WEBM = "WEBM";
		const FLV = "FLV";
		const MATROSKA = "MATROSKA";*/

		/**
		 * The mapping of actual mime-types to simplified content types
		 *
		 * This list is based on Rails' mime mapping (http://apidock.com/rails/Mime) with
		 * some additions.
		 * 
		 * @var array
		 */
		public static $contentTypeMap = array(

			"*/*" => self::ALL,
			
			"text/plain" => self::TEXT,

			"text/html" => self::HTML,
			"application/xmlhtml+xml" => self::HTML,
			"application/html+xml" => self::HTML,

			"text/javascript" => self::JS,
			"application/javascript" => self::JS,
			"application/x-javascript" => self::JS,

			"text/calendar" => self::ICS,

			"text/csv" => self::CSV,

			"application/xml" => self::XML,
			"text/xml" => self::XML,
			"application/x-xml" => self::XML,

			"text/yaml" => self::YAML,
			"application/yaml" => self::YAML,

			"application/rss+xml" => self::RSS,

			"application/atom+xml" => self::ATOM,

			"application/json" => self::JSON,
			"text/x-json" => self::JSON,

			/*"application/soap+xml" => self::SOAP,

			"image/gif" => self::GIF,
			"image/jpeg" => self::JPEG,
			"image/pjpeg" => self::JPEG,
			"image/png" => self::PNG,
			"image/svg+xml" => self::SVG,
			"image/tiff" => self::TIFF,*/

			"text/css" => self::CSS

		);

		/**
		 * The mapping of file extentions to simplified content types
		 * @var array
		 */
		public static $extensionMap = array(

			"txt" => self::TEXT, 

			"html" => self::HTML,
			//"htm" => self::HTML,			We don't like .htm... Just takes up guessing time, and is old fashioned...

			"js" => self::JS,

			"css" => self::CSS,

			"ics" => self::ICS,			
			"csv" => self::CSV,

			"xml" => self::XML,

			"yml" => self::YAML,

			"rss" => self::RSS,

			"atom" => self::ATOM,
			"json" => self::JSON

		/*	"pdf" => self::PDF,
			"zip" => self::ZIP,
			"gzip" => self::ZIP,

			"jpg" => self::JPEG,
			"jpeg" => self::JPEG,
			"gif" => self::GIF,
			"png" => self::PNG,
			"svg" => self::SVG,
			"tiff" => self::TIFF,
			"tif" => self::TIFF,

			"m4a" => self::MP4_AUDIO,
			"mp3" => self::MP3,
			"oga" => self::OGG_AUDIO,
			"ogg" => self::OGG_AUDIO,

			"mp4" => self::MP4_VIDEO,
			"m4v" => self::MP4_VIDEO,
			"ogv" => self::OGG_VIDEO,
			"mov" => self::QUICKTIME,
			"qt" => self::QUICKTIME,
			"webm" => self::WEBM,
			"flv" => self::FLV,
			"f4v" => self::FLV,
			"mkv" => self::MATROSKA*/

		);	

		/**
		 * Register a new Mime type (or add contentTypes or extensions to an existing type)
		 * @param  string 		$type        The name of the type, for example 'XLS'
		 * @param  string|array $contentType The content-type(s), for example 'application/vnd.ms-excel'
		 * @param  string|array $extension   The file extension(s), for example 'xls'
		 * @return void
		 */
		public static function register($type, $contentType, $extension)
		{

			// Loop through content types
			$contentType = is_array($contentType) ? $contentType : array($contentType);
			foreach ($contentType as $ct) {
				self::$contentTypeMap[$type] = $ct;
			}

			// Loop through extensions
			$extension = is_array($extension) ? $extension : array($extension);
			foreach ($extension as $ext) {
				self::$extensionMap[$ext] = $type;
			}

		}


		/**
		 * Find a Mime type by its extension
		 * @param string $extension The file extension
		 * @param float $quality  (default: 1) The quality of the mime in a HTTP Accept header (i.e. it's rank)
		 * @return Mime|false      A Mime instance, or false when extension is not known.
		 */
		public static function byExtension($extension, $quality = 1.0)
		{

			// Trim it!
			$extension = trim($extension, '. ');

			// Look it up
			if (!array_key_exists($extension, self::$extensionMap)) {
				return false;
			} else {

				return new Mime(self::$extensionMap[$extension], $quality);

			}

		}

		public static function byFile($filename)
		{
			// By fileinfo?
			$mime = null;
			if (function_exists("finfo_open")) {

				$info = finfo_open(FILEINFO_MIME_TYPE);
				$mime = finfo_file($info, $filename);
				$mime = Mime::byContentType($mime);

			} elseif (function_exists("mime_content_type")) {
				$mime = mime_content_type($filename);
				$mime = Mime::byContentType($mime);
			}

			// Text?
			if (is_null($mime) || preg_match('/^text\//', $mime->getContentType())) {

				// Check extension
				$ext = pathinfo($filename, PATHINFO_EXTENSION);
				if ($ext == '') {
					$mime = \ChickenWire\Application::getConfiguration()->defaultOutputMime;
				} else {
					$mime = Mime::byExtension($ext);
				}

			}

			return $mime;

		}

		/**
		 * Find a Mime type by a contentType
		 * @param  string $contentType The content type to look for, such as application/pdf.
		 * @param  float $quality    (default: 1) The quality of the mime in a HTTP Accept header (i.e. it's rank)
		 * @return Mime|false      A Mime instance, or false when contentType is not known.
		 */
		public static function byContentType($contentType, $quality = 1.0)
		{

			// Look it up
			if (!array_key_exists($contentType, self::$contentTypeMap)) {
				return false;
			} else {

				return new Mime(self::$contentTypeMap[$contentType], $quality);

			}

		}



		public $type;
		public $quality;

		/**
		 * Create a new Mime instanced
		 * @param string $type The mime type (one of the class constants)
		 * @param float $quality The quality of the mime in a HTTP Accept header (i.e. it's rank)
		 */
		public function __construct($type, $quality = 1.0)
		{

			// Store it
			$this->type = $type;
			$this->quality = floatval($quality);

		}


		/**
		 * Get possible extensions for this type
		 * @return array Array of possible extensions for this Mime type
		 */
		public function getExtensions()
		{

			// Loop through extension map
			$exts = array();
			foreach (self::$extensionMap as $ext => $type) {
				if ($type == $this->type) {
					$exts[] = $ext;
				}
			}
			return $exts;

		}

		/**
		 * Get the default content-type string for this Mime type
		 * @return string The content-type
		 */
		public function getContentType()
		{

			foreach (self::$contentTypeMap as $ct => $type) {
				if ($type == $this->type) {
					return $ct;
				}
			}
			return false;

		}


	}


?>