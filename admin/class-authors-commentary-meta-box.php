<?php

/**
 * Represents the Author's Commentary Meta Box.
 *
 * @link       http://code.tutsplus.com/tutorials/creating-maintainable-wordpress-meta-boxes-the-layout--cms-22208
 * @since      0.2.0
 *
 * @package    Author_Commentary
 * @subpackage Author_Commentary/admin
 */

/**
 * Represents the Author's Commentary Meta Box.
 *
 * Registers the meta box with the WordPress API, sets its properties, and renders the content
 * by including the markup from its associated view.
 *
 * @package    Author_Commentary
 * @subpackage Author_Commentary/admin
 * @author     Tom McFarlin <tom@tommcfarlin.com>
 */
class Authors_Commentary_Meta_Box {

	/**
	 * Register this class with the WordPress API
	 *
	 * @since    1.0.0
	 */
	public function initialize_hooks() {

		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );

	}

	/**
	 * The function responsible for creating the actual meta box.
	 *
	 * @since    0.2.0
	 */
	public function add_meta_box() {

		add_meta_box(
			'authors-commentary',
			"Author's Commentary",
			array( $this, 'display_meta_box' ),
			'post',
			'normal',
			'default'
		);

	}

	/**
	 * Renders the content of the meta box.
	 *
	 * @since    0.2.0
	 */
	public function display_meta_box() {
		include_once( 'views/authors-commentary-navigation.php' );
	}

	/**
	 * Loads all of the comments for the given post along with checkboxes used to
	 * indicate whether or not they've received a reply or not.
	 *
	 * @since    0.4.0
	 * @access   private
	 */
	private function load_post_comments() {

		$args = array(
			'post_id' => get_the_ID(),
			'status'  => 'approve'
		);
		$comments = get_comments( $args );

		return $comments;

	}

	/**
	 * Sanitizes and serializes the information associated with this post.
	 *
	 * @since    0.5.0
	 *
	 * @param    int    $post_id    The ID of the post that's currently being edited.
	 */
	public function save_post( $post_id ) {

		/* If we're not working with a 'post' post type or the user doesn't have permission to save,
		 * then we exit the function.
		 */
		if ( ! $this->user_can_save( $post_id, 'authors_commentary_nonce', 'authors_commentary_save' ) ) {
			return;
		}

		// If the 'Drafts' textarea has been populated, then we sanitize the information.
		if ( $this->value_exists( 'authors-commentary-drafts' ) ) {

			$this->update_post_meta(
				$post_id,
				'authors-commentary-drafts',
				$this->sanitize_data( 'authors-commentary-drafts' )
			);

		} else {
			$this->delete_post_meta( $post_id, 'authors-commentary-drafts' );
		}

		// If the 'Resources' inputs exist, iterate through them and sanitize them
		if ( $this->value_exists( 'authors-commentary-resources' ) ) {

			$this->update_post_meta(
				$post_id,
				'authors-commentary-resources',
				$this->sanitize_data( 'authors-commentary-resources', true )
			);

		} else {
			$this->delete_post_meta( $post_id, 'authors-commentary-resources' );
		}

		// If there are any values saved in the 'Published' input, save them
		if ( $this->value_exists( 'authors-commentary-comments' ) ) {

			$this->update_post_meta(
				$post_id,
				'authors-commentary-comments',
				$_POST['authors-commentary-comments']
			);

		} else {
			$this->delete_post_meta( $post_id, 'authors-commentary-comments' );
		}

	}

	/**
	 * Determines whether or not a value exists in the $_POST collection
	 * identified by the specified key.
	 *
	 * @since   1.0.0
	 *
	 * @param   string    $key    The key of the value in the $_POST collection.
	 * @return  bool              True if the value exists; otherwise, false.
	 */
	private function value_exists( $key ) {
		return ! empty( $_POST[ $key ] );
	}

	/**
	 * Deletes the specified meta data associated with the specified post ID
	 * based on the incoming key.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    int      $post_id    The ID of the post containing the meta data
	 * @param    string   $meta_key   The ID of the meta data value
	 */
	private function delete_post_meta( $post_id, $meta_key ) {

		if ( '' !== get_post_meta( $post_id, $meta_key, true ) ) {
			delete_post_meta( $post_id, $meta_key );
		}

	}

	/**
	 * Updates the specified meta data associated with the specified post ID
	 * based on the incoming key.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    int      $post_id    The ID of the post containing the meta data
	 * @param    string   $meta_key   The ID of the meta data value
	 * @param    mixed    $meta_value The sanitized meta data
	 */
	private function update_post_meta( $post_id, $meta_key, $meta_value ) {

		if ( is_array( $_POST[ $meta_key ] ) ) {
			$meta_value = array_filter( $_POST[ $meta_key ] );
		}

		update_post_meta( $post_id, $meta_key, $meta_value );

	}

	/**
	 * Sanitizes the data in the $_POST collection identified by the specified key
	 * based on whether or not the data is text or is an array.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string        $key                      The key used to retrieve the data from the $_POST collection.
	 * @param    bool          $is_array    Optional.    True if the incoming data is an array.
	 * @return   array|string                            The sanitized data.
	 */
	private function sanitize_data( $key, $is_array = false ) {

		$sanitized_data = null;

		if ( $is_array ) {

			$resources = $_POST[ $key ];
			$sanitized_data = array();

			foreach ( $resources as $resource ) {

				$resource = esc_url( strip_tags( $resource ) );
				if ( ! empty( $resource ) ) {
					$sanitized_data[] = $resource;
				}

			}

		} else {

			$sanitized_data = '';
			$sanitized_data = trim( $_POST[ $key ] );
			$sanitized_data = esc_textarea( strip_tags( $sanitized_data ) );

		}

		return $sanitized_data;

	}

	/**
	 * Verifies that the post type that's being saved is actually a post (versus a page or another
	 * custom post type.
	 *
	 * @since       0.5.0
	 * @access      private
	 * @return      bool      Return if the current post type is a post; false, otherwise.
	 */
	private function is_valid_post_type() {
		return ! empty( $_POST['post_type'] ) && 'post' == $_POST['post_type'];
	}

	/**
	 * Determines whether or not the current user has the ability to save meta data associated with this post.
	 *
	 * @since       0.5.0
	 * @access      private
	 * @param		int		$post_id	  The ID of the post being save
	 * @param       string  $nonce_action The name of the action associated with the nonce.
	 * @param       string  $nonce_id     The ID of the nonce field.
	 * @return		bool				  Whether or not the user has the ability to save this post.
	 */
	private function user_can_save( $post_id, $nonce_action, $nonce_id ) {

	    $is_autosave = wp_is_post_autosave( $post_id );
	    $is_revision = wp_is_post_revision( $post_id );
	    $is_valid_nonce = ( isset( $_POST[ $nonce_action ] ) && wp_verify_nonce( $_POST[ $nonce_action ], $nonce_id ) );

	    // Return true if the user is able to save; otherwise, false.
	    return ! ( $is_autosave || $is_revision ) && $this->is_valid_post_type() && $is_valid_nonce;

	}

}
