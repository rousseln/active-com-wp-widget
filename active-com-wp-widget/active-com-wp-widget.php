<?php

/**
 *
 * @link              rousseldesign.com
 * @since             1.0.0
 * @package           active-com-wp-widget
 *
 * @wordpress-plugin
 * Plugin Name:       Events from Active.com
 * Plugin URI:        rousseldesign.com
 * Description:       A plugin that displays the latest events from active.com.
 * Version:           1.0.0
 * Author:            Norm Roussel
 * Author URI:        rousseldesign.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       active-events-widget


 Active.com Events Widget is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 2 of the License, or
 any later version.

 Active.com Events Widget is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Active.com Events Widget. If not, see http://www.gnu.org/licenses/gpl-2.0.txt.

 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );



/* === Create Widget === */
add_action( 'widgets_init', function(){
		register_widget( 'activecom_widget' );
	});

class activecom_widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'activecom_widget', 
			__('Active.com Events', 'active-events-widget'), 
			array( 'description' => __( 'List the latest events from a select active.com account', 'active-events-widget' ), ) 
		);
	}

	public function widget( $args, $instance ) {
		wp_enqueue_style( 'active-style', plugins_url('css/active.css', __FILE__) );
		wp_enqueue_style( 'font-awesome', plugins_url('css/font-awesome.min.css', __FILE__) );
		extract($args);
		
		echo $args['before_widget'];
		
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		
		$limit = $instance['limit'];
		$query = $instance['query'];
		$city = $instance['city'];
		$state = $instance['state'];
		$zip = $instance['zip'];
		$country = $instance['country'];
		$start_date = $instance['start_date'];

		//build widget
		$widget_data = active_events_build($query, $city, $state, $zip, $country, $start_date, $limit);

		echo $widget_data;
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

		
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'query' => '', 'city' => '',  'state' => '',  'zip' => '',  'country' => '',  'start_date' => '',  'limit' => '5' ) );

		$title 	 = esc_attr( $instance['title'] );
		$limit 	 = esc_attr( $instance['limit'] );
		$query 	 = esc_attr( $instance['query'] );
		$city 	 = esc_attr( $instance['city'] );
		$state 	 = esc_attr( $instance['state'] );
		$zip 	 = esc_attr( $instance['zip'] );
		$country = esc_attr( $instance['country'] );
		$start_date = esc_attr( $instance['start_date'] );
		echo '<script type="text/javascript">
				jQuery(document).ready(function($) {
					$(".custom_date").datepicker({
						dateFormat : "yy-mm-dd"
					});
				});
			  </script>';

		echo '<p><label for="'.esc_attr($this->get_field_id('title')).'">'.__('Title:','active-events-widget').'</label>';
		echo '<input class="widefat" id="'.esc_attr($this->get_field_id('title')).'"  name="'.esc_attr($this->get_field_name('title')).'" type="text" value="'.esc_attr($title).'" /></p>';

		echo '<p><label for="'.esc_attr($this->get_field_id('query')).'">'.__('Search String:','active-events-widget').'</label>';
		echo '<input class="widefat" id="'.esc_attr($this->get_field_id('query')).'" name="'.esc_attr($this->get_field_name('query')).'" type="text" value="'.esc_attr($query).'" /><span>'.__('Search by keywords','active-events-widget').'</span></p>';
		
		echo '<p><label for="'.esc_attr($this->get_field_id('city')).'">'.__('City:','active-events-widget').'</label>';
		echo '<input class="widefat" id="'.esc_attr($this->get_field_id('city')).'" name="'.esc_attr($this->get_field_name('city')).'" type="text" value="'.esc_attr($city).'" /></p>';
		
		echo '<p><label for="'.esc_attr($this->get_field_id('state')).'">'.__('State:','active-events-widget').'</label>';
		echo '<input class="widefat" id="'.esc_attr($this->get_field_id('state')).'" name="'.esc_attr($this->get_field_name('state')).'" type="text" value="'.esc_attr($state).'" /></p>';
		
		echo '<p><label for="'.esc_attr($this->get_field_id('zip')).'">'.__('Zip Code:','active-events-widget').'</label>';
		echo '<input class="widefat" id="'.esc_attr($this->get_field_id('zip')).'" name="'.esc_attr($this->get_field_name('zip')).'" type="text" value="'.esc_attr($zip).'" /></p>';
		
		echo '<p><label for="'.esc_attr($this->get_field_id('country')).'">'.__('Country:','active-events-widget').'</label>';
		echo '<input class="widefat" id="'.esc_attr($this->get_field_id('country')).'" name="'.esc_attr($this->get_field_name('country')).'" type="text" value="'.esc_attr($country).'" /></p>';
		
		echo '<p><label for="'.esc_attr($this->get_field_id('start_date')).'">'.__('Start Date:','active-events-widget').'</label>';
		echo '<input type="date" class="custom_date widefat" id="'.esc_attr($this->get_field_id('start_date')).'" name="'.esc_attr($this->get_field_name('start_date')).'" type="text" value="'.esc_attr($start_date).'" /><<span>'.__('Pick the day you want the events to start from. Leave blank to pull the latest dates','active-events-widget').'</span></p>';
				
		echo '<p><label for="'.esc_attr($this->get_field_id('limit')).'">'.__('Number of Results:','active-events-widget').'</label>';
		echo '<input class="widefat" id="'.esc_attr($this->get_field_id('limit')).'"  name="'.esc_attr($this->get_field_name('limit')).'" type="text" value="'.esc_attr($limit).'" /><span>'.__('Default is 5','active-events-widget').'</span></p>';
	}
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['query'] = strip_tags($new_instance['query']);
		$instance['city'] = strip_tags($new_instance['city']);
		$instance['state'] = strip_tags($new_instance['state']);
		$instance['zip'] = strip_tags($new_instance['zip']);
		$instance['country'] = strip_tags($new_instance['country']);
		$instance['start_date'] = strip_tags($new_instance['start_date']);
		$instance['limit'] = strip_tags($new_instance['limit']);
		return $instance;
	}


}


