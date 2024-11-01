<?php

class wikitip_post_metabox {

	function admin_init() {
		/* List all post types */
		$post_types = get_post_types( '', 'names' );

		/* Eliminate certain post types from the list: mediapage, attachment, revision, nav_menu_item - (compatible up to WordPress version 3.2)  */
		foreach ( $post_types as $key => $value ) {

			if ( $value == 'mediapage' || $value == 'attachment' || $value == 'revision' || $value == 'nav_menu_item' ) {

				unset( $post_types[ $key ] );

			}
		}

		/*  */
		$screens = apply_filters( 'wikitip_post_metabox_screens', $post_types );

		foreach ( $screens as $screen ) {
			add_meta_box( 'wikitip', 'WikiTip Knowledge Clusters for WordPress', array(
				$this,
				'post_metabox'
			), $screen, 'side', 'default' );
		}
		add_action( 'save_post', array( $this, 'save_post' ) );

		add_filter( 'default_hidden_meta_boxes', array( $this, 'default_hidden_meta_boxes' ) );
	}

	function default_hidden_meta_boxes( $hidden ) {
		$hidden[] = 'wikitip';

		return $hidden;
	}

	function post_metabox() {
		global $post_id;

		if ( is_null( $post_id ) ) {
			$checked = '';
		} else {
			$custom_fields = get_post_custom( $post_id );
			$checked       = ( isset ( $custom_fields['wikitip_exclude'] ) ) ? 'checked="checked"' : '';
		}

		wp_nonce_field( 'wikitip_postmetabox_nonce', 'wikitip_postmetabox_nonce' );

		echo '<label for="wikitip_show_option">';

		_e( "Do not use WikiTip tooltips on this page:", 'wikitip' );

		echo '</label> ';

		echo '<input type="checkbox" id="wikitip_show_option" name="wikitip_show_option" value="1" ' . $checked . '>';
	}

	function save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['wikitip_postmetabox_nonce'] ) || ! wp_verify_nonce( $_POST['wikitip_postmetabox_nonce'], 'wikitip_postmetabox_nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['wikitip_show_option'] ) ) {
			delete_post_meta( $post_id, 'wikitip_exclude' );
		} else {
			$custom_fields = get_post_custom( $post_id );
			if ( ! isset ( $custom_fields['wikitip_exclude'][0] ) ) {
				add_post_meta( $post_id, 'wikitip_exclude', 'true' );
			} else {
				delete_post_meta( $post_id, 'wikitip_exclude' );
				add_post_meta( $post_id, 'wikitip_exclude', 'true' );
				//update_post_meta($post_id, 'wikitip_exclude', 'true' , $custom_fields['wikitip_exclude'][0]  );
			}
		}

	}

}

$wikitip_post_metabox = new wikitip_post_metabox;
add_action( 'admin_init', array( $wikitip_post_metabox, 'admin_init' ) );

