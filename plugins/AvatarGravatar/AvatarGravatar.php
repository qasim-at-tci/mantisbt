<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Mantis Avatar Plugin Base Class
 * Implements basic avatar handling functionality within MantisBT
 * @copyright Copyright 2015  Damien Regad - dregad@mantisbt.org
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage classes
 */
class AvatarGravatarPlugin extends AvatarPlugin {

	/**
	 * Gravatar URLs
	 */
	const GRAVATAR_URL        = 'http://www.gravatar.com/';
	const GRAVATAR_URL_SECURE = 'https://secure.gravatar.com/';

	/**
	 * Default Gravatar image types
	 */
	const GRAVATAR_DEFAULT_MYSTERYMAN = 'mm';
	const GRAVATAR_DEFAULT_IDENTICON  = 'identicon';
	const GRAVATAR_DEFAULT_MONSTERID  = 'monsterid';
	const GRAVATAR_DEFAULT_WAVATAR    = 'wavatar';
	const GRAVATAR_DEFAULT_RETRO      = 'retro';
	const GRAVATAR_DEFAULT_BLANK      = 'blank';

	const GRAVATAR_RATING_G = 'g';

	function register() {
		$this->name = plugin_lang_get( 'title' );
		$this->description = plugin_lang_get( 'description' );
//		$this->page = 'config';

		$this->version = '0.1';
		$this->requires = array(
			'MantisCore' => '1.3.0',
		);

		$this->author = 'MantisBT Team';
		$this->contact = 'mantisbt-dev@lists.sourceforge.net';
		$this->url = 'http://www.mantisbt.org';
	}

	/**
	 * Retrieves the URL to the user's avatar
	 */
	function get_url( $p_event, $p_user_id, $p_size = 80 ) {
		$t_default_avatar = config_get( 'show_avatar' );

		# Default avatar is either one of Gravatar's options, or
		# an URL to a default avatar image
		if( OFF === $t_default_avatar ) {
			# Avatars are not used
			return array();
		}
		# Default avatar for legacy configuration
		if( ON === $t_default_avatar ) {
			$t_default_avatar = self::GRAVATAR_DEFAULT_IDENTICON;
		}
		$t_default_avatar = urlencode( $t_default_avatar );

		$t_email_hash = md5( strtolower( trim( user_get_email( $p_user_id ) ) ) );

		# Build Gravatar URL
		if( http_is_protocol_https() ) {
			$t_avatar_url = self::GRAVATAR_URL_SECURE;
		} else {
			$t_avatar_url = self::GRAVATAR_URL;
		}

		$t_avatar_url = $t_avatar_url . 'avatar/'
			. $t_email_hash . '?'
			. http_build_query( array(
				'd' => $t_default_avatar,
				'r' => self::GRAVATAR_RATING_G,
				's' => $p_size
			  ));

		return array( $t_avatar_url, $p_size, $p_size );
	}

	/**
	 * Prints avatar image for the given user ID
	 */
	function display( $p_event, $p_user_id, $p_size = 80 ) {
		if(    OFF == config_get( 'show_avatar' )
			|| !user_exists( $p_user_id )
		) {
			return;
		}

		if( access_has_project_level( config_get( 'show_avatar_threshold' ), null, $p_user_id ) ) {
			$t_avatar = $this->get_url( $p_event, $p_user_id, $p_size );
			if( !empty( $t_avatar ) ) {
				printf(
					'<a rel="nofollow" href="%s"><img class="avatar" src="%s" alt="User avatar" width="%s" height="%s" /></a>',
					'http://site.gravatar.com',
					htmlspecialchars( $t_avatar[0] ), # Avatar URL
					$t_avatar[1], # width
					$t_avatar[2]  # height
				);
			}
		}
	}
}