/* === Shortcodes === */
function activeevents($params, $content=null){
	extract(shortcode_atts( array (
				'query' => '', 
				'city' => '',  
				'state' => '',  
				'zip' => '',  
				'country' => '',  
				'start_date' => '',  
				'limit' => '5'
			), $params ) );

	$events = active_events_build($query, $city, $state, $zip, $country, $start_date, $limit);
	return $events;
}
add_shortcode('activeevents', 'activeevents');


/* === Helper Functions === */

function active_events_build($query, $city, $state, $zip, $country, $start_date, $limit){
	
	if($start_date == ""){
		$today = date('Y-m-d');
	}else{
		$today = $start_date;
	}
	
	$query = str_replace(" ", "+", $query);
	
	$url = 'http://api.amp.active.com/v2/search/?city='.$city.'&state='.$state.'&zip='.$zip.'&country='.$country.'&query='.$query.'&current_page=1&per_page='.$limit.'&sort=date_asc&start_date='.$today.'..&exclude_children=true&api_key=4yyqjd76brhy9gdp7zc648s2';
	

	//build curl() to active.com
	$curl = curl_init();
	curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"cache-control: no-cache"
			),
		));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if($err){
		$result = __('Sorry, There was an error in the API call. Please check your widget settings. Error: ','active-events-widget');
		print_r($err);
	}else if($response){

		$data = json_decode($response, true);
		$active_events = $data['results'];
		//print_r($data['results']);
		//format results
		$result = '<div class="active-events">';

		foreach($active_events as $event ){
			$start_date = $event['activityStartDate'];
			$date = substr($start_date, 0, strpos($start_date, "T"));
			$newDate = date("M j, Y", strtotime($date));

			$result .='<div class="event">';

			$result .='<h4 class="event-title"><a href="'.$event['registrationUrlAdr'].'" target="_blank">'.$event['assetName'].'</a></h4>';
			$result .= '<p class="clearfix"><span class="event-date"><span class="screen-reader-text">'.__('Date:','active-events-widget').'</span> <i class="fa fa-calendar-o"></i> '.$newDate.'</span>';
			$result .= '<span class="event-location"><i class="fa  fa-map-marker"></i><span class="screen-reader-text">'.__('Location:','active-events-widget').'</span> '.$event['place']['placeName'].'</span></p>';
			$result .= '<a class="more-link" target="_blank" href="'.$event['registrationUrlAdr'].'">'.__('View More','active-events-widget').'<span class="screen-reader-text">'.__('About','active-events-widget').' '.$event['assetName'].'</span><span class="fa-fw fa fa-caret-right"></span></a>';
			$result .= '</div>';
		}
		$result .= '</div>';
	}else{
		$result = __('No Response','active-events-widget');
	}
	return $result;
}
