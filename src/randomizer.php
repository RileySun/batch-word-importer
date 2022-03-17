<?php

class PostRandomizer {	
	public static function init() {
		$posts = self::getAllPosts();
		$dates = array();
		
		//Get Dates
		foreach ($posts as &$post) {
			$dates[] = self::getRandomDate();
		}
		usort($dates, array( __CLASS__, 'date_sort'));
		
		$out = array();
		
		//Set Post Dates
		foreach ($posts as $index => $post) { 
			self::changeDate($post->ID, $dates[$index]);
		}
		return json_encode($out);
	}
	
	private static function getAllPosts() {
		$args = array(
			'numberposts' => -1,
			'post_status' => 'publish',
			'post_type' => 'post',
			'orderby' => 'title',
		);
		return get_posts($args);
	}
	
	private static function getRandomDate() {
		$start = strtotime('-30 days');
		$end = strtotime('today');
		$random = mt_rand($start, $end);
		return date('Y-m-d H:i:s', $random); 
	}
	
	private static function changeDate($postID, $newDate) {
		$postData = array(
			'ID' => $postID,
			'post_date' => $newDate,
			'post_modified' => $newDate
		);
		wp_update_post($postData);
	}
	
	private static function date_sort($a, $b) {
    	return strtotime($a) - strtotime($b);
	}
}