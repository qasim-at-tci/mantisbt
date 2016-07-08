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
 * Bug action group attach tags include file
 *
 * @package MantisBT
 * @copyright Copyright 2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @author Philipp Ramsenthaler
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses tag_api.php
 */

if( !defined( 'BUG_ACTIONGROUP_INC_ALLOW' ) ) {
	return;
}

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'tag_api.php' );

/**
 * Prints the title for the custom action page.
 * @return void
 */
function action_detach_tags_print_title() {
    echo '<tr class="form-title">';
    echo '<td colspan="2">';
    echo lang_get( 'tag_detach_long' );
    echo '</td></tr>';
}

/**
 * Prints the table and form for the Detach Tags group action page.
 */
function action_detach_tags_print_fields() {
	echo '<tr ',helper_alternate_class(),'><td class="category">',lang_get('tag_detach_long'),'</td><td>';
	//Possible TODO Show only tags which are included in given issues
	print_tag_input();
	echo '<input type="submit" class="button" value="' . lang_get( 'tag_detach' ) . ' " /></td></tr>';
}

/**
 * Validates the Detach Tags group action.
 * Gets called for every bug, but performs the real tag validation only
 * the first time.  Any invalid tags will be skipped, as there is no simple
 * or clean method of presenting these errors to the user.
 * @param integer Bug ID
 * @return boolean True
 */
function action_detach_tags_validate( $p_bug_id ) {
	global $g_action_detach_tags_valid;
	if ( !isset( $g_action_detach_tags_valid ) ) {
		$f_tag_string = gpc_get_string( 'tag_string' );
		$f_tag_select = gpc_get_string( 'tag_select' );

		global $g_action_detach_tags_detach, $g_action_detach_tags_failed;
		$g_action_detach_tags_detach = array();
		$g_action_detach_tags_failed = array();

		$t_tags = tag_parse_string( $f_tag_string );
		$t_can_detach = access_has_bug_level( config_get( 'tag_detach_threshold' ), $p_bug_id );

		foreach ( $t_tags as $t_tag_row ) {
			if ( -2 == $t_tag_row['id'] ) {
				$g_action_detach_tags_failed[] = $t_tag_row;
			} else if ( $t_can_detach ) {
				$g_action_detach_tags_detach[] = $t_tag_row;
			} else {
				$g_action_detach_tags_failed[] = $t_tag_row;
			}
		}

		if ( 0 < $f_tag_select && tag_exists( $f_tag_select ) ) {
			if ( $t_can_detach ) {
				$g_action_detach_tags_detach[] = tag_get( $f_tag_select );
			} else {
				$g_action_detach_tags_failed[] = tag_get( $f_tag_select );
			}
		}

	}

	global $g_action_detach_tags_detach,  $g_action_detach_tags_failed;

	return true;
}

/**
 * Detaches all the tags to each bug in the group action.
 * @param integer Bug ID
 * @return boolean True if all tags detach properly
 */
function action_detach_tags_process( $p_bug_id ) {
	global $g_action_detach_tags_detach/*, $g_action_detach_tags_create*/;

	$t_user_id = auth_get_current_user_id();


	foreach( $g_action_detach_tags_detach as $t_tag_row ) {
		if ( tag_bug_is_attached( $t_tag_row['id'], $p_bug_id ) ) {
			tag_bug_detach( $t_tag_row['id'], $p_bug_id, $t_user_id );
		}
	}

	return true;
}
