<?php
/**
 * Plugin Name: WikiTip Knowledge Cluster ToolTip for Wordpress
 * Plugin URI: http://wikitip.info/website-integration/
 * Description: Use terms definitions from knowledge clusters at wikitip.info
 * Author: Richard Vencu
 * Author URI: http://richardconsulting.ro
 * Version: 1.5
 * License: GPLv2, MIT, GNU
 *
 *  1. Copyright 2011  Richard Vencu  (email : richard.vencu@richardconsulting.ro)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * 2. Redistribution of "Simple PHP Proxy" Version: 1.6 script
 * Project Home - http://benalman.com/projects/php-simple-proxy/
 * GitHub       - http://github.com/cowboy/php-simple-proxy/
 * Source       - http://github.com/cowboy/php-simple-proxy/raw/master/ba-simple-proxy.php
 * Copyright (c) 2010 "Cowboy" Ben Alman,
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 *
 * 3. Redistribution of "jQuery Thesaurus" (jquery.thesaurus.js) script licensed under GNU
 * @copyright (c) Dmitry Sheiko http://www.cmsdevelopment.com
 */
$controller = 'dorian';

$wikitip_all_blogs = array();
$postId            = 0;

register_activation_hook( __FILE__, 'wikitip_install' );

register_deactivation_hook( __FILE__, 'wikitip_deactivation' );

add_action( 'after_setup_theme', 'wikitip_setup' );

require_once( 'wikitip_post_metabox.php' );

function wikitip_install() {

	/* Declare default values */
	$wikitip_options = array(

		'username' => '',

		'secret' => '',

		'domain' => '',

		'cluster' => '',

		'casesensitive' => 0,

		'containers' => '',

		'delay' => 250,

		'effect' => '',

		'frontpage' => 0,

		'search' => 0,

		'archive' => 0,

		'author' => 0,

		'category' => 0,

		'tag' => 0,

		'loggedinonly' => 1,

		'salt' => '',

		'minsize' => 1,

		'zindex' => 'auto',

		'usercontrol' => 1,

		'inflexions' => 0,

		'show_count' => - 1,

		'title_exact_term_sorting_weight' => 5,

		'content_exact_term_sorting_weight' => 2,

		'title_inflected_term_sorting_weight' => 3,

		'content_inflected_term_sorting_weight' => 1,

		'matchlang' => 1

	);

	/* At first activation push values to database */
	if ( is_multisite() ) {

		global $wikitip_all_blogs;

		wikitip_retrieve_blogs();

		foreach ( $wikitip_all_blogs as $blog ) {
			if ( ! get_blog_option( $blog, 'wikitip_options' ) ) {
				update_blog_option( $blog, 'wikitip_options', $wikitip_options );
			}
		}
	} else {
		if ( ! get_option( 'wikitip_options' ) ) {
			update_option( 'wikitip_options', $wikitip_options );
		}
	}
}

function wikitip_deactivation() {

	/* Delete options */
}

