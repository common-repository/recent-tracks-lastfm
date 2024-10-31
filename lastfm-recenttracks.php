<?php
/**
 * Plugin Name: Last.FM Recent Tracks
 * Plugin URI: http://maksimdegtyarev.me
 * Description: A Wordpress-plugin showing your recent tracks on last.fm.
 * Version: 1.0
 * Author: Maksim Degtyarev
 * Author URI: http://maksimdegtyarev.me
 * License: GPL2
 */


register_activation_hook(__FILE__, function(){
	add_option("lastfm-recenttracks-limit", '5', '', '5');
	add_option("lastfm-recenttracks-user", '', '', '');
});
register_deactivation_hook( __FILE__, function(){
	delete_option('lastfm-recenttracks-limit');
	delete_option('lastfm-recenttracks-user');
});

add_shortcode( 'lastfm', function(){
	$user = get_option('lastfm-recenttracks-user');
	$limit = get_option('lastfm-recenttracks-limit');
	
	$params = array(
					'method' 	=> 'user.getRecentTracks',
					'user' 		=> $user,
					'api_key' 	=> '989374961a3b0f0d55fa2711ec066d12',
					'limit' 	=> $limit
					);
	
	$request = file_get_contents('http://ws.audioscrobbler.com/2.0/?' . http_build_query($params, '', '&'));
	if($request != ""){
		$xml = new SimpleXMLElement($request);
		$tracks = $xml->recenttracks->track;
		
		$echo = '<ul class="recent-tracks">';
		foreach($tracks as $track){
			$limit--;
			$echo .= '<li class="track-item">
					<a href="' . $track->url . '">
						<img src="' . $track->image[0] . '" alt="' . $track->album . '">
						<div class="track-description">
							<span class="track-album">' . $track->album . '</span><br/>
							<span class="track-artist">' . $track->artist . '</span><br/>
							<span class="track-name">' . $track->name . '</span>
						</div>
					</a>
			</li>';
			if(!$limit){
				break;
			}
		}
		$echo .= "</ul>";
		return $echo;
	}
	else{
		return "";
	}
});

add_action( 'wp_enqueue_scripts', function(){
	wp_register_style( 'lastfm-style', plugins_url( '/lastfm-style.css', __FILE__ ), array(), false, 'all' );
	wp_enqueue_style( 'lastfm-style' );
});

if(is_admin()){
	add_action('admin_menu', function(){
		add_options_page('Last.FM Recent Tracks Plugin', 'Last.FM Recent Tracks', 'administrator', 'lastfmrecenttracks', function(){?>
			<div>
				<h2>Last.FM Recent Tracks Plugin options</h2>
				<form method="post" action="options.php">
					<?php wp_nonce_field('update-options'); ?>
					Enter last.fm username:<br/>
					<input name="lastfm-recenttracks-user" type="text" id="lastfm-recenttracks-user" value="<?php echo get_option('lastfm-recenttracks-user'); ?>" /><br/>
					Enter last.fm recent tracks limit:<br/>
					<input name="lastfm-recenttracks-limit" type="text" id="lastfm-recenttracks-limit" value="<?php echo get_option('lastfm-recenttracks-limit'); ?>" /><br/>		
					<input type="hidden" name="action" value="update" />
					<input type="hidden" name="page_options" value="lastfm-recenttracks-user,lastfm-recenttracks-limit" />
					
					<input type="submit" value="<?php _e('Submit') ?>" />
				</form>
			</div>
		<?});
	});
}