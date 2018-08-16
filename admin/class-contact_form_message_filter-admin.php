<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       www.kofimokome.ml
 * @since      1.0.0
 *
 * @package    Contact_form_message_filter
 * @subpackage Contact_form_message_filter/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Contact_form_message_filter
 * @subpackage Contact_form_message_filter/admin
 * @author     Kofi Mokome <kofimokome10@gmail.com>
 */
class Contact_form_message_filter_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/*todo: add description*/
	private $temp_email;
	private $temp_message;
	private $count_updated = false;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Contact_form_message_filter_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Contact_form_message_filter_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/contact_form_message_filter-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Contact_form_message_filter_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Contact_form_message_filter_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/contact_form_message_filter-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function kmcfmf_add_main_menu() {
		add_menu_page(
			'Contact Form Message Filter',
			'Contact Form Filter',
			'manage_options',
			'contact_form_message_filter',
			array( $this, 'kmcfmf_menu_view' ),
			'dashicons-filter'
		);
	}

	public function kmcfmf_add_options_submenu() {
		add_submenu_page(
			'contact_form_message_filter',
			'Blocked Messages',
			'Blocked Messages',
			'manage_options',
			'contact_form_message_filter_messages',
			array( $this, 'kmcfmf_messages_view' )
		);

		add_submenu_page(
			'contact_form_message_filter',
			'Options',
			'Options',
			'manage_options',
			'contact_form_message_filter_option',
			array( $this, 'kmcfmf_options_view' )
		);
	}

	public function kmcfmf_menu_view() {
		?>
        <h1> Welcome To Your Dashboard </h1>
		<?php if ( ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ): ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e( 'Please Install / Enable Contact Form 7 Plugin First!', 'sample-text-domain' ); ?></p>
            </div>
		<?php endif; ?>
        <hr>
        Contact Form Message Filter filters messages submitted from contact form 7. You can choose to filter emails or messages or both.

        <h2>General Statistics For <?php echo Date( "l, dS F Y" ); ?></h2>
        <hr>
        <h3>Messages Blocked Today : <?php echo get_option( 'kmcfmf_messages_blocked_today' ); ?></h3>
        <hr>
        <h3>Total Messages Blocked: <?php echo get_option( 'kmcfmf_messages_blocked' ); ?></h3>
        <hr>
        <h3>Last Message Blocked: </h3><?php echo get_option( 'kmcfmf_last_message_blocked' ); ?>
        <hr>
		<?php
	}

	public function kmcfmf_options_view() {
		?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"></div>
            <h1>Message Filter Options</h1>
            <strong>Please enter each word separated by a space in the boxes below</strong>
			<?php settings_errors(); ?>
            <form method="post" action="options.php">
				<?php
				settings_fields( 'kmcfmf_message_filter_option' );
				do_settings_sections( 'contact_form_message_filter_option' );
				submit_button();
				?>
            </form>

        </div>
		<?php
	}

	public function kmcfmf_messages_view() {
		$pagination = (int) $_GET['pagination'];

		if ( $pagination <= 0 ) {
			$pagination = 1;
		}

		$start           = 0;
		$end             = - 1;
		$number_per_page = 5;

		$messages = explode( "]kmcfmf_message[", get_option( 'kmcfmf_messages' ) );
		$messages = array_reverse( $messages, false );
		$size     = ( sizeof( $messages ) - 1 );
		if ( ( $pagination * $number_per_page ) > $size && ( ( $pagination * $number_per_page ) - $number_per_page ) < $size ) {
			$start = ( ( $pagination * $number_per_page ) - ( $number_per_page ) );
			$end   = ( $size );

		} elseif ( ( $pagination * $number_per_page ) <= $size ) {
			$start = ( ( $pagination * $number_per_page ) - ( $number_per_page ) );
			$end   = ( $pagination * $number_per_page );
		}
		// echo "<br>we will search from " . $start . " to " . ( $end - 1 ) . "<br>";
		?>
        <h3><?php echo get_option( 'kmcfmf_messages_blocked' ); ?> messages have been blocked</h3>
        <table class="kmcfmf_table">
            <tr>
                <td><b>S/N</b></td>
                <td>
                    <center><b>Time</b></center>
                </td>
                <td>
                    <center><b>Email</b></center>
                </td>
                <td>
                    <center><b>Message</b></center>
                </td>
            </tr>
			<?php
			for ( $i = $start; $i < $end; $i ++ ) {
				$data = explode( "kmcfmf_data=", $messages[ $i ] );
				if ( $data[1] != '' && $data[2] != '' && $data[3] != '' ) {
					echo "<tr>";
					echo "<td>" . ( $i + 1 ) . "</td>";
					echo "<td>" . $data[3] . "</td>";
					echo "<td>" . $data[2] . "</td>";
					echo "<td>" . $data[1] . "</td>";
					//echo $i . " message: " . $data[1] . " email: " . $data[2] . " time: " . $data[3] . "<br>";
					echo "</tr>";
				}
			}
			?>
        </table>
        <br>
		<?php
		if ( $pagination > 1 ) {
			echo "<a href='?page=contact_form_message_filter_messages&pagination=" . ( $pagination - 1 ) . "' class='button button-primary'> < Prev page</a>";
		}
		if ( ( ( ( $pagination + 1 ) * $number_per_page ) - $number_per_page ) < $size ) {
			echo " <a href='?page=contact_form_message_filter_messages&pagination=" . ( $pagination + 1 ) . "' class='button button-primary'> Next page > </a>";
		}


	}

	function kmcfmf_register_settings_init() {
		// Add the section for user to enter restricted words
		add_settings_section(
			'kmcfmf_message_filter_option',
			'',
			array( $this, 'kmcfmf_restricted_callback' ),
			'contact_form_message_filter_option' );

		add_settings_field(
			'kmcfmf_restricted_words',
			'Restricted Words',
			array( $this, 'kmcfmf_restricted_words_callback' ),
			'contact_form_message_filter_option',
			'kmcfmf_message_filter_option'
		);

		add_settings_field(
			'kmcfmf_restricted_emails',
			'Restricted Emails',
			array( $this, 'kmcfmf_restricted_emails_callback' ),
			'contact_form_message_filter_option',
			'kmcfmf_message_filter_option'
		);

		register_setting( 'kmcfmf_message_filter_option', 'kmcfmf_restricted_words' );
		register_setting( 'kmcfmf_message_filter_option', 'kmcfmf_restricted_emails' );

		// Section to enable and disable functionalities

		add_settings_field(
			'kmcfmf_message_filter_toggle',
			'Enable Message Filter?: ',
			array( $this, 'kmcfmf_message_filter_toggle_callback' ),
			'contact_form_message_filter_option',
			'kmcfmf_message_filter_option'
		);

		add_settings_field(
			'kmcfmf_email_filter_toggle',
			'Enable Email Filter?: ',
			array( $this, 'kmcfmf_email_filter_toggle_callback' ),
			'contact_form_message_filter_option',
			'kmcfmf_message_filter_option'
		);
		register_setting( 'kmcfmf_message_filter_option', 'kmcfmf_email_filter_toggle' );
		register_setting( 'kmcfmf_message_filter_option', 'kmcfmf_message_filter_toggle' );

		add_settings_field(
			'kmcfmf_message_filter_reset',
			'Reset Filter Count?: ',
			array( $this, 'kmcfmf_message_filter_reset_callblack' ),
			'contact_form_message_filter_option',
			'kmcfmf_message_filter_option'
		);
		register_setting( 'kmcfmf_message_filter_option', 'kmcfmf_message_filter_reset' );
	}

	function kmcfmf_message_filter_reset_callblack() {
		?>
        <input type="checkbox" name="kmcfmf_message_filter_reset" id="kmcfmf_message_filter_reset">
		<?php
	}

	function kmcfmf_email_filter_toggle_callback() {
		?>
        <input type="checkbox" name="kmcfmf_email_filter_toggle"
               id="kmcfmf_email_filter_toggle" <?php echo get_option( 'kmcfmf_email_filter_toggle' ) == 'on' ? 'checked' : '' ?>>
		<?php
	}

	function kmcfmf_message_filter_toggle_callback() {
		?>
        <input type="checkbox" name="kmcfmf_message_filter_toggle"
               id="kmcfmf_message_filter_toggle" <?php echo get_option( 'kmcfmf_message_filter_toggle' ) == 'on' ? 'checked' : '' ?>>
		<?php
	}

	function kmcfmf_restricted_callback() {

	}

	function kmcfmf_restricted_words_callback() {
		?>
        <textarea name="kmcfmf_restricted_words" id="kmcfmf_restricted_words" cols="80"
                  rows="8"
                  placeholder="eg john doe baby man earth"><?php echo get_option( 'kmcfmf_restricted_words' ); ?></textarea>
		<?php
	}

	function kmcfmf_restricted_emails_callback() {
		?>
        <textarea name="kmcfmf_restricted_emails" id="kmcfmf_restricted_emails" cols="80"
                  rows="8"
                  placeholder="eg john@localhost.com john "><?php echo get_option( 'kmcfmf_restricted_emails' ); ?></textarea>
        <br/>
        <strong>Note: If you write john, we will check for ( john@gmail.com, john@yahoo.com, john@hotmail.com
            etc... ) </strong>
		<?php
	}

	function kmcfmf_textarea_validation_filter( $result, $tag ) {
		$type = $tag->type;
		$name = $tag->name;

		$found = false;

		$check_words = explode( " ", get_option( 'kmcfmf_restricted_words' ) );

		$value = isset( $_POST[ $name ] ) ? (string) $_POST[ $name ] : '';
		//$value = '';

		foreach ( $check_words as $check_word ) {
			if ( preg_match( "/\b" . $check_word . "/mi", $value ) > 0 ) {
				$found = true;
			}

			/*if ( strpos( $value, $check_word ) !== false ) {
				$found = true;
			}*/
		}

		if ( $tag->is_required() && '' == $value ) {
			$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
		}

		if ( $found == true ) {
			$result->invalidate( $tag, wpcf7_get_message( 'validation_error' ) );

			$this->temp_email = $_POST['your-email'];

			if ( ! $this->count_updated && $this->temp_email != '' ) {
				update_option( 'kmcfmf_last_message_blocked', '<b>Time: </b>' . Date( 'd-m-y h:ia' ) . ' <br/><b>Message: </b> ' . $value . ' <br/><b>Email: </b> ' . $this->temp_email );
				update_option( "kmcfmf_messages", get_option( "kmcfmf_messages" ) . "]kmcfmf_message[ kmcfmf_data=" . $value . " kmcfmf_data=" . $this->temp_email . " kmcfmf_data=" . Date( 'd-m-y  h:ia' ) );

				update_option( 'kmcfmf_messages_blocked', get_option( 'kmcfmf_messages_blocked' ) + 1 );
				update_option( "kmcfmf_messages_blocked_today", get_option( "kmcfmf_messages_blocked_today" ) + 1 );
				$this->count_updated = true;
			}
		}

		if ( '' !== $value ) {
			$maxlength = $tag->get_maxlength_option();
			$minlength = $tag->get_minlength_option();

			if ( $maxlength && $minlength && $maxlength < $minlength ) {
				$maxlength = $minlength = null;
			}

			$code_units = wpcf7_count_code_units( stripslashes( $value ) );

			if ( false !== $code_units ) {
				if ( $maxlength && $maxlength < $code_units ) {
					$result->invalidate( $tag, wpcf7_get_message( 'invalid_too_long' ) );
				} elseif ( $minlength && $code_units < $minlength ) {
					$result->invalidate( $tag, wpcf7_get_message( 'invalid_too_short' ) );
				}
			}
		}

		return $result;
	}

	function kmcfmf_text_validation_filter( $result, $tag ) {
		$name        = $tag->name;
		$check_words = explode( " ", get_option( 'kmcfmf_restricted_emails' ) );

		$value = isset( $_POST[ $name ] )
			? trim( wp_unslash( strtr( (string) $_POST[ $name ], "\n", " " ) ) )
			: '';

		if ( 'text' == $tag->basetype ) {
			if ( $tag->is_required() && '' == $value ) {
				$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
			}
		}

		if ( 'email' == $tag->basetype ) {
			if ( $tag->is_required() && '' == $value ) {
				$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
			} elseif ( '' != $value && ! wpcf7_is_email( $value ) ) {
				$result->invalidate( $tag, wpcf7_get_message( 'invalid_email' ) );
			} else {
				foreach ( $check_words as $check_word ) {
					if ( strpos( $value, $check_word ) !== false ) {
						$this->temp_message = $_POST['your-message'];
						$result->invalidate( $tag, wpcf7_get_message( 'invalid_email' ) );

						if ( ! $this->count_updated && $this->temp_message != '' ) {
							update_option( 'kmcfmf_last_message_blocked', '<b>Time: </b>' . Date( 'd-m-y h:ia' ) . ' <br/><b>Message: </b> ' . $this->temp_message . ' <br/><b>Email: </b> ' . $value );
							update_option( "kmcfmf_messages", get_option( "kmcfmf_messages" ) . "]kmcfmf_message[ kmcfmf_data=" . $this->temp_message . " kmcfmf_data=" . $value . " kmcfmf_data=" . Date( 'd-m-y  h:ia' ) );
							update_option( 'kmcfmf_messages_blocked', get_option( 'kmcfmf_messages_blocked' ) + 1 );
							update_option( "kmcfmf_messages_blocked_today", get_option( "kmcfmf_messages_blocked_today" ) + 1 );
							$this->count_updated = true;
						}
					}
				}
			}
		}

		if ( '' !== $value ) {
			$maxlength = $tag->get_maxlength_option();
			$minlength = $tag->get_minlength_option();

			if ( $maxlength && $minlength && $maxlength < $minlength ) {
				$maxlength = $minlength = null;
			}

			$code_units = wpcf7_count_code_units( stripslashes( $value ) );

			if ( false !== $code_units ) {
				if ( $maxlength && $maxlength < $code_units ) {
					$result->invalidate( $tag, wpcf7_get_message( 'invalid_too_long' ) );
				} elseif ( $minlength && $code_units < $minlength ) {
					$result->invalidate( $tag, wpcf7_get_message( 'invalid_too_short' ) );
				}
			}
		}

		return $result;
	}
}
