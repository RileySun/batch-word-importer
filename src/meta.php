<?php


BatchImport::init();

class BatchImport {

	public static function init() {
		//Init
		add_action('admin_menu', array(__CLASS__, 'adminPage'));
		add_action('admin_post_nopriv_batchImport', array(__CLASS__, 'submitAdminPage'));
		add_action('admin_post_batchImport', array(__CLASS__, 'submitAdminPage'));
		
		//Enqueue Scripts
		add_action('admin_enqueue_scripts', array(__CLASS__, 'batchImportEnqueAdminScripts'));
	}
	
	public static function adminPage() {
		$hooknameTop = add_menu_page('Batch Import', 'Batch Import', 'manage_options', 'batchimport', array(__CLASS__, 'mainPage'), 'dashicons-book', 25);
		$hooknameSub = add_submenu_page('batchimport', 'Settings', 'Settings', 'manage_options', 'settings', array(__CLASS__, 'settingsPage') );
	}
	
	public static function submitAdminPage() {
		include 'importer.php';
		if (isset($_POST['BatchImport'])) {
			$importer = new Importer;
			$result = $importer::init($_FILES['zip']);
			//echo 'Result: '.$result;
			echo ($result) ? 'Success' : 'Error';
		}
		//wp_redirect( $_SERVER['HTTP_REFERER'] );
		//exit;
	}
	
	public static function mainPage() {
		wp_enqueue_style('batch-import-css');
		//wp_enqueue_script('sun-carousel-js');
		include 'templates/main.php';
	}
	
	public static function settingsPage() {
		//wp_enqueue_style('sun-carousel-style');
		//wp_enqueue_script('sun-carousel-js');
		include 'templates/settings.php';
	}
	
	
	public static function batchImportEnqueAdminScripts($hook) {
		if ($hook == 'post-new.php' || $hook == 'post.php') {
			wp_register_style('batch-import-css', plugin_dir_url( __FILE__ ) . 'assets/batch-import.css', array(), '1.0.0', 'all' );
			wp_register_script('batch-import-js', plugin_dir_url( __FILE__ ) . 'assets/batch-import.js', array('jquery'), '1.0.0', 'all' );
			wp_enqueue_media();
			
		}
	}
}