function wikitip_setup() {

	/* Load translation */
	load_plugin_textdomain( 'wikitip', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	/* Add filters, actions, and theme-supported features. */

	/* Add theme-supported features. */

	/* Add custom actions. */
	add_action( 'loop_start', 'wikitip_displayon' );
	add_action( 'admin_menu', 'wikitip_admin_page' );
	add_action( 'admin_init', 'wikitip_admin_init' );
	add_action( 'wpmu_new_blog', 'wikitip_init_newblog' );
	//add_action( 'admin_menu', 'wikitip_load_effects' );
	/* Add custom filters. */

}

function wikitip_init_newblog() {
	global $blog_id;

	/* Declare default values */
	$wikitip_options = array(

		'username' => '',

		'secret' => '',

		'domain' => '',

		'cluster' => '',

		'casesensitive' => 0,

		'containers' => '',

		'delay' => 250,

		'effect' => '',

		'frontpage' => 0,

		'search' => 0,

		'archive' => 0,

		'author' => 0,

		'category' => 0,

		'tag' => 0,

		'loggedinonly' => 1,

		'salt' => '',

		'minsize' => 1,

		'zindex' => 'auto',

		'usercontrol' => 1,

		'inflexions' => 0,

		'show_count' => - 1,

		'title_exact_term_sorting_weight' => 5,

		'content_exact_term_sorting_weight' => 2,

		'title_inflected_term_sorting_weight' => 3,

		'content_inflected_term_sorting_weight' => 1,

		'matchlang' => 1

	);

	update_blog_option( $blog_id, 'wikitip_options', $wikitip_options );

}

function wikitip_load_header() {
	/* enqueue css and js files */

	wp_enqueue_style( 'wikitip_style', plugins_url( '/css/wikitip.css', __FILE__ ) );

	wp_register_script( 'thesaurus_js', plugins_url( '/js/jquery.thesaurus.min.js', __FILE__ ), array( 'jquery' ), '' );

	wp_register_script( 'wikitip-init', plugins_url( '/js/wikitip-init.min.js', __FILE__ ), array( 'thesaurus_js' ), '' );

	global $blog_id, $controller, $filter_dict;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$nonce = '';
	for ( $i = 0; $i < 10; $i ++ ) {
		$nonce .= substr( "./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789", mt_rand( 0, 63 ), 1 );
	}

	//get original language of the site
	$arr  = explode( '-', get_bloginfo( 'language' ) );
	$lang = $arr[0];
	//check for qTranslate plugin
	if ( function_exists( 'qtrans_getLanguage' ) ) {
		$lang = qtrans_getLanguage();
	}
	//check for WPML plugin
	if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
		$lang = ICL_LANGUAGE_CODE;
	}

	$username    = $wikitip_options['username'];
	$secret      = $wikitip_options['secret'];
	$cluster     = $wikitip_options['cluster'];
	$salt        = $wikitip_options['salt'];
	$minsize     = $wikitip_options['minsize'];
	$containers  = $wikitip_options['containers'];
	$delay       = $wikitip_options['delay'];
	$domain      = $wikitip_options['domain'];
	$inflex      = $wikitip_options['inflexions'];
	$usercontrol = $wikitip_options['usercontrol'];
	$matchlang   = $wikitip_options['matchlang'];

	$tew = $wikitip_options['title_exact_term_sorting_weight'];
	$cew = $wikitip_options['content_exact_term_sorting_weight'];
	$tiw = $wikitip_options['title_inflected_term_sorting_weight'];
	$ciw = $wikitip_options['content_inflected_term_sorting_weight'];

	$zindex = $wikitip_options['zindex'];
	if ( empty( $zindex ) ) {
		$zindex = 'auto';
	}

	$url = wikitip_url();

	$effect = $wikitip_options['effect'];
	if ( empty( $effect ) ) {
		$effect = 'null';
	}

	$case          = $wikitip_options['casesensitive'];
	$casesensitive = 'false';
	if ( $case == 1 ) {
		$casesensitive = 'true';
	}

	$timestamp = time();
	$key       = get_JSON_APIkey( $nonce, $secret . $timestamp, $salt );

	$def_uri         = 'https://' . $cluster . '.wikitip.info/apis/' . $controller . '/get_network_posts_by_wikiterm/?lang=' . $lang . '&user=' . $username . '&key=' . $key . '&nonce=' . $nonce . '&timestamp=' . $timestamp . '&tew=' . $tew . '&cew=' . $cew . '&tiw=' . $tiw . '&ciw=' . $ciw . '&wikiterm=';
	$remote_post_uri = urlencode( 'https://' . $cluster . '.wikitip.info/apis/' . $controller . '/get_network_thesaurus/?lang=' . $lang . '&matchlang=' . $matchlang . '&user=' . $username . '&key=' . $key . '&nonce=' . $nonce . '&timestamp=' . $timestamp . '&domain=' . $domain . '&url=' . $url . '&cluster=' . $cluster . '&minsize=' . $minsize . '&inflex=' . $inflex );
	$local_post_uri  = plugin_dir_url( __FILE__ ) . 'ba-simple-proxy.php';

// Localize the script with new data
	$init_data = array(
		'TOOLTIP_LOADING_TPL'  => __( 'Loading', 'wikitip' ),
		'TOOLTIP_BODY_TPL'     => '<div class="thesaurus-header"><a class="reference" target="_blank" href="https://wikitip.info/">' . _e( 'A WikiTip Cluster', 'wikitip' ) . '</a><a class="term"></a></div><div class="thesaurus-body"></div>',
		'zetind'               => $zindex,
		'ACTIVE'               => $usercontrol == 1 ? 1 : 0,
		'caseSentitive'        => $casesensitive,
		'delay'                => $delay,
		'containers'           => $containers,
		'effect'               => $effect,
		'JSON_DEF_URI'         => $def_uri,
		'JSON_REMOTE_POST_URI' => $remote_post_uri,
		'JSON_LOCAL_POST_URI'  => $local_post_uri,
		'MESSAGE1'             => __( 'Corrupted response format. Contact the webmaster.', 'wikitip' ),
		'MESSAGE2'             => __( 'Filed in', 'wikitip' ),
		'MESSAGE3'             => __( 'under', 'wikitip' ),
		'MESSAGE4'             => __( 'No comments yet', 'wikitip' ),
		'MESSAGE5'             => __( 'Add comment', 'wikitip' ),
		'MESSAGE6'             => __( 'comment', 'wikitip' ),
		'MESSAGE7'             => __( 'comments', 'wikitip' ),
		'MESSAGE8'             => __( 'Error', 'wikitip' ),
		'MESSAGE9'             => empty( $filter_dict ) ? __( 'There is no definition for this term. Please contact the website admin.', 'wikitip' ) : __( 'There is no definition for this term in the selected glossaries. Please try different selection', 'wikitip' ),
		'MESSAGE10'            => __( 'wikis found', 'wikitip' ),
		'MESSAGE11'            => __( 'A WikiTip Thesaurus', 'wikitip' ),
		'MESSAGE12'            => __( 'Showing definitions', 'wikitip' ),
		'show_count'           => $wikitip_options['show_count']
	);
	wp_localize_script( 'wikitip-init', 'init', $init_data );

// Enqueued script with localized data.
	wp_enqueue_script( 'wikitip-init' );

	wp_enqueue_script( 'thesaurus_js' );

	wp_register_script( 'pagination_js', plugins_url( '/js/jquery.wikitippagination.min.js', __FILE__ ), array( 'jquery' ), '' );

	wp_enqueue_script( 'pagination_js' );

}

function wikitip_load_effects() {

	wp_enqueue_script( 'scriptaculous-effects' );

}


