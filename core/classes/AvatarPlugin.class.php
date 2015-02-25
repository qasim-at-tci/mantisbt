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
abstract class AvatarPlugin extends MantisPlugin {

	/**
	 * Event hook declaration.
	 * @return array
	 */
	function hooks() {
		return array(
			'EVENT_GET_AVATAR'     => 'get_url',
			'EVENT_DISPLAY_AVATAR' => 'display',
		);
	}

	/**
	 * Retrieves the URL to the user's avatar
	 * @param integer $p_user_id A valid user identifier
	 * @param integer $p_size    Number of pixels for the image
	 * @return array (URL, width, height) or empty array if given user has no avatar
	 */
	abstract function get_url( $p_event, $p_user_id, $p_size = 80 );

	/**
	 * Prints avatar image for the given user ID
	 *
	 * @param integer $p_user_id A user identifier.
	 * @param integer $p_size    Image pixel size.
	 * @return void
	 */
	abstract function display( $p_event, $p_user_id, $p_size = 80 );

}
