<?php

class ConfirmModeForCF7 {

	/**
	 * Singleton instance
	 */
	private static $instance = null;

	/**
	 * Singleton instance getter/creator
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * user options
	 */
	private $user_options_setting;
	private $user_options_default;

	/**
	 * Singleton constructor
	 */
	private function __construct() {

		load_plugin_textdomain( 'confirm-mode-for-contact-form-7' );

		$this->user_options_setting = array(
			'USE_CONFIRM_MODE' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'CONFIRM_BUTTON_TEXT' => array(
				'type' => 'string',
				'default' => __( 'Confirm', 'confirm-mode-for-contact-form-7' ),
			),
			'RETURN_BUTTON_TEXT' => array(
				'type' => 'string',
				'default' => __( 'Back', 'confirm-mode-for-contact-form-7' ),
			),
			'CONFIRM_MESSAGE' => array(
				'type' => 'string',
				'default' => __( 'If the entered information is correct, please send it.', 'confirm-mode-for-contact-form-7' ),
			),
			'AUTO_SCROLL' => array(
				'type' => 'boolean',
				'default' => true,
			),
		);

		$this->user_options_default = array();

		foreach ( $this->user_options_setting as $name => $value ) {
			$this->user_options_default[$name] = $value['default'];
		}

		add_action( 'init', array( $this, 'action_init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ) );
		add_filter( 'wpcf7_form_hidden_fields', array( $this, 'filter_wpcf7_form_hidden_fields' ) );
		add_action( 'wpcf7_before_send_mail', array( $this, 'action_wpcf7_before_send_mail' ), 99, 3 );
		add_action( 'wpcf7_mail_sent', array( $this, 'action_wpcf7_mail_sent' ) );

		if ( is_admin() ) {
			add_filter( 'wpcf7_editor_panels', array( $this, 'filter_wpcf7_editor_panels' ) );
			add_action( 'wpcf7_save_contact_form', array( $this, 'action_wpcf7_save_contact_form' ), 10, 3 );
			add_action( 'delete_post', array( $this, 'action_delete_post' ) );
		}
	}

	/**
	 * wp action hook init
	 */
	public function action_init() {

		session_start();
	}

	/**
	 * wp action hook wp_enqueue_scripts
	 */
	public function action_wp_enqueue_scripts() {

		$scripts = array(
			'/js/script.js',
		);
		$styles = array(
			'/css/style.css',
		);

		$url_base = plugins_url( '', dirname( __FILE__ ) );

		foreach ( $scripts as $i => $script ) {
			wp_enqueue_script( "cm4cf7-script-{$i}", $url_base . $script, array(), '1.0.0' );
		}

		foreach ( $styles as $i => $style ) {
			wp_enqueue_style( "cm4cf7-style-{$i}", $url_base . $style, array(), '1.0.0' );
		}
	}

	/**
	 * wp filter hook wpcf7_form_hidden_fields
	 */
	public function filter_wpcf7_form_hidden_fields( $hidden ) {

		// Embed <input type="hidden"> to pass form settings to the JS side
		$contact_form = WPCF7_ContactForm::get_current();
		$user_options = $this->load_user_options( $contact_form->id() );

		$json = json_encode( $user_options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		$hidden['_cm4cf7_user_options'] = $json;

		return $hidden;
	}

	/**
	 * wp action hook wpcf7_before_send_mail
	 */
	public function action_wpcf7_before_send_mail( $contact_form, &$abort, $submission ) {

		// Stop sending emails until confirm (if mode is enabled)
		$user_options = $this->load_user_options( $contact_form->id() );
		if ( empty( $user_options['USE_CONFIRM_MODE'] ) ) return;

		$is_request_confirm = ! empty( $_POST['_cm4cf7_request_confirm'] );
		$is_request_send = ! empty( $_POST['_cm4cf7_request_send'] );
		$key_confirm_ok = '_cm4cf7_confirm_ok_' . $contact_form->id();

		$response = $submission->get_response();

		if ( ! $abort && empty( $response ) ) {
			// Confirm
			if ( $is_request_confirm ) {
				$submission->set_response( 'CM4CF7_MAILSEND_ABORTED' );
				$_SESSION[$key_confirm_ok] = true;
				$abort = true;
				return;
			}
			// Send
			if ( $is_request_send && ! empty( $_SESSION[$key_confirm_ok] ) ) {
				return;
			}
		}
		if ( ! $abort ) {
			$sep = strlen( $response ) ? "\n" : '';
			$msg = __( 'Transmission aborted. (Confirm Mode for Contact Form 7)', 'confirm-mode-for-contact-form-7' );
			$submission->set_response( $response . $sep . $msg );
			$abort = true;
		}
		unset( $_SESSION[$key_confirm_ok] );
	}

	/**
	 * wp action hook wpcf7_mail_sent
	 */
	public function action_wpcf7_mail_sent( $contact_form ) {

		$key_confirm_ok = '_cm4cf7_confirm_ok_' . $contact_form->id();
		unset( $_SESSION[$key_confirm_ok] );
	}

	/**
	 * wp filter hook wpcf7_editor_panels
	 */
	public function filter_wpcf7_editor_panels( $panels ) {

		// Add [Confirm Mode] to Admin Form Setting Panel
		$panels['cm4cf7-panel'] = array(
			'title' => __( 'Confirm Mode', 'confirm-mode-for-contact-form-7' ),
			'callback' => array( $this, 'admin_editor_panel' ),
		);
		return $panels;
	}

	/**
	 * editor panel content
	 */
	public function admin_editor_panel( $contact_form ) {

		$user_options = $this->load_user_options( $contact_form->id() );

		include __DIR__ . '/admin_editor_panel.php';
	}

	/**
	 * wp action hook wpcf7_save_contact_form
	 */
	public function action_wpcf7_save_contact_form( $contact_form, $args, $context ) {

		if ( empty( $args['cm4cf7_user_options'] ) ) return;

		$input_user_options = $args['cm4cf7_user_options'];
		if ( ! is_array( $input_user_options ) ) $input_user_options = array();

		$user_options = array();

		foreach ( $this->user_options_setting as $name => $value ) {
			// Convert form checkbox to boolean type.
			// - When checkbox is checked, $_POST['name'] = 'on'.
			// - When checkbox is unchecked, $_POST has no key.
			if ( $value['type'] === 'boolean' ) {
				$user_options[$name] = ! empty( $input_user_options[$name] );
				continue;
			}
			// Form text
			if ( $value['type'] === 'string' ) {
				if ( array_key_exists( $name, $input_user_options ) ) {
					$user_options[$name] = (string)$input_user_options[$name];
				} else {
					$user_options[$name] = '';
				}
				continue;
			}
		}

		$this->save_user_options( $contact_form->id(), $user_options );
	}

	/**
	 * wp action hook delete_post
	 */
	public function action_delete_post( $post_id ) {

		$post = get_post( $post_id );

		if ( 'wpcf7_contact_form' === $post->post_type ) {
			delete_post_meta( $post->ID, 'cm4cf7_user_options' );
		}
	}

	/**
	 * Before uninstall
	 */
	public static function uninstall() {

		delete_post_meta_by_key( 'cm4cf7_user_options' );
	}

	/**
	 * Get options for specified form
	 */
	private function load_user_options( $post_id ) {

		$json = get_post_meta( $post_id, 'cm4cf7_user_options', true );

		$user_options = json_decode( $json, true );
		if ( ! is_array( $user_options ) ) $user_options = array();

		return array_merge( $this->user_options_default, $user_options );
	}

	/**
	 * Save options for specified form
	 */
	private function save_user_options( $post_id, $user_options ) {

		$json = json_encode( $user_options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

		// Need wp_slash() to pass to update_post_meta().
		// https://developer.wordpress.org/reference/functions/update_post_meta/#character-escaping
		update_post_meta( $post_id, 'cm4cf7_user_options', wp_slash( $json ) );
	}

}