function wikitip_load_ui() {
	global $controller, $filter_dict, $blog_id;
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}
	$cluster = $wikitip_options['cluster'];
	$path    = 'https://' . $cluster . '.wikitip.info/apis/' . $controller . '/get_category_index/';

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $path );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, 0 );

	// Getting results
	$result = curl_exec( $ch ); // Getting jSON result string
	curl_close( $ch );

	$cats = json_decode( $result );
	?>
    <div class="panel">
        <h3><?php _e( 'WikiTip Configuration Panel', 'wikitip' ); ?></h3>
        <p><?php _e( 'Here you can control the behavior of WikiTip tooltips.', 'wikitip' ); ?></p>
        <h4><?php _e( 'Enable tooltips', 'wikitip' ); ?></h4>
        <label for="wikitip-onoff"><input type="checkbox" name="wikitip-onoff"
                                          id="wikitip-onoff" <?php if ( $_COOKIE['wikitip_onoff'] == 'on' ) {
				echo 'checked="checked" ';
			} ?> onchange="if(this.checked){
						setCookie('wikitip_onoff', 'on');
						location.reload();
					}
					else{
						setCookie('wikitip_onoff', 'off');
						location.reload();
					}"/><span>wikitip on/off</span></label>
        <h4><?php _e( 'Filter by dictionaries', 'wikitip' ); ?></h4>
        <p><?php _e( 'Your selection will be saved using cookies.', 'wikitip' ); ?></p>
		<?php
		$dicts = $cats->categories;
		sort_on_field( $dicts, 'post_count', 'DESC' );
		echo "<table border='0' cellspacing='0' cellpadding='2' class='wikitip_control'>";
		foreach ( $dicts as $dict ) {
			echo "<tr><td><label for=" . $dict->id . "><input type=checkbox id='" . $dict->id . "' name='" . $dict->id . "' ";
			if ( $_COOKIE[ $cluster . $dict->id ] == 'on' ) {
				echo 'checked="checked" ';
			}
			echo 'onchange="if(this.checked){
						setCookie(\'' . $cluster . $dict->id . '\', \'on\');
						filter.push(\'' . $dict->id . '\');
						thes.cache = {};
					}
					else{
						setCookie(\'' . $cluster . $dict->id . '\', \'off\');
						removeElementFromArray(filter,\'' . $dict->id . '\');
						thes.cache = {};
					}"';
			echo " /><span>" . $dict->id . "</span></label></td><td>" . $dict->title . "<br /><strong>" . __( "Definitions count", "wikitip" ) . ": " . $dict->post_count . "</strong></td></tr>";
			if ( $_COOKIE[ $cluster . $dict->id ] == 'on' ) {
				echo '<script type="text/javascript">filter.push(\'' . $dict->id . '\');</script>';
			}
		}
		echo "</table>";


		?>
    </div>
    <a class="trigger" href="#">wikitip</a>
	<?php
}

function wikitip_displayon() {

	global $blog_id;
	global $post;
	$singular = 0;

	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	if ( is_singular() ) {
		$singular = 1;
		if ( get_post_meta( $post->ID, 'wikitip_exclude', true ) ) {
			$singular = 0;
		}
	}

	if ( ! is_admin() ) {
		if ( ( $wikitip_options['loggedin'] == 1 && is_user_logged_in() ) || $wikitip_options['loggedin'] == 0 ) {
			if ( ( $singular == 1 && ! is_front_page() || $wikitip_options['frontpage'] == 1 && is_front_page() ) ||
			     ( $wikitip_options['search'] == 1 && is_search() ) ||
			     ( $wikitip_options['archive'] == 1 && is_archive() ) ||
			     ( $wikitip_options['category'] == 1 && is_category() ) ||
			     ( $wikitip_options['author'] == 1 && is_author() ) ||
			     ( $wikitip_options['tag'] == 1 && is_tag() )
			) {
				add_action( 'wp_footer', 'wikitip_load_header' );

				if ( $wikitip_options['usercontrol'] == 1 ) {
					add_action( 'wp_print_footer_scripts', 'wikitip_load_ui', 98 );
				}
			}
		}
	}

}

function get_JSON_APIkey( $nonce = null, $secret = null, $salt = null ) {
	if ( $nonce && $secret && $salt ) {
		return crypt( $secret . $nonce, $salt );
	}

	return false;
}

/* Setup the admin options page */
function wikitip_admin_page() {

	add_options_page(

		__( 'WikiTip Settings Page', 'wikitip' ),

		__( 'WikiTip', 'wikitip' ),

		'manage_options',

		__FILE__,

		'wikitip_admin_settings_page'
	);
}

/*  Draw the option page */
function wikitip_admin_settings_page() {

	?>

    <div class="wrap">

        <h2><?php echo __( 'WikiTip Knowledge Cluster Tooltip for WordPress', 'wikitip' ); ?></h2>

        <form action="options.php" method="post">

			<?php settings_fields( 'wikitip_options' ); ?>

			<?php do_settings_sections( 'wikitip' ); ?>

            <p><input name="Submit" type="submit" value="<?php _e( 'Save Changes', 'wikitip' ); ?>" class="button"/></p>

        </form>

    </div>

	<?php
}

/* Register and define the settings */
function wikitip_admin_init() {

	register_setting(
		'wikitip_options',
		'wikitip_options',
		'wikitip_validate_options'
	);

	add_settings_section(
		'wikitip_main',
		__( 'Account information', 'wikitip' ),
		'wikitip_section_text',
		'wikitip'
	);

	add_settings_field(
		'wikitip_username',
		__( 'Username', 'wikitip' ),
		'wikitip_setting_input1',
		'wikitip',
		'wikitip_main'
	);

	add_settings_field(
		'wikitip_secret',
		__( 'Secret', 'wikitip' ),
		'wikitip_setting_input2',
		'wikitip',
		'wikitip_main'
	);

	add_settings_field(
		'wikitip_salt',
		__( 'Encryption SALT (Blowfish)', 'wikitip' ),
		'wikitip_setting_input3',
		'wikitip',
		'wikitip_main'
	);

	add_settings_field(
		'wikitip_domain',
		__( 'Your domain name. Be careful to include www if necessary.', 'wikitip' ),
		'wikitip_setting_input4',
		'wikitip',
		'wikitip_main'
	);

	add_settings_section(
		'wikitip_cluster_options',
		__( 'Set cluster usage options', 'wikitip' ),
		'wikitip_section_text',
		'wikitip'
	);

	add_settings_field(
		'wikitip_cluster',
		__( 'Knowledge Cluster to use', 'wikitip' ),
		'wikitip_setting_input5',
		'wikitip',
		'wikitip_cluster_options'
	);

	add_settings_field(
		'wikitip_minsize',
		__( 'The minimum size of terms', 'wikitip' ),
		'wikitip_setting_input9',
		'wikitip',
		'wikitip_cluster_options'
	);

	add_settings_field(
		'wikitip_inflexions',
		__( 'Terms inflexions', 'wikitip' ),
		'wikitip_setting_checkbox11',
		'wikitip',
		'wikitip_cluster_options'
	);

	add_settings_field(
		'wikitip_casesensitive',
		__( 'Case sensitivity', 'wikitip' ),
		'wikitip_setting_checkbox1',
		'wikitip',
		'wikitip_cluster_options'
	);

	add_settings_field(
		'wikitip_matchlang',
		__( 'Match cluster\'s language', 'wikitip' ),
		'wikitip_setting_checkbox12',
		'wikitip',
		'wikitip_cluster_options'
	);

	add_settings_field(
		'wikitip_make_trie',
		__( 'Trie objects', 'wikitip' ),
		'wikitip_setting_status1',
		'wikitip',
		'wikitip_cluster_options'
	);

	add_settings_section(
		'wikitip_tooltip_options',
		__( 'Set tooltip usage options', 'wikitip' ),
		'wikitip_section_text',
		'wikitip'
	);

	add_settings_field(
		'wikitip_showcount',
		__( 'Maximum number of definitions display', 'wikitip' ),
		'wikitip_setting_input11',
		'wikitip',
		'wikitip_tooltip_options'
	);

	add_settings_field(
		'wikitip_containers',
		__( 'Selector for containers of target text', 'wikitip' ),
		'wikitip_setting_input6',
		'wikitip',
		'wikitip_tooltip_options'
	);

	add_settings_field(
		'wikitip_delay',
		__( 'Tooltip destruction delay (miliseconds)', 'wikitip' ),
		'wikitip_setting_input7',
		'wikitip',
		'wikitip_tooltip_options'
	);

	add_settings_field(
		'wikitip_zindex',
		__( 'Tooltip z-index value', 'wikitip' ),
		'wikitip_setting_input10',
		'wikitip',
		'wikitip_tooltip_options'
	);

	add_settings_field(
		'wikitip_effect',
		__( 'Tooltip construction effect', 'wikitip' ),
		'wikitip_setting_input8',
		'wikitip',
		'wikitip_tooltip_options'
	);

	add_settings_section(
		'wikitip_sorting',
		__( 'Sorting options', 'wikitip' ),
		'wikitip_section_text',
		'wikitip'
	);

	add_settings_field(
		'wikitip_title_exact',
		__( 'Exact term in title weight', 'wikitip' ),
		'wikitip_setting_input12',
		'wikitip',
		'wikitip_sorting'
	);

	add_settings_field(
		'wikitip_content_exact',
		__( 'Exact term in content weight', 'wikitip' ),
		'wikitip_setting_input13',
		'wikitip',
		'wikitip_sorting'
	);

	add_settings_field(
		'wikitip_title_inflected',
		__( 'Inflected term in title weight', 'wikitip' ),
		'wikitip_setting_input14',
		'wikitip',
		'wikitip_sorting'
	);

	add_settings_field(
		'wikitip_content_inflected',
		__( 'Inflected term in content weight', 'wikitip' ),
		'wikitip_setting_input15',
		'wikitip',
		'wikitip_sorting'
	);

	add_settings_section(
		'wikitip_display',
		__( 'Context for tooltips usage, besides singular pages', 'wikitip' ),
		'wikitip_section_text',
		'wikitip'
	);

	add_settings_field(
		'wikitip_usercontrol',
		__( 'Display user control panel', 'wikitip' ),
		'wikitip_setting_checkbox10',
		'wikitip',
		'wikitip_display'
	);

	add_settings_field(
		'wikitip_frontpage',
		__( 'Display on frontpage', 'wikitip' ),
		'wikitip_setting_checkbox2',
		'wikitip',
		'wikitip_display'
	);

	add_settings_field(
		'wikitip_search',
		__( 'Display on search page', 'wikitip' ),
		'wikitip_setting_checkbox3',
		'wikitip',
		'wikitip_display'
	);

	add_settings_field(
		'wikitip_archive',
		__( 'Display on archive pages', 'wikitip' ),
		'wikitip_setting_checkbox4',
		'wikitip',
		'wikitip_display'
	);

	add_settings_field(
		'wikitip_category',
		__( 'Display on category pages', 'wikitip' ),
		'wikitip_setting_checkbox5',
		'wikitip',
		'wikitip_display'
	);

	add_settings_field(
		'wikitip_author',
		__( 'Display on author pages', 'wikitip' ),
		'wikitip_setting_checkbox6',
		'wikitip',
		'wikitip_display'
	);

	add_settings_field(
		'wikitip_tag',
		__( 'Display on tag pages', 'wikitip' ),
		'wikitip_setting_checkbox7',
		'wikitip',
		'wikitip_display'
	);

	add_settings_field(
		'wikitip_members',
		__( 'Display only if users are logged in?', 'wikitip' ),
		'wikitip_setting_checkbox8',
		'wikitip',
		'wikitip_display'
	);

}

/*  Draw the section header */
function wikitip_section_text() {
	echo '<p>' . __( 'Enter your settings below.', 'wikitip' ) . '</p>';
}

/* Display and fill the form fields */
function wikitip_setting_input1() {

	/* Get option 'username' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['username'];

	/* Echo the field */
	echo "<input id='username' name='wikitip_options[username]' type='text' value='$text_string' />";
	echo "<p class='description'>" . __( "Please enter your username from wikitip.info", "wikitip" ) . "</p>";
}

function wikitip_setting_input2() {

	/* Get option 'secret' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['secret'];

	/* Echo the field */
	echo "<input id='secret' name='wikitip_options[secret]' type='text' value='$text_string' />";
	echo "<p class='description'>" . __( "Please copy your secret word from <a href='https://wikitip.info/my-subscription/'>My Subscription</a>", "wikitip" ) . "</p>";
}

function wikitip_setting_input3() {

	/* Get option 'salt' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['salt'];

	/* Echo the field */
	echo "<input id='salt' name='wikitip_options[salt]' type='text' value='$text_string' />";
	echo "<p class='description'>" . __( "Please copy your encryption SALT from <a href='https://wikitip.info/my-subscription/'>My Subscription</a>", "wikitip" ) . "</p>";
}

function wikitip_setting_input4() {

	/* Get option 'domain' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['domain'];

	/* Echo the field */
	echo "<input id='domain' name='wikitip_options[domain]' type='text' value='$text_string' />";
	echo "<p class='description'>" . __( "Please insert your domain and make sure it is corectly registered at <a href='https://wikitip.info/my-subscription/'>My Subscription</a>", "wikitip" ) . "</p>";
}

function wikitip_setting_input5() {
	/* Get option 'cluster' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['cluster'];

	/* Echo the field */
	echo "<input id='cluster' name='wikitip_options[cluster]' type='text' value='$text_string' />";
	echo "<p class='description'>" . __( "Please insert the knowledge cluster you want to use, such as `mycluster` where the cluster address would be `mycluster.wikitip.info`.", "wikitip" ) . "</p>";
}

function wikitip_setting_input6() {

	/* Get option 'containers' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['containers'];

	/* Echo the field */
	echo "<input id='containers' name='wikitip_options[containers]' type='text' value='$text_string' />";
	echo "<p class='description'>" . __( "Please insert the content containers to match the text for. It can be any HTML element such as `div.entry-content` or `strong` or `em`.", "wikitip" ) . "</p>";
}

function wikitip_setting_input7() {

	/* Get option 'delay' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['delay'];

	/* Echo the field */
	echo "<input id='delay' name='wikitip_options[delay]' type='text' value='$text_string' />";
	echo "<p class='description'>" . __( "Please insert a tooltip destruction delay in miliseconds. It may be useful for accidental mouse moves outside the tooltip area.", "wikitip" ) . "</p>";
}

function wikitip_setting_input8() {

	/* Get option 'effect' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['effect'];

	/* Echo the field */
	echo "<input id='effect' name='wikitip_options[effect]' type='text' value='$text_string' />";
	echo "<p class='description'>" . __( "Please insert the desired effect for tooltip display. Can be empty or `null`, `slide` and `fade`.", "wikitip" ) . "</p>";
}

function wikitip_setting_input9() {

	/* Get option 'minsize' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['minsize'];

	/* Echo the field */
	echo "<input id='minsize' name='wikitip_options[minsize]' type='text' value='$text_string' />";
	echo "<p class='description'>" . __( "Please insert the minimum length of terms to be matched. Useful if you want to avoid matching short terms against a broad dictionary.", "wikitip" ) . "</p>";
}

function wikitip_setting_input10() {

	/* Get option 'zindex' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['zindex'];

	/* Echo the field */
	echo "<input id='zindex' name='wikitip_options[zindex]' type='text' value='$text_string' />";
	echo "<p class='description'>" . __( "Please insert a tooltip z-index value. It can help in the case the tooltip is not shown with default values in your theme.", "wikitip" ) . "</p>";
}

function wikitip_setting_input11() {

	/* Get option 'show_count' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['show_count'];

	/* Echo the field */
	echo "<input id='show_count' name='wikitip_options[show_count]' type='text' value='$text_string' />";
	echo "<p class='description'>" . __( "Please insert the maximum number of definitions to show in the tooltip at a time. Use -1 for unlimited.", "wikitip" ) . "</p>";
}

function wikitip_setting_input12() {

	/* Get option 'title_exact_term_sorting_weight' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['title_exact_term_sorting_weight'];

	/* Echo the field */
	echo "<input id='title_exact_term_sorting_weight' name='wikitip_options[title_exact_term_sorting_weight]' type='text' value='$text_string' />";
	echo "<p class='description'>" . __( "Please insert the score calculation weight for matching the exact term in the definition title. Use 0 to ignore.", "wikitip" ) . "</p>";
}

function wikitip_setting_input13() {

	/* Get option 'content_exact_term_sorting_weight' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['content_exact_term_sorting_weight'];

	/* Echo the field */
	echo "<input id='content_exact_term_sorting_weight' name='wikitip_options[content_exact_term_sorting_weight]' type='text' value='$text_string' />";
	echo "<p class='description'>" . __( "Please insert the score calculation weight for matching the exact term in the definition content. Use 0 to ignore.", "wikitip" ) . "</p>";
}

function wikitip_setting_input14() {

	/* Get option 'title_inflected_term_sorting_weight' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['title_inflected_term_sorting_weight'];

	/* Echo the field */
	echo "<input id='title_inflected_term_sorting_weight' name='wikitip_options[title_inflected_term_sorting_weight]' type='text' value='$text_string' />";
	echo "<p class='description'>" . __( "Please insert the score calculation weight for matching any of the term inflexions in the definition title. Use 0 to ignore.", "wikitip" ) . "</p>";
}

function wikitip_setting_input15() {

	/* Get option 'content_inflected_term_sorting_weight' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['content_inflected_term_sorting_weight'];

	/* Echo the field */
	echo "<input id='content_inflected_term_sorting_weight' name='wikitip_options[content_inflected_term_sorting_weight]' type='text' value='$text_string' />";
	echo "<p class='description'>" . __( "Please insert the score calculation weight for matching any of the term inflexions in the definition content. Use 0 to ignore.", "wikitip" ) . "</p>";
}

function wikitip_setting_checkbox1() {

	/* Get option 'casesensitive' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['casesensitive'];

	/* Echo the field */
	echo "<input id='casesensitive' name='wikitip_options[casesensitive]' type='checkbox' value='1' ";

	checked( 1 == $text_string );

	echo " />";
	echo "<p class='description'>" . __( "Please check if you want terms matching to be case sensitive.", "wikitip" ) . "</p>";
}

function wikitip_setting_checkbox2() {

	/* Get option 'frontpage' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['frontpage'];

	/* Echo the field */
	echo "<input id='frontpage' name='wikitip_options[frontpage]' type='checkbox' value='1' ";

	checked( 1 == $text_string );

	echo " />";
	echo "<p class='description'>" . __( "Please check if you want the matching to happen in the frontpage.", "wikitip" ) . "</p>";
}

function wikitip_setting_checkbox3() {

	/* Get option 'search' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['search'];

	/* Echo the field */
	echo "<input id='search' name='wikitip_options[search]' type='checkbox' value='1' ";

	checked( 1 == $text_string );

	echo " />";
	echo "<p class='description'>" . __( "Please check if you want the matching to happen in the search results page.", "wikitip" ) . "</p>";
}

function wikitip_setting_checkbox4() {

	/* Get option 'archive' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['archive'];

	/* Echo the field */
	echo "<input id='archive' name='wikitip_options[archive]' type='checkbox' value='1' ";

	checked( 1 == $text_string );

	echo " />";
	echo "<p class='description'>" . __( "Please check if you want the matching to happen in the archive pages.", "wikitip" ) . "</p>";
}

function wikitip_setting_checkbox5() {

	/* Get option 'category' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['category'];

	/* Echo the field */
	echo "<input id='category' name='wikitip_options[category]' type='checkbox' value='1' ";

	checked( 1 == $text_string );

	echo " />";
	echo "<p class='description'>" . __( "Please check if you want the matching to happen in the category pages.", "wikitip" ) . "</p>";
}

function wikitip_setting_checkbox6() {

	/* Get option 'author' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['author'];

	/* Echo the field */
	echo "<input id='author' name='wikitip_options[author]' type='checkbox' value='1' ";

	checked( 1 == $text_string );

	echo " />";
	echo "<p class='description'>" . __( "Please check if you want the matching to happen in the author pages.", "wikitip" ) . "</p>";
}

function wikitip_setting_checkbox7() {

	/* Get option 'tag' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['tag'];

	/* Echo the field */
	echo "<input id='tag' name='wikitip_options[tag]' type='checkbox' value='1' ";

	checked( 1 == $text_string );

	echo " />";
	echo "<p class='description'>" . __( "Please check if you want the matching to happen in the tag pages.", "wikitip" ) . "</p>";
}

function wikitip_setting_checkbox8() {

	/* Get option 'loggedin' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['loggedin'];

	/* Echo the field */
	echo "<input id='loggedin' name='wikitip_options[loggedin]' type='checkbox' value='1' ";

	checked( 1 == $text_string );

	echo " />";
	echo "<p class='description'>" . __( "Please check if you want the matching to happen in the selected pages only for logged in users.", "wikitip" ) . "</p>";
}


function wikitip_setting_checkbox10() {

	/* Get option 'usercontrol' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['usercontrol'];

	/* Echo the field */
	echo "<input id='usercontrol' name='wikitip_options[usercontrol]' type='checkbox' value='1' ";

	checked( 1 == $text_string );

	echo " />";
	echo "<p class='description'>" . __( "Please check if you want to display an user control panel for the tooltips.<br />Note: when you uncheck this setting, tooltips will be enforced to all users regardless of their previous setting!", "wikitip" ) . "</p>";

}

function wikitip_setting_checkbox11() {

	/* Get option 'inflexions' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['inflexions'];

	/* Echo the field */
	echo "<input id='inflexions' name='wikitip_options[inflexions]' type='checkbox' value='1' ";

	checked( 1 == $text_string );

	echo " />";
	echo "<p class='description'>" . __( "Please check if you want to match terms inflexions.", "wikitip" ) . "</p>";
}

function wikitip_setting_checkbox12() {

	/* Get option 'matchlang' value from the database */
	global $blog_id;
	/* Read options */
	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}

	$text_string = $wikitip_options['matchlang'];

	/* Echo the field */
	echo "<input id='matchlang' name='wikitip_options[matchlang]' type='checkbox' value='1' ";

	checked( 1 == $text_string );

	echo " />";
	echo "<p class='description'>" . __( "Please check if you want to match current language with cluster's language.", "wikitip" ) . "</p>";
}

function wikitip_setting_status1() {

	global $controller, $blog_id;

	if ( is_multisite() ) {
		$wikitip_options = get_blog_option( $blog_id, 'wikitip_options' );
	} else {
		$wikitip_options = get_option( 'wikitip_options' );
	}
	$cluster = $wikitip_options['cluster'];
	$path    = 'https://' . $cluster . '.wikitip.info/apis/' . $controller . '/check_trie/';

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $path );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, 0 );

	// Getting results
	$result = curl_exec( $ch ); // Getting jSON result string
	curl_close( $ch );

	$trie       = json_decode( $result );
	$langs      = $trie->languages;
	$inflexions = $trie->inflexions;
	$terms      = $trie->terms;

	wp_register_script( 'wikitip-admin', plugins_url( '/js/wikitip-admin.js', __FILE__ ), array( 'jquery' ), '' );

	$init_data = array(
		'url1' => 'https://' . $cluster . '.wikitip.info/apis/' . $controller . '/make_trie/?inflex=1&lang=',
		'url2' => 'https://' . $cluster . '.wikitip.info/apis/' . $controller . '/make_trie/?lang='
	);

	wp_localize_script( 'wikitip-admin', 'init', $init_data );

