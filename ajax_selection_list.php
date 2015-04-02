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
 * Selection list AJAX
 * Prints a selection list for the specified field and bug id
 *
 * @package MantisBT
 * @copyright Copyright 2015  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @author Damien Regad - dregad@mantisbt.org
  * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses compress_api.php
 * @uses gpc_api.php
 * @uses bug_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
require_api( 'compress_api.php' );
require_api( 'gpc_api.php' );
require_api( 'bug_api.php' );
require_api( 'print_api.php' );

compress_enable();

$f_field  = gpc_get_string( 'field' );
$f_bug_id = gpc_get_int( 'bug_id' );

$t_bug = bug_get( $f_bug_id, true );

switch( $f_field ) {
	case 'reporter_id':
		$t_function = 'print_reporter_option_list';
		break;
	default:
		trigger_error( ERROR_GENERIC, ERROR );
		return;
}

echo '<select ' . helper_get_tab_index() . ' id="' . $f_field . '" name="' . $f_field . '">';
call_user_func_array( $t_function, array( $t_bug->reporter_id, $t_bug->project_id ) );
echo '</select>';
