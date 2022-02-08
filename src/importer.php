<?php

class Importer {
	private static $path;
	
	public static function init($zipFile) {
		ob_start();
		
		$uploadsPath = wp_upload_dir();
		self::$path = $uploadsPath['basedir'].'/BatchImport';
		
		//Delete all files in wp-content/uploads/BatchImport
		self::cleanFolder();
		
		//Move zip to wp-content/uploads/BatchImport
		$saved = self::saveZip($zipFile);
		if (!$saved) {
			return false;
		}
		
		//Extract zip to wp-content/uploads/BatchImport
		$extracted = self::extractZip();
		if (!$extracted) {
			return false;
		}
		
		//Create Categories if Necessary
		self::createCategories();
		
		//Create Articles
		self::createArticles();
		
		return true;
		
	}
	
	//Actions
	public static function cleanFolder() {		
		$files = scandir(self::$path);
		if (count($files) > 2) {
			$folder = self::$path.'/'.$files[2];
			self::recursiveDelete($folder);
		}
	}

	public static function saveZip($file) {		
		if (!is_dir(self::$path)) {
			mkdir($path, 0700);
		}
	
		return move_uploaded_file($file["tmp_name"], self::$path.'/upload.zip');
	}

	public static function extractZip() {	
		$zip = new ZipArchive();
		$zipOpen = $zip->open(self::$path.'/upload.zip');
		if($zipOpen === true) {
			$zip->extractTo(self::$path);
			$zip->close();
			unlink(self::$path.'/upload.zip');
			return true;
		}
		else {
			return false;
		}
	}
	
	public static function createCategories() {
		$niches = self::getAllFiles(self::$path.'/Articles');
		$out = array();
		
		foreach ($niches as &$niche) {
			$exists = term_exists($niche, 'category');
			if ($exists === NULL) {
				wp_create_category($niche, 0);
			}
		}
	}
	
	public static function createArticles() {
		$niches = self::getAllFiles(self::$path.'/Articles');
		
		foreach ($niches as &$niche) {
			$path = self::$path.'/Articles/'.$niche;
			$articles = self::getAllFiles($path);
			
			$category = get_cat_ID($niche);
			
			foreach ($articles as &$article) {
				$rawName = explode('.', $article)[0];
				$name =  str_replace('_', ' ', $rawName);
				
				$post = self::getPostByName($name);
				
				$postData = array();
				$document = $path.'/'.$article;
				
				$content = self::parseDoc($document);
				
				if ($post != NULL) {
					$postData = array(
						'ID' => $post->ID,
						'post_title' => $post->post_title,
						'post_content' => $content,
						'post_status' => $post->post_status,
						'post_author' => $post->post_author,
						'post_category' => $post->post_category
					);
				}
				else {
					$postData = array(
						'post_title' => $name,
						'post_content' => $content,
						'post_status' => 'publish',
						'post_author' => get_current_user_id(),
						'post_category' => array($category)
					);
				}
				
				wp_insert_post($postData);
			}
		}
		
		return true;
	}
	
	//Utils
	public static function getAllFiles($path) {
		$files = scandir($path);
		array_shift($files);
		array_shift($files);
		return $files;
	}
	
	public static function recursiveDelete($dir) {
		if (is_dir($dir)) { 
			$files = scandir($dir); 
			foreach ($files as $file) { 
				if ($file != "." && $file != "..") { 
					if (filetype($dir."/".$file) == "dir") self::recursiveDelete($dir."/".$file); else unlink($dir."/".$file); 
				} 
			} 
			reset($files); 
			rmdir($dir); 
		} 
	}
	
	public static function getPostByName($postName) {
		$query = new WP_Query([
			"post_type" => 'post',
			"post_status" => array('publish', 'draft'),
			"name" => $postName
		]);
		
		return $query->have_posts() ? reset($query->posts) : NULL;
	}
	
	//DOCX
	public static function parseDoc($document){
		$unformatted = self::openDoc($document);
		$cleaned = self::removeCopyscape($unformatted);
		$titled = self::insertTags($cleaned, '~', '<strong>');
		$newLine = "

";
		$lineBreaks =  str_replace('|', $newLine, $titled);
		return $lineBreaks;
	}
	
	public static function openDoc($document){
		$file = $document;
	
		$zip = new ZipArchive();
	
		if ($zip->open($file, ZipArchive::CREATE)!==TRUE) {
			return false;
		}
	
		$rawXML = $zip->getFromName('word/document.xml');
		
		$xml = simplexml_load_string($rawXML);
		$namespaces = $xml->getNameSpaces(true);
		$xml->registerXPathNamespace('w', $namespaces['w']);
		$nodes = $xml->xpath('/w:document/w:body//w:t');
		
		$content = implode(' ', $nodes);
	
		return $content;
	}
	
	public static function removeCopyscape($raw) {
		return explode('*', $raw)[0];
	}
	
	public static function insertTags($raw, $symbol, $replace) {
		$contents = explode($symbol, $raw);
		$isChar = true;
		$out = '';
		
		$filtered = array_values(array_filter($contents));
		
		$startTag = $replace;
		$endTag = substr($replace, 0, 1).'/'.substr($replace, 1);
		$newLine = "

";
		
		foreach($filtered as &$part) {			
			$out .= ($isChar) ? $startTag.$part.$endTag.$newLine : $part;
			$isChar = !$isChar;
		}
		
		return $out;
	}
}