// Enqueued script with localized data.
	wp_enqueue_script( 'wikitip-admin' );

	$text_string = __( "Your cluster has these trie objects in the following languages", "wikitip" ) . ":<br /><table id='trie_table'>";
	foreach ( $terms as $key => $term ) {
		$text_string .= '<tr><td>' . __( 'Terms', 'wikitip' ) . ' (' . $key . ')</td><td>' . __( 'Last updated', 'wikitip' ) . ': <span id="stamp' . $key . '" name="stamp' . $key . '">' . $term . '</span></td><td><input id="make_trie' . $key . '" name="make_trie' . $key . '" type="button" class="button" value="' . __( 'Make or update trie for terms in ', 'wikitip' ) . ' ' . $key . '" onclick="make_trie(false,\'' . $key . '\');" /></td></tr>';
	}
	foreach ( $inflexions as $key => $term ) {
		$text_string .= '<tr><td>' . __( 'Inflexions', 'wikitip' ) . ' (' . $key . ')</td><td>' . __( 'Last updated', 'wikitip' ) . ': <span id="stamp' . $key . 'i" name="stamp' . $key . 'i">' . $term . '</td><td><input id="make_trie' . $key . 'i" name="make_trie' . $key . 'i" type="button" class="button" value="' . __( 'Make or update trie for inflexions in ', 'wikitip' ) . ' ' . $key . '" onclick="make_trie(true,\'' . $key . '\');" /></td></tr>';
	}
	$text_string .= '</table>';
	/* Echo the field */
	echo $text_string . "<br /><p class='description'>" . __( "Click 'Make trie' buttons to regenerate your cluster's trie objects.", "wikitip" ) . "</p>";
}

