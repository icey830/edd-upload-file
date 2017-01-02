<?php
/**
 * Download actions
 *
 * @package     EDD\UploadFile\Download\Actions
 * @since       2.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Process a file download
 *
 * @since       2.0.0
 * @return      void
 */
function edd_upload_file_process_download() {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'edd_upload_file_download_nonce' ) ) {
		return;
	}

	if ( ! isset( $_GET['filename'] ) || ! isset( $_GET['filepath'] ) ) {
		return;
	}

	if ( ! function_exists( 'edd_get_file_ctype' ) ) {
		require_once EDD_PLUGIN_DIR . 'includes/process-download.php';
	}

	$upload_dir     = wp_upload_dir();
	$upload_dir     = $upload_dir['basedir'] . '/edd-upload-files/';
	$requested_file = $upload_dir . $_GET['filepath'];
	$file_extension = edd_get_file_extension( $requested_file );
	$ctype          = edd_get_file_ctype( $file_extension );
	$method         = edd_get_file_download_method();

	if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit(0);
	}
	if ( function_exists( 'get_magic_quotes_runtime' ) && get_magic_quotes_runtime() && version_compare( phpversion(), '5.4', '<' ) ) {
		set_magic_quotes_runtime(0);
	}

	@session_write_close();
	if ( function_exists( 'apache_setenv' ) ) {
		@apache_setenv('no-gzip', 1);
	}
	@ini_set( 'zlib.output_compression', 'Off' );

	nocache_headers();
	header("Robots: none");
	header("Content-Type: " . $ctype . "");
	header("Content-Description: File Transfer");
	header("Content-Disposition: attachment; filename=\"" . $_GET['filename'] . "\"");
	header("Content-Transfer-Encoding: binary");

	if ( 'x_sendfile' == $method && ( ! function_exists( 'apache_get_modules' ) || ! in_array( 'mod_xsendfile', apache_get_modules() ) ) ) {
		// If X-Sendfile is selected but is not supported, fallback to Direct
		$method = 'direct';
	}

	$file_details = parse_url( $requested_file );
	$schemes      = array( 'http', 'https' ); // Direct URL schemes

	if ( ( ! isset( $file_details['scheme'] ) || ! in_array( $file_details['scheme'], $schemes ) ) && isset( $file_details['path'] ) && file_exists( $requested_file ) ) {

		/**
		 * Download method is seto to Redirect in settings but an absolute path was provided
		 * We need to switch to a direct download in order for the file to download properly
		 */
		$method = 'direct';

	}

	switch ( $method ) :

		case 'redirect' :

			// Redirect straight to the file
			edd_deliver_download( $requested_file, true );
			break;

		case 'direct' :
		default:

			$direct    = false;
			$file_path = $requested_file;

			if ( ( ! isset( $file_details['scheme'] ) || ! in_array( $file_details['scheme'], $schemes ) ) && isset( $file_details['path'] ) && file_exists( $requested_file ) ) {

				/** This is an absolute path */
				$direct    = true;
				$file_path = $requested_file;

			} elseif ( defined( 'UPLOADS' ) && strpos( $requested_file, UPLOADS ) !== false ) {

				/**
				 * This is a local file given by URL so we need to figure out the path
				 * UPLOADS is always relative to ABSPATH
				 * site_url() is the URL to where WordPress is installed
				 */
				$file_path  = str_replace( site_url(), '', $requested_file );
				$file_path  = realpath( ABSPATH . $file_path );
				$direct     = true;

			} elseif ( strpos( $requested_file, content_url() ) !== false ) {

				/** This is a local file given by URL so we need to figure out the path */
				$file_path  = str_replace( content_url(), WP_CONTENT_DIR, $requested_file );
				$file_path  = realpath( $file_path );
				$direct     = true;

			} elseif ( strpos( $requested_file, set_url_scheme( content_url(), 'https' ) ) !== false ) {

				/** This is a local file given by an HTTPS URL so we need to figure out the path */
				$file_path  = str_replace( set_url_scheme( content_url(), 'https' ), WP_CONTENT_DIR, $requested_file );
				$file_path  = realpath( $file_path );
				$direct     = true;

			}

			// Set the file size header
			header( "Content-Length: " . @filesize( $file_path ) );

			// Now deliver the file based on the kind of software the server is running / has enabled
			if ( stristr( getenv( 'SERVER_SOFTWARE' ), 'lighttpd' ) ) {

				header( "X-LIGHTTPD-send-file: $file_path" );

			} elseif ( $direct && ( stristr( getenv( 'SERVER_SOFTWARE' ), 'nginx' ) || stristr( getenv( 'SERVER_SOFTWARE' ), 'cherokee' ) ) ) {

				// We need a path relative to the domain
				$file_path = str_ireplace( realpath( $_SERVER['DOCUMENT_ROOT'] ), '', $file_path );
				header( "X-Accel-Redirect: /$file_path" );

			}

			if ( $direct ) {

				edd_deliver_download( $file_path );

			} else {

				// The file supplied does not have a discoverable absolute path
				edd_deliver_download( $requested_file, true );

			}

			break;

	endswitch;

	edd_die();
}
add_action( 'edd_upload_file_download', 'edd_upload_file_process_download' );
