<?php

function openDoc($document){
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

function saveZip($file) {
	$upload = wp_upload_dir();
	$path = $upload['basedir'].'/BatchImport';
	if (!is_dir($path)) {
       mkdir($path, 0700);
    }
    
	return move_uploaded_file($file["tmp_name"], $path.'/upload.zip');
}

function extractZip() {
	$upload = wp_upload_dir();
	$path = $upload['basedir'].'/BatchImport';
	
	$zip = new ZipArchive();
	$x = $zip->open($path.'/upload.zip');
	if($x === true) {
		$zip->extractTo($path);
		$zip->close();
		unlink($path.'/upload.zip');
		return true;
	}
	else {
		return false;
	}
}

function importArticles() {

}


function uploadFile($file) {
	$saved = saveZip($file);
	
	if ($saved) {
		$extracted = extractZip();
		
		if ($extracted) {
			$imported = importArticles();
		}
	}
}