/* Validate user input */
function wikitip_validate_options( $input ) {

	$valid = array();

	$valid['delay'] = preg_replace( '/[^0-9]/', '', $input['delay'] );

	if ( $valid['delay'] != $input['delay'] ) {

		$valid['delay'] = 250;
		add_settings_error(
			'wikitip_text_string',
			'wikitip_texterror',
			__( 'Incorrect value entered for delay number!', 'wikitip' ),
			'error'
		);
	}

	$valid['minsize'] = preg_replace( '/[^0-9]/', '', $input['minsize'] );

	if ( $valid['minsize'] != $input['minsize'] ) {

		$valid['minsize'] = 1;
		add_settings_error(
			'wikitip_text_string',
			'wikitip_texterror',
			__( 'Incorrect value entered for minimum size number!', 'wikitip' ),
			'error'
		);
	}

	$valid['title_exact_term_sorting_weight'] = preg_replace( '/[^0-9]/', '', $input['title_exact_term_sorting_weight'] );

	if ( $valid['title_exact_term_sorting_weight'] != $input['title_exact_term_sorting_weight'] ) {

		$valid['title_exact_term_sorting_weight'] = 0;
		add_settings_error(
			'wikitip_text_string',
			'wikitip_texterror',
			__( 'Incorrect value entered for weighting the exact term in titles!', 'wikitip' ),
			'error'
		);
	}

	$valid['content_exact_term_sorting_weight'] = preg_replace( '/[^0-9]/', '', $input['content_exact_term_sorting_weight'] );

	if ( $valid['content_exact_term_sorting_weight'] != $input['content_exact_term_sorting_weight'] ) {

		$valid['content_exact_term_sorting_weight'] = 0;
		add_settings_error(
			'wikitip_text_string',
			'wikitip_texterror',
			__( 'Incorrect value entered for weighting the exact term in content!', 'wikitip' ),
			'error'
		);
	}

	$valid['title_inflected_term_sorting_weight'] = preg_replace( '/[^0-9]/', '', $input['title_inflected_term_sorting_weight'] );

	if ( $valid['title_inflected_term_sorting_weight'] != $input['title_inflected_term_sorting_weight'] ) {

		$valid['title_inflected_term_sorting_weight'] = 0;
		add_settings_error(
			'wikitip_text_string',
			'wikitip_texterror',
			__( 'Incorrect value entered for weighting inflections of term in titles!', 'wikitip' ),
			'error'
		);
	}

	$valid['content_inflected_term_sorting_weight'] = preg_replace( '/[^0-9]/', '', $input['content_inflected_term_sorting_weight'] );

	if ( $valid['content_inflected_term_sorting_weight'] != $input['content_inflected_term_sorting_weight'] ) {

		$valid['content_inflected_term_sorting_weight'] = 0;
		add_settings_error(
			'wikitip_text_string',
			'wikitip_texterror',
			__( 'Incorrect value entered for weighting inflextions of term in content!', 'wikitip' ),
			'error'
		);
	}

	$valid['show_count'] = - 1;
	if ( $input['show_count'] != - 1 ) {
		$valid['show_count'] = preg_replace( '/[^0-9]/', '', $input['show_count'] );
	}

	if ( $valid['show_count'] != $input['show_count'] ) {

		$valid['show_count'] = - 1;
		add_settings_error(
			'wikitip_text_string',
			'wikitip_texterror',
			__( 'Incorrect value entered for maximum definitions number!', 'wikitip' ),
			'error'
		);
	}

	$valid['zindex'] = preg_replace( '/[^0-9]/', '', $input['zindex'] );

	if ( ! in_array( $input['zindex'], array( $valid['zindex'], 'auto', 'inherit' ) ) ) {

		add_settings_error(
			'wikitip_text_string',
			'wikitip_texterror',
			__( 'Incorrect value entered for z-index: it can be a number or `auto` or `inherit` only!', 'wikitip' ),
			'error'
		);
		$valid['zindex'] = 'auto';
	} else {
		$valid['zindex'] = $input['zindex'];
	}

	$valid['casesensitive'] = 0;

	if ( isset( $input['casesensitive'] ) && ( 1 == $input['casesensitive'] ) ) {
		$valid['casesensitive'] = 1;
	}

	$valid['frontpage'] = 0;

	if ( isset( $input['frontpage'] ) && ( 1 == $input['frontpage'] ) ) {
		$valid['frontpage'] = 1;
	}

	$valid['search'] = 0;

	if ( isset( $input['search'] ) && ( 1 == $input['search'] ) ) {
		$valid['search'] = 1;
	}

	$valid['archive'] = 0;

	if ( isset( $input['archive'] ) && ( 1 == $input['archive'] ) ) {
		$valid['archive'] = 1;
	}

	$valid['category'] = 0;

	if ( isset( $input['category'] ) && ( 1 == $input['category'] ) ) {
		$valid['category'] = 1;
	}

	$valid['tag'] = 0;

	if ( isset( $input['tag'] ) && ( 1 == $input['tag'] ) ) {
		$valid['tag'] = 1;
	}

	$valid['author'] = 0;

	if ( isset( $input['author'] ) && ( 1 == $input['author'] ) ) {
		$valid['author'] = 1;
	}

	$valid['loggedin'] = 0;

	if ( isset( $input['loggedin'] ) && ( 1 == $input['loggedin'] ) ) {
		$valid['loggedin'] = 1;
	}

	$valid['usercontrol'] = 0;

	if ( isset( $input['usercontrol'] ) && ( 1 == $input['usercontrol'] ) ) {
		$valid['usercontrol'] = 1;
	}

	$valid['inflexions'] = 0;

	if ( isset( $input['inflexions'] ) && ( 1 == $input['inflexions'] ) ) {
		$valid['inflexions'] = 1;
	}

	$valid['matchlang'] = 0;

	if ( isset( $input['matchlang'] ) && ( 1 == $input['matchlang'] ) ) {
		$valid['matchlang'] = 1;
	}

	if ( isset( $input['effect'] ) && ! in_array( $input['effect'], array( '', 'null', 'slide', 'fade' ) ) ) {

		add_settings_error(
			'wikitip_text_string',
			'wikitip_texterror',
			__( 'Incorrect effect name, can be only null, fade or slide!', 'wikitip' ),
			'error'
		);

	}

	$valid['username']   = $input['username'];
	$valid['secret']     = $input['secret'];
	$valid['salt']       = $input['salt'];
	$valid['domain']     = $input['domain'];
	$valid['cluster']    = $input['cluster'];
	$valid['containers'] = $input['containers'];
	$valid['effect']     = $input['effect'];

	return $valid;
}

