<?php

class Importer {
	private static $path;
	
	public static function init($zipFile) {
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
		$articles = self::createArticles();
		if (!$articles) {
			return false;
		}
		
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
			
			foreach ($articles as &$article) {
				error_log($article);
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

	public static function openDoc($document){
		$upload = wp_upload_dir();
		$file = $upload['basedir'].'/BatchImport/'.$document;
	
		$zip = new ZipArchive();
	
		if ($zip->open($file, ZipArchive::CREATE)!==TRUE) {
			return false;
		}
	
		$xml = $zip->getFromName('word/document.xml');
	
		//$content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
		//$content = str_replace('</w:r></w:p>', "\r\n", $content);
		//$striped_content = strip_tags($content);
	
		return $xml;
	}

}