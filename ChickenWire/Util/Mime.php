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
		
		const SOAP = "SOAP";

		const PDF = "PDF";
		const ZIP = "ZIP";
		const GZIP = "GZIP";

		const JPEG = "JPEG";
		const GIF = "GIF";
		const PNG = "PNG";
		const SVG = "SVG";
		const TIFF = "TIFF";

		const CSS = "CSS";

		const MP4_AUDIO = "MP4_AUDIO";
		const MP3 = "MP3";
		const OGG_AUDIO = "OGG_AUDIO";

		const MP4_VIDEO = "MP4_VIDEO";
		const OGG_VIDEO = "OGG_VIDEO";
		const QUICKTIME = "QUICKTIME";
		const WEBM = "WEBM";
		const FLV = "FLV";
		const MATROSKA = "MATROSKA";

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

			"application/soap+xml" => self::SOAP,

			"image/gif" => self::GIF,
			"image/jpeg" => self::JPEG,
			"image/pjpeg" => self::JPEG,
			"image/png" => self::PNG,
			"image/svg+xml" => self::SVG,
			"image/tiff" => self::TIFF,

			"text/css" => self::CSS

		);

		/**
		 * The mapping of file extentions to simplified content types
		 * @var array
		 */
		public static $extensionMap = array(

			"txt" => self::TEXT, 

			"html" => self::HTML,
			"htm" => self::HTML,

			"js" => self::JS,

			"css" => self::CSS,

			"ics" => self::ICS,			
			"csv" => self::CSV,

			"xml" => self::XML,

			"yml" => self::YAML,

			"rss" => self::RSS,

			"atom" => self::ATOM,
			"json" => self::JSON,

			"pdf" => self::PDF,
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
			"mkv" => self::MATROSKA

		);	


	}


?>