function wikitip_retrieve_blogs() {
	/* Retrieve all blog ids */

	global $wpdb, $wikitip_all_blogs;

	$sql = "SELECT blog_id FROM $wpdb->blogs";

	$wikitip_all_blogs = $wpdb->get_col( $wpdb->prepare( $sql ) );
}

function wikitip_url() {
	$pageURL = 'http';
	if ( $_SERVER["HTTPS"] == "on" ) {
		$pageURL .= "s";
	}
	$pageURL .= "://";
	if ( $_SERVER["SERVER_PORT"] != "80" ) {
		$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	}

	return $pageURL;
}

function sort_on_field( &$objects, $on, $order = 'ASC' ) {
	/* 		$comparer = ($order === 'DESC')
				? "return -1 * gmp_cmp(\$a->{$on},\$b->{$on});"
				: "return gmp_cmp(\$a->{$on},\$b->{$on});";  */
	$comparer = ( $order === 'DESC' )
		? "if (\$a->{$on} > \$b->{$on}) {return -1;} elseif (\$a->{$on} == \$b->{$on}) {return 0;} else {return 1;}"
		: "if (\$a->{$on} > \$b->{$on}) {return 1;} elseif (\$a->{$on} == \$b->{$on}) {return 0;} else {return -1;}";
	usort( $objects, create_function( '$a,$b', $comparer ) );
}

