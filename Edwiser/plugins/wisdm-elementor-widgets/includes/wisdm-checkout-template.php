<?php

/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
$checkout_page = 1;

if(!function_exists('wdm_get_social_login_links')){
	function wdm_get_social_login_links($provider='google'){
		$icon = '';
		if($provider=='google'){
			$icon = get_stylesheet_directory_uri().'/assets/images/google-icon.svg';
		}else if($provider=='facebook'){
			$icon = get_stylesheet_directory_uri().'/assets/images/facebook-icon.svg';
		}
		
		$link = '<a href="'.site_url('wp-login.php').'?loginSocial='.$provider.'" testshvsh="' . site_url('wp-login.php') . '" rel="nofollow" aria-label="Login with '.ucfirst($provider).'" data-redirect="current" data-plugin="nsl" data-action="connect" data-provider="'.$provider.'" data-popupwidth="475" data-popupheight="175"><div class="nsl-button nsl-button-default nsl-button-'.$provider.'" data-skin="dark"><div class="nsl-button-svg-container"><img src="'.$icon.'" alt="'.ucfirst($provider).'"/></div><div class="nsl-button-label-container">Login with '.ucfirst($provider).'</div></div></a>';
		unset($icon);
		return $link;
	}
}

$lost_password_slug = get_option('tml_lostpassword_slug');
$lost_password_url  = home_url($lost_password_slug);
?>
<div class="tml tml-login" id="theme-my-login1">
	<?php
		$the_action_url = 'login';
		$username_or_email_label = 'E-mail';
		$login_label = 'Log In';
		if($checkout_page){
			$username_or_email_label = 'Email ID';
			$the_action_url = '';
			$login_label = 'Login';
		}

		$redirect_to = '';
		if( !empty($_GET['redirect_to']) ){
			$redirect_to = $_GET['redirect_to'];
		}else{
			$current_page = get_field('wisdm_minimal_elementor_checkout_page','option');
			$redirect_to = $current_page;
		}
	?>
	<div class="nsl-container nsl-container-block" data-align="left">
		<div class="nsl-container-buttons">
			<?php
			echo wdm_get_social_login_links('google');
			echo wdm_get_social_login_links('facebook');
			?>
		</div>
	</div>

	<p class="login-or-para"><span>OR</span></p>
	<!-- <form name="loginform" id="loginform<?php //$template->the_instance(); ?>" action="<?php //echo get_field('wisdm_minimal_elementor_checkout_page','option'); ?>" method="post"> -->
	<p class="tml-user-login-wrap">
		<?php $options = get_option( 'tml_login_type' ); ?>
		<label class="edd-label" for="user_login1"><?php
			if ( 'username' == $options['login_type'] ) {
				_e( 'Username', 'theme-my-login' );
			} elseif ( 'email' == $options['login_type'] ) {
				_e( 'E-mail', 'theme-my-login' );
			} else {
				_e( $username_or_email_label, 'theme-my-login' );
			}
			?>
		</label>
		<input type="text" name="log" id="user_login1" class="input" value="" size="20" />
	</p>

	<p class="tml-user-pass-wrap">
		<label class="edd-label" for="user_pass1"><?php _e( 'Password', 'theme-my-login' ); ?></label>
		<input type="password" name="pwd" id="user_pass1" class="input" value="" size="20" autocomplete="off" />
	</p>
	<ul class="tml-action-links">
		<li><a href="<?php echo esc_url($lost_password_url); ?>" rel="nofollow"></a></li>
	</ul>
	<div class="tml-rememberme-submit-wrap">
		<p class="tml-rememberme-wrap">
			<label class="checkbox-container" for="rememberme1">
				<?php esc_attr_e( 'Remember Me', 'theme-my-login' ); ?>
				<input name="rememberme" type="checkbox" id="rememberme1" value="forever" />
				<span class="checkmark"></span>
			</label>
		</p>

		<p class="tml-submit-wrap">
			<input type="submit" name="wp-submit" id="wp-submit1" value="<?php esc_attr_e( $login_label, 'theme-my-login' ); ?>" />
			<input type="hidden" name="redirect_to" value="<?php echo $redirect_to ?>" />
			<input type="hidden" name="instance" value="1" />
			<input type="hidden" name="action" value="login" />
			<?php wp_nonce_field( 'ajax-login-nonce', 'wdm-security' ); ?>
		</p>
	</div>
	<!-- </form> -->
</div>
