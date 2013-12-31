<?php
/* 
Plugin Name: The Mighty Mo Recent Posts Notifier
Description: Sends a user-specific email to all users of a WordPress site every 3am. Each email will include a list of the 5 most recent posts that the user is author of.  
Author: Sherwin Calims
Author URI: http://www.themightymo.com
*/ 


if ( ! wp_next_scheduled( 'my_task_hook' ) ) {
  wp_schedule_event( mktime(3,0,0,date('m'),date('d'),date('Y')), 'daily', 'my_task_hook' );
}

add_action( 'my_task_hook', 'my_task_function' );

function my_task_function() { 

	global $post;
	
	$users=get_users();
	
	foreach($users as $user){
	
		//The Query
		query_posts('author='.$user->ID);
        
		$content='Hello '.ucwords($user->user_nicename).',<br/> Please see the following posts: <br/><ul>';
		
		//The Loop
		$has_content=false;
		if ( have_posts() ) : while ( have_posts() ) : the_post();
		
			$content .= '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>'; 
			$has_content=true;
			
		endwhile;endif;
		
		$content.='</ul><br/>Sincerely, <br/>'.get_bloginfo('name');
	
		//Reset Query
		wp_reset_query();
		
		if($has_content){
			wp_mail( $user->user_email, 'Email from '.get_bloginfo('name').' on '.date("F j, Y"), $content);
		}
		
	}//foreach user
	
	remove_filter ( 'wp_mail_content_type', 'set_html_content_type' );
	
}

add_filter( 'wp_mail_content_type', 'set_html_content_type' );

function set_html_content_type() {
	return 'text/html';
}
