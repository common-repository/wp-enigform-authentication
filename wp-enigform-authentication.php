<?php
/*
Plugin Name: Enigform Authentication
Version: 1.2.1
Plugin URI: http://wiki.buanzo.org/index.php?n=Main.Wp-enigform-authentication
Description: Authenticate users using Enigform Session Management. Based off HTTP Authentication plugin.
Author: Arturo Buanzo Busleiman
Author URI: http://www.buanzo.com.ar/pro/eng.html
*/

if (! class_exists('EnigformAuthenticationPlugin')) {
	class EnigformAuthenticationPlugin {
		function EnigformAuthenticationPlugin() {
			if (isset($_GET['activate']) and $_GET['activate'] == 'true') {
				add_action('init', array(&$this, 'initialize_options'));
			}
			add_action('admin_menu', array(&$this, 'add_options_page'));
			add_action('wp_authenticate', array(&$this, 'authenticate'), 10, 2);
			add_filter('check_password', array(&$this, 'skip_password_check'), 10, 4);
			add_action('wp_logout', array(&$this, 'logout'));
			add_action('lost_password', array(&$this, 'disable_function'));
			add_action('retrieve_password', array(&$this, 'disable_function'));
			add_action('password_reset', array(&$this, 'disable_function'));
			add_action('check_passwords', array(&$this, 'generate_password'), 10, 3);
			add_filter('show_password_fields', array(&$this, 'disable_password_fields'));
		}


		/*************************************************************
		 * Plugin hooks
		 *************************************************************/

		/*
		 * Add options for this plugin to the database.
		 */
		function initialize_options() {
			if (current_user_can('manage_options')) {
				add_option('enigform_authentication_logout_uri', get_option('home'), 'The URI to which the user is redirected when she chooses "Logout".');
				add_option('enigform_authentication_auto_create_user', false, 'Should a new user be created automatically if not already in the WordPress database?');
				add_option('enigform_authentication_administrator_keyid','','Long OpenPGP KeyID of Administrator user');
/* Should use X-Auth-OpenPGP-Email header
				add_option('enigform_authentication_auto_create_email_domain', '', 'The domain to use for the email address of an automatically created user.');
*/
			}
		}

		/*
		 * Add an options pane for this plugin.
		 */
		function add_options_page() {
			if (function_exists('add_options_page')) {
				add_options_page('Enigform Authentication', 'Enigform Authentication', 9, __FILE__, array(&$this, '_display_options_page'));
			}
		}

		/*
		 * If the X-OpenPGP-Session-Status header is set and equals 'Valid', then use it as the username.
		 * This assumes that you have externally authenticated the user.
		 */
		function authenticate($username, $password) {
			if ($_SERVER['HTTP_X_OPENPGP_SESSION_STATUS']!='Valid') {
				die('No HTTP_X_OPENPGP_SESSION_STATUS = Valid header found in $_SERVER.');
			}

			// Fake WordPress into authenticating by overriding the credentials
			
			// Check if session belongs to administrator keyid
			$admin_keyid = get_option('enigform_authentication_administrator_keyid');
			if ($admin_keyid <> '' && $_SERVER['HTTP_X_AUTH_OPENPGP_KEYID'] == $admin_keyid)
				$username = 'admin'; // TODO: need a better way to detect first admin user
			else
				$username = $_SERVER['HTTP_X_AUTH_OPENPGP_KEYID']; // If we'd used Email, comment, etc this'd be fakeable. we dont want that.
			$password = $this->_get_password();

			// Create new users automatically, if configured
			$user = get_userdatabylogin($username);
			if (! $user or $user->user_login != $username) {
				if ((bool) get_option('enigform_authentication_auto_create_user')) {
					$this->_create_user($username);
				}
				else {
					// Bail out to avoid showing the login form
					die("User $username does not exist in the WordPress database");
				}
			}
		}

		/*
		 * Skip the password check, since we've externally authenticated.
		 */
		function skip_password_check($check, $password, $hash, $user_id) {
			return true;
		}

		/*
		 * Logout the user by redirecting them to the logout URI.
		 */
		function logout() {
			header('Location: ' . get_option('enigform_authentication_logout_uri'));
			exit();
		}

		/*
		 * Generate a password for the user. This plugin does not
		 * require the user to enter this value, but we want to set it
		 * to something nonobvious.
		 */
		function generate_password($username, $password1, $password2) {
			$password1 = $password2 = $this->_get_password();
		}

		/*
		 * Used to disable certain display elements, e.g. password
		 * fields on profile screen.
		 */
		function disable_password_fields($show_password_fields) {
			return false;
		}

		/*
		 * Used to disable certain login functions, e.g. retrieving a
		 * user's password.
		 */
		function disable_function() {
			die('Disabled');
		}


		/*************************************************************
		 * Functions
		 *************************************************************/

		/*
		 * Generate a random password.
		 */
		function _get_password($length = 10) {
			return substr(md5(uniqid(microtime())), 0, $length);
		}

		/*
		 * Create a new WordPress account for the specified username.
		 */
		function _create_user($username) {
			$password = $this->_get_password();
			$email = $_SERVER['HTTP_X_AUTH_OPENPGP_EMAIL'];

			require_once(WPINC . DIRECTORY_SEPARATOR . 'registration.php');
			wp_create_user($username, $password, $email);
		}

		/*
		 * Display the options for this plugin.
		 */
		function _display_options_page() {
			$logout_uri = get_option('enigform_authentication_logout_uri');
			$auto_create_user = (bool) get_option('enigform_authentication_auto_create_user');
			$auto_create_email_domain = get_option('enigform_authentication_auto_create_email_domain');
			$administrator_keyid = get_option('enigform_authentication_administrator_keyid');
?>
<div class="wrap">
  <h2>Enigform Authentication Options</h2>
  <form action="options.php" method="post">
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="page_options" value="enigform_authentication_logout_uri,enigform_authentication_auto_create_user,enigform_authentication_administrator_keyid" />
    <?php if (function_exists('wp_nonce_field')): wp_nonce_field('update-options'); endif; ?>

    <table class="form-table">
      <tr valign="top">
        <th scope="row"><label for="enigform_authentication_logout_uri">Logout URI</label></th>
        <td>
          <input type="text" name="enigform_authentication_logout_uri" id="enigform_authentication_logout_uri" value="<?php echo htmlspecialchars($logout_uri) ?>" size="50" /><br />
          Default is <code><?php echo htmlspecialchars(get_settings('home')); ?></code>; override to e.g. remove a cookie.
        </td>
      </tr>
      <tr valign="top">
        <th scope="row"><label for="enigform_authentication_auto_create_user">Automatically create accounts?</label></th>
        <td>
          <input type="checkbox" name="enigform_authentication_auto_create_user" id="enigform_authentication_auto_create_user"<?php if ($auto_create_user) echo ' checked="checked"' ?> value="1" /><br />
          Should a new user be created automatically if not already in the WordPress database?<br />
          Created users will obtain the role defined under &quot;New User Default Role&quot; on the <a href="options-general.php">General Options</a> page.
        </td>
      </tr>
      <tr valign="top">
        <th scope="row"><label for="enigform_authentication_administrator_keyid">Administrator Long KeyID (gpg -K --keyid-format long, 16 chars))</label></th>
        <td>
          <input type="text" name="enigform_authentication_administrator_keyid" id="enigform_authentication_administrator_keyid" value="<?php echo htmlspecialchars($administrator_keyid) ?>" size="50" /><br />
          If the Enigform Session is signed by this locally known GnuPG key, then login the user as administrator (quick hack for first version of this plugin).
        </td>
      </tr>
    </table>
    <p class="submit">
      <input type="submit" name="Submit" value="Save Changes" />
    </p>
  </form>
</div>
<?php
		}
	}
}

// Load the plugin hooks, etc.
$enigform_authentication_plugin = new EnigformAuthenticationPlugin();
?>
