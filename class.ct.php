<?php

class CalculatieTool {
	const API_ENDPOINT = 'https://app.calculatietool.com';

	private static $initiated = false;

	/**
	 * Initialize the hooks if class is not instanciated yet.
	 */
	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	}

	/**
	 * Initializes WordPress hooks
	 */
	private static function init_hooks() {
		self::$initiated = true;

		add_shortcode('ctsignup-form-signup', array( 'CalculatieTool', 'signup_form_signup' ) );
		add_shortcode('ctsignup-form-mail', array( 'CalculatieTool', 'signup_form_mail' ) );

		wp_enqueue_script( 'script', plugins_url( '/js/jquery.form-validator.min.js', __FILE__ ), array ( 'jquery' ) );

		add_action('wp_footer', array( 'CalculatieTool', 'jq_validator' ) );

		add_filter( 'wp_mail_from', array( 'CalculatieTool', 'wp_mail_from' ) );
		add_filter( 'wp_mail_from_name', array( 'CalculatieTool', 'wp_mail_from_name' ) );

		if ( is_admin() ) {
			add_action( 'admin_init', array( 'CalculatieTool', 'admin_init' ) );
			add_action( 'admin_menu', array( 'CalculatieTool', 'admin_menu' ) );
		}
	}

	/**
	 * Remote user address.
	 *
	 * @return string Remote address or null.
	 */
	private static function get_remote_addr() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} else if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			return $_SERVER['REMOTE_ADDR'];
		}
	}

	/**
	 * HTTP referer is send by the browser.
	 *
	 * @return string HTTP referer or null.
	 */
	private static function get_referer() {
		if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			return $_SERVER['HTTP_REFERER'];
		}
	}

	/**
	 * Register the admin settings and validation callbacks.
	 */
	public static function admin_init() {
		register_setting( 'ctsignup-settings-group', 'client_id', array( 'CalculatieTool', 'setting_size_check' ) );
		register_setting( 'ctsignup-settings-group', 'client_secret', array( 'CalculatieTool', 'setting_size_check' ) );
	}

	/**
	 * Register the admin menu callback.
	 */
	public static function admin_menu() {
		add_options_page( __('CTSignup', 'ctsignup'), __('CTSignup', 'ctsignup'), 'manage_options', 'ctsignup-config', array( 'CalculatieTool', 'load_options_page' ) );
	}

	/**
	 * Sender address.
	 *
	 * @return string Sender address.
	 */
	public static function wp_mail_from( $content_type ) {
		return 'info@calculatietool.com';
	}

	/**
	 * Sender name.
	 *
	 * @return string Sender name.
	 */
	public static function wp_mail_from_name( $name ) {
		return 'WPCTSignup';
	}

	/**
	 * Print admin settings page.
	 */
	public static function load_options_page() {
		?>
			<div class="wrap">
			<h1>CTSignup settings</h1>

			<form method="post" action="options.php">
			    <?php settings_fields( 'ctsignup-settings-group' ); ?>
			    <?php do_settings_sections( 'ctsignup-settings-group' ); ?>
			    <table class="form-table">
			        <tr valign="top">
			        	<th scope="row">Callback</th>
			        	<td><?php _e( get_site_url() ); ?></td>
			        </tr>

			        <tr valign="top">
			        	<th scope="row">Client ID</th>
			        	<td><input type="text" name="client_id" value="<?php _e( esc_attr( get_option( 'client_id') ) ); ?>" /></td>
			        </tr>
			         
			        <tr valign="top">
			        	<th scope="row">Client secret</th>
			        	<td><input type="password" name="client_secret" value="<?php _e( esc_attr( get_option( 'client_secret' ) ) ); ?>" /></td>
			        </tr>
					
					<tr valign="top">
						<th scope="row">
							<?php _e( 'Shortcode' ); ?>
						</th>
						<td>
							<div>
								<strong>[ctsignup-form-signup success="<em>{pagina}</em>" tags="<em>{tag1,tag2}</em>" id="<em>{id}</em>"]</strong>
							</div>
							<div>
								<p class="description"><?php _e( 'Gebruik deze shortcode voor de registratiepagina' ); ?></p>
							</div>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"></th>
						<td>
							<div>
								<strong>[ctsignup-form-mail success="<em>{pagina}</em>" tags="<em>{tag1,tag2}</em>" id="<em>{id}</em>"]</strong>
							</div>
							<div>
								<p class="description"><?php _e( 'Gebruik deze shortcode voor de contactpagina' ); ?></p>
							</div>
						</td>
					</tr>
			    </table>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ); ?>" />
					<a href="<?php _e( add_query_arg( 'verify', true ) ); ?>" type="button" class="button-secondary"><?php _e( 'Test Configuratie' ); ?><a/>
					<a href="<?php _e( add_query_arg( 'testmail', true ) ); ?>" type="button" class="button-secondary"><?php _e( 'Test Mail' ); ?><a/>
				</p>
			</form>
			</div>
		<?php
	}

	/**
	 * Validate the length of the client keys.
	 *
	 * @param string $input Key to validate.
	 * @return string The key on success, empty on failure.
	 */
	public static function setting_size_check( $input ) {
		if ( 40 != strlen( $input ) ) {
			add_settings_error( 'ctsignup-settings-keylength', esc_attr( 'settings-update' ), 'Invalid key provided' );
			return;
		}

		delete_transient( 'ctsignup_access_token' );

		return $input;
	}

	/**
	 * Retrieve the client id from either the wp settings or
	 * defined config file.
	 *
	 * @return string The client id.
	 */
	private static function get_client_id() {
		return defined( 'CTSIGNUP_CLIENT_ID' ) ? constant( 'CTSIGNUP_CLIENT_ID' ) : get_option( 'client_id' );
	}

	/**
	 * Retrieve the client secret from either the wp settings or
	 * defined config file.
	 *
	 * @return string The client secret.
	 */
	private static function get_client_secret() {
		return defined( 'CTSIGNUP_CLIENT_SECRET' ) ? constant( 'CTSIGNUP_CLIENT_SECRET' ) : get_option( 'client_secret' );
	}

	/**
	 * Build the access token URL.
	 *
	 * @return string The resulting URL.
	 */
	private static function get_token_url() {
		return self::API_ENDPOINT . '/oauth2/access_token';
	}

	/**
	 * Build the request URL towards the service.
	 *
	 * @param string $uri Part of the URl specific for the request.
	 * @return string The resulting URL.
	 */
	private static function build_api_url( $uri ) {
		return self::API_ENDPOINT . $uri;
	}

	/**
	 * Print script component. Validator is called and
	 * bound to the HTML form.
	 */
	public static function jq_validator() {
		?>
		<script type='text/javascript'>
		jQuery(document).ready(function() {
			jQuery.validate({
				form : '#ctsignup_signup_form',
				lang: 'nl',
				modules: [ 'security', 'sanitize' ]
			});
			jQuery.validate({
				form : '#ctsignup_mail_form',
				lang: 'nl',
				modules: [ 'sanitize' ]
			});
		});
		</script>
		<?php
	}

	/**
	 * Print the admin verification success banner.
	 */
	public static function ctsignup_admin_verify_ok() {
	    ?>
	    <div class="updated notice">
	        <p><?php _e( '<strong>Verbinding gelukt</strong>' ); ?></p>
	    </div>
	    <?php
	}

	/**
	 * Print the admin verification error banner.
	 */
	public static function ctsignup_admin_verify_error() {
	    ?>
	    <div class="error notice">
	        <p><?php _e( '<strong>Verbinding mislukt</strong>, controlleer de instellingen' ); ?></p>
	    </div>
	    <?php
	}

	/**
	 * Print the admin verification success banner.
	 */
	public static function ctsignup_admin_mail_ok() {
	    ?>
	    <div class="updated notice">
	        <p><?php _e( '<strong>Mail verzonden aan ' . get_bloginfo( 'admin_email' ) . '</strong>' ); ?></p>
	    </div>
	    <?php
	}

	/**
	 * Print the admin verification error banner.
	 */
	public static function ctsignup_admin_mail_error() {
	    ?>
	    <div class="error notice">
	        <p><?php _e( '<strong>Mail niet verzonden</strong>, controlleer de instellingen' ); ?></p>
	    </div>
	    <?php
	}

	/**
	 * Catch any errors that occur during the request.
	 *
	 * @return class WP_Error The wordpress error object.
	 */
	public static function ctsignup_errors() {
    	static $wp_error;
    	return isset( $wp_error ) ? $wp_error : ($wp_error = new WP_Error( null, null, null ) );
	}

	/**
	 * print the HTML error view.
	 */
	public static function signup_form_error_messages() {
		if ( $codes = self::ctsignup_errors()->get_error_codes() ) {
			echo '<div class="ctsignup_errors">';
			echo '<span>Oeps:</span><ul>';
			foreach ( $codes as $code ) {
				$message = self::ctsignup_errors()->get_error_message( $code );
				echo '<li class="ctsignup_error">' . $message . '</span><br/>';
			}
			echo '</ul></div>';
		}	
	}

	/**
	 * Return the HTML view.
	 *
	 * @param string|array $attrs Supplied shortcode attributes.
	 * @return string The HTML page.
	 */
	public static function signup_form_signup( $attrs ) {
		$redirect = "/";
		if ( isset( $attrs['success'] ) ) {
			$redirect = $attrs['success'];
		}

		if ( isset( $attrs['id'] ) ) {
			$id = $attrs['id'];
		}

		if ( isset( $attrs['tags'] ) ) {
			$tags = $attrs['tags'];
		}

		ob_start();

		require( CTSINGUP__INCLUDE_DIR . 'signup_form.include.php' );

		return ob_get_clean();
	}

	/**
	 * Return the HTML view.
	 *
	 * @param string|array $attrs Supplied shortcode attributes.
	 * @return string The HTML page.
	 */
	public static function signup_form_mail( $attrs ) {
		$redirect = "/";
		if ( isset( $attrs['success'] ) ) {
			$redirect = $attrs['success'];
		}

		if ( isset( $attrs['id'] ) ) {
			$id = $attrs['id'];
		}

		if ( isset( $attrs['tags'] ) ) {
			$tags = $attrs['tags'];
		}

		ob_start();

		require( CTSINGUP__INCLUDE_DIR . 'mail_form.include.php' );

		return ob_get_clean();
	}

	/**
	 * Handle the form POST request from the frontend. Perform
	 * basic validation to ease the service and gain faster feedback.
	 */
	private static function process_signup_form() {
		$first_name    = sanitize_text_field( $_POST["ctsignup_signup_first"] );
		$last_name     = sanitize_text_field( $_POST["ctsignup_signup_last"] );
		$phone         = sanitize_text_field( $_POST["ctsignup_signup_phone"] );
		$company       = sanitize_text_field( $_POST["ctsignup_signup_company"] );
		$account       = sanitize_text_field( $_POST["ctsignup_signup_account"] );
		$email         = sanitize_email( $_POST["ctsignup_signup_email"] );
		$password      = sanitize_text_field( $_POST["ctsignup_signup_pass"] );
		$password2     = sanitize_text_field( $_POST["ctsignup_signup_pass_confirm"] );
		$redirect      = sanitize_text_field( $_POST["ctsignup_signup_form_redirect"] );
		$tags          = @sanitize_text_field( $_POST["ctsignup_signup_form_tags"] );

		if ( ! $first_name || ! $last_name ) {
			self::ctsignup_errors()->add('empty_names', __('Voor en achternaam zijn verplicht') );
		}

		if ( ! $account ) {
			self::ctsignup_errors()->add('empty_account', __('Gebruikersnaam is verplicht') );
		}

		if ( ! $email ) {
			self::ctsignup_errors()->add('empty_email', __('Email is verplicht') );
		}

		if ( ! $password || ! $password2 ) {
			self::ctsignup_errors()->add('empty_password', __('Wachtwoord is verplicht') );
		}

		if ( strlen( $password ) < 5 ) {
			self::ctsignup_errors()->add('short_password', __('Wachtwoord moet minimaal 5 characters bevatten') );
		}

		if ( $password != $password2 ) {
			self::ctsignup_errors()->add('no_match_password', __('Wachtwoorden komen niet overeen') );
		}

		if ( ! empty( $tags ) ) {
			$tags = explode( ",", $tags );
		}

		if ( empty( self::ctsignup_errors()->get_error_messages() ) ) {
 			if ( CalculatieTool::api_external_signup( compact( 'first_name', 'last_name', 'phone', 'company', 'account', 'email', 'password', 'tags' ) ) ) {
				$mail_content  = "Nieuwe gebruiker via CTSignup\n\n";
				$mail_content .= "Gebruiker: " . $first_name . " " . $last_name . "\n";
				$mail_content .= "Email: " . $email . "\n";
				$mail_content .= "Bedrijf: " . $company . "\n";
				$mail_content .= "Telefoonnummer: " . $phone . "\n";

				if ( ! empty( $tags ) ) {
					$mail_content .= "Tags: " . $tags . "\n";
				}				

				$mail_content .= "\nCheers,\nWordPress";

				@wp_mail( get_bloginfo( 'admin_email' ), 'Nieuwe gebruiker via CTSignup', $mail_content );

				wp_redirect( $redirect ); exit;
			} else {
				CalculatieTool::log( 'User was not created' );
			}
		}
	}

	/**
	 * Handle the form POST request from the frontend. Perform
	 * basic validation to ease the service and gain faster feedback. Then
	 * send the form per mail.
	 */
	private static function process_mail_form() {
		$first_name    = sanitize_text_field( $_POST["ctsignup_mail_first"] );
		$last_name     = sanitize_text_field( $_POST["ctsignup_mail_last"] );
		$email         = sanitize_email( $_POST["ctsignup_mail_email"] );
		$phone         = sanitize_text_field( $_POST["ctsignup_mail_phone"] );
		$comment       = sanitize_text_field( $_POST["ctsignup_mail_comment"] );
		$redirect      = sanitize_text_field( $_POST["ctsignup_mail_form_redirect"] );
		$tags          = @sanitize_text_field( $_POST["ctsignup_mail_form_tags"] );

		if ( ! $first_name || ! $last_name ) {
			self::ctsignup_errors()->add('empty_names', __('Voor en achternaam zijn verplicht') );
		}

		if ( ! $email ) {
			self::ctsignup_errors()->add('empty_email', __('Email is verplicht') );
		}

		if ( ! $phone ) {
			self::ctsignup_errors()->add('empty_email', __('Telefoonnummer is verplicht') );
		}

		$mail_content  = "Aanvraag online demo\n\n";
		$mail_content .= "Gebruiker: " . $first_name . " " . $last_name . "\n";
		$mail_content .= "Email: " . $email . "\n";
		$mail_content .= "Telefoonnummer: " . $phone . "\n";

		if ( ! empty( $comment ) ) {
			$mail_content .= "Opmerking: " . $comment . "\n";
		}

		if ( ! empty( $tags ) ) {
			$mail_content .= "Tags: " . $tags . "\n";
		}

		$mail_content .= "\nCheers, WordPress";

		if ( empty( self::ctsignup_errors()->get_error_messages() ) ) {
			if ( wp_mail( get_bloginfo( 'admin_email' ), 'Aanvraag online demo', $mail_content ) ) {
				wp_redirect( $redirect ); exit;
			} else {
				CalculatieTool::log( 'Email was not send' );
			}
		}
	}

	/**
	 * Check with the service if the username exists
	 * and return the formatted message.
	 */
	private static function process_usercheck() {
		$name = sanitize_text_field( $_POST["ctsignup_signup_account"] );

		if ( ! $name ) {
			wp_send_json( array( 'valid' => false, 'message' => 'Gebruikersnaam mag alleen alfanumerieke en .- karakters bevatten' ) );
		}

		if ( ! preg_match( '/^[a-z0-9._-]+$/', $name ) ) {
			wp_send_json( array( 'valid' => false, 'message' => 'Gebruikersnaam mag alleen alfanumerieke en .- karakters bevatten' ) );
		}

		if ( self::api_external_username_check( compact( 'name' ) ) ) {
			wp_send_json( array( 'valid' => false, 'message' => 'Gebruikersnaam bestaat al' ) );
		} else {
			wp_send_json( array( 'valid' => true ) );
		}

		exit;
	}

	/**
	 * Verify that the connection, keys and response 
	 * are correct. Show the appropriate message on 
	 * return.
	 */
	private static function process_verify() {
		delete_transient( 'ctsignup_access_token' );

		if ( self::api_external_verification() ) {
			add_action( 'admin_notices',  array( 'CalculatieTool', 'ctsignup_admin_verify_ok') );
		} else {
			add_action( 'admin_notices',  array( 'CalculatieTool', 'ctsignup_admin_verify_error') );
		}
	}

	/**
	 * Send an testmail to test the wp_mail()
	 * system.
	 */
	private static function process_testmail() {
		$mail_content  = "CTSignup testmail\n\n";
		$mail_content .= "Dit is een testmail om te kijken of email vanuit\n";
		$mail_content .= "WordPress goed aankomt en juist is geformateerd\n";
		$mail_content .= "\nCheers, WordPress";

		if ( wp_mail( get_bloginfo( 'admin_email' ), 'CTSignup testmail', $mail_content ) ) {
			add_action( 'admin_notices',  array( 'CalculatieTool', 'ctsignup_admin_mail_ok') );
		} else {
			add_action( 'admin_notices',  array( 'CalculatieTool', 'ctsignup_admin_mail_error') );
		}
	}

	/**
	 * Catch the incomming request, and send it to the
	 * designated callback.
	 */
	public static function helper() {
		if ( isset( $_POST['ctsignup_signup_form_save'] ) ) {
			self::process_signup_form();
		}

		if ( isset( $_POST['ctsignup_mail_form_save'] ) ) {
			self::process_mail_form();
		}

		if ( isset( $_POST['ctsignup_signup_account'] ) && isset( $_GET['usercheck'] ) ) {
			self::process_usercheck();
		}

		if ( isset( $_GET['verify'] ) && is_admin() ) {
			self::process_verify();
		}

		if ( isset( $_GET['testmail'] ) && is_admin() ) {
			self::process_testmail();
		}
	}

	/**
	 * Return the access token if already present. If not
	 * request the token via the client settings. The token
	 * is stored in the application cache while it is valid.
	 *
	 * @return string Return the access token or false on failure.
	 */
	private static function get_access_token() {
		$access_token = get_transient( 'ctsignup_access_token' );

		if( false === $access_token ) {
			$body = array(
				'client_id' => self::get_client_id(),
				'client_secret' => self::get_client_secret(),
				'redirect_uri' => get_site_url(),
				'grant_type' => 'client_credentials',
			);

			$response = self::http_post( $body , self::get_token_url() );
			if ( ! $response ) {
				CalculatieTool::log( 'Service returned empty response' );
				
				return false;
			}

			if ( property_exists( $response, 'error' ) ) {
				CalculatieTool::log( compact( 'response' ) );

				return false;
			}

			$expiration = $response->expires_in;
			if ( $expiration > 150 ) {
				$expiration -= 100;
			}
			
			set_transient( 'ctsignup_access_token', $response->access_token, $expiration );

			$access_token = $response->access_token;
		}

		return $access_token;
	}

	/**
	 * Check if the username exists.
	 *
	 * @return bool True on existing username, false otherwise.
	 */
	private static function api_external_username_check( $data ) {
		$access_token = self::get_access_token();
		if ( ! $access_token ) {
			return false;
		}

		$response = self::http_post( $data, self::build_api_url( '/oauth2/rest/internal/usernamecheck' ), $access_token );
		if ( ! $response ) {
			CalculatieTool::log( 'Service returned empty response' );
			
			return false;
		}

		if ( property_exists( $response, 'error' ) ) {
			CalculatieTool::log( compact( 'response' ) );

			return false;
		}

		if ( 0 === $response->success ) {
			return false;
		}

		if ( 1 == $response->exist ) {
			return true;
		}

		return false;
	}

	/**
	 * Verify the connection, keys and service.
	 *
	 * @return bool True on success, false on failure.
	 */
	private static function api_external_verification() {
		$access_token = self::get_access_token();
		if ( ! $access_token ) {
			return false;
		}

		$response = self::http_get( self::build_api_url( '/oauth2/rest/internal/verify' ), $access_token );
		if ( ! $response ) {
			CalculatieTool::log( 'Service returned empty response' );
			
			return false;
		}

		if ( property_exists( $response, 'error' ) ) {
			CalculatieTool::log( compact( 'response' ) );

			return false;
		}

		if ( 0 === $response->success ) {
			return false;
		}

		return true;
	}

	/**
	 * Send the signup request.
	 *
	 * @param array $data User data.
	 * @return bool True on success, false on failure.
	 */
	private static function api_external_signup( $data ) {
		$access_token = self::get_access_token();
		if ( ! $access_token ) {
			return false;
		}

		$data['remote_addr'] = self::get_remote_addr();
		$data['http_referer'] = self::get_referer();

		$response = self::http_post( $data, self::build_api_url( '/oauth2/rest/internal/user_signup' ), $access_token );
		if ( ! $response ) {
			CalculatieTool::log( 'Service returned empty response' );
			
			return false;
		}

		if ( property_exists( $response, 'error' ) ) {
			CalculatieTool::log( compact( 'response' ) );

			return false;
		}

		if ( 0 === $response->success ) {
			foreach ( $response->errors as $error ) {
				self::ctsignup_errors()->add( 'backend_error', $error );
			}

			return false;
		}

		return true;
	}

	/**
	 * Log debugging info to the error log.
	 *
	 * Enabled when WP_DEBUG_LOG is enabled (and WP_DEBUG, since according to
	 * core, "WP_DEBUG_DISPLAY and WP_DEBUG_LOG perform no function unless
	 * WP_DEBUG is true), but can be disabled via the akismet_debug_log filter.
	 *
	 * @param mixed $message The data to log.
	 */
	public static function log( $message ) {
		error_log(  'CTSignup: an error occured: ' . print_r( compact( 'message' ), true ) );
	}

	/**
	 * Make a POST request to the service.
	 *
	 * @param array $request Data to send to the service.
	 * @param string $url The URL for the request.
	 * @param string $token Access token to identify the client.
	 * @return object Resulting object, empty on failure.
	 */
	private static function http_post( $request, $url, $token = null ) {
		$request_ua = sprintf( 'WordPress/%s | CTSignup/%s', $GLOBALS['wp_version'], constant( 'CTSINGUP_VERSION' ) );

		$http_args = array(
			'body' => $request,
			'headers' => array(
				'User-Agent' => $request_ua,
			),
			'timeout' => 15
		);

		if ( isset( $token ) ) {
			$http_args[ 'headers' ][ 'Authorization' ] = "Bearer " . $token;
		}

		$response = wp_remote_post( $url, $http_args );
		if ( is_wp_error( $response ) ) {
			do_action( 'http_request_failure', $response );

			CalculatieTool::log( compact( 'url', 'http_args', 'response' ) );

			return;
		}

		return json_decode(wp_remote_retrieve_body($response));
	}

	/**
	 * Make a GET request to the service.
	 *
	 * @param string $url The URL for the request.
	 * @param string $token Access token to identify the client.
	 * @return object Resulting object, empty on failure.
	 */
	private static function http_get( $url, $token = null ) {
		$request_ua = sprintf( 'WordPress/%s | CTSignup/%s', $GLOBALS['wp_version'], constant( 'CTSINGUP_VERSION' ) );

		$http_args = array(
			'headers' => array(
				'User-Agent' => $request_ua,
			),
			'timeout' => 15
		);

		if ( isset( $token ) ) {
			$http_args[ 'headers' ][ 'Authorization' ] = "Bearer " . $token;
		}

		$response = wp_remote_get( $url, $http_args );
		if ( is_wp_error( $response ) ) {
			do_action( 'http_request_failure', $response );

			CalculatieTool::log( compact( 'url', 'http_args', 'response' ) );

			return;
		}

		return json_decode(wp_remote_retrieve_body($response));
	}

}
