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
 * Display Project Roadmap
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses filter_api.php
 * @uses filter_constants_inc.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'category_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'filter_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );
require_api( 'version_api.php' );
require_api( 'roadmap_changelog_api.php' );


$t_issues_found = false;
$t_user_id = auth_get_current_user_id();

# -------------------------------------------------------

# Initialize the roadmap
$t_roadmap = new RoadmapClass();

html_page_top( lang_get( 'roadmap' ) );

while( $t_project_id = $t_roadmap->get_next_project() ) {
	$t_can_view_private = access_has_project_level( config_get( 'private_bug_threshold' ), $t_project_id );

	$t_limit_reporters = config_get( 'limit_reporters' );
	$t_user_access_level_is_reporter = ( config_get( 'report_bug_threshold', null, null, $t_project_id ) == access_get_project_level( $t_project_id ) );

	$t_resolved = config_get( 'bug_resolved_status_threshold' );

	# cache category info, but ignore the results for now
	category_get_all_rows( $t_project_id );

	while( $t_version_row = $t_roadmap->get_next_version() ) {
		if( $t_roadmap->get_issues() ) {
			$t_roadmap->print_project_header();
			$t_roadmap->print_version_header( $t_version_row );
			$t_roadmap->print_progress_bar();
		}

		$t_issue_set_ids = array();
		$t_issue_set_levels = array();
		$k = 0;

		$t_cycle = false;
		$t_cycle_ids = array();

		while( 0 < count( $t_roadmap->issues ) ) {
			$t_issue_id = $t_roadmap->issues[$k];
			$t_issue_parent = $t_roadmap->issues_parents[$k];

			if( in_array( $t_issue_id, $t_cycle_ids ) && in_array( $t_issue_parent, $t_cycle_ids ) ) {
				$t_cycle = true;
			} else {
				$t_cycle = false;
				$t_cycle_ids[] = $t_issue_id;
			}

			if( $t_cycle || !in_array( $t_issue_parent, $t_roadmap->issues ) ) {
				$l = array_search( $t_issue_parent, $t_issue_set_ids );
				if( $l !== false ) {
					for( $m = $l+1; $m < count( $t_issue_set_ids ) && $t_issue_set_levels[$m] > $t_issue_set_levels[$l]; $m++ ) {
						#do nothing
					}
					$t_issue_set_ids_end = array_splice( $t_issue_set_ids, $m );
					$t_issue_set_levels_end = array_splice( $t_issue_set_levels, $m );
					$t_issue_set_ids[] = $t_issue_id;
					$t_issue_set_levels[] = $t_issue_set_levels[$l] + 1;
					$t_issue_set_ids = array_merge( $t_issue_set_ids, $t_issue_set_ids_end );
					$t_issue_set_levels = array_merge( $t_issue_set_levels, $t_issue_set_levels_end );
				} else {
					$t_issue_set_ids[] = $t_issue_id;
					$t_issue_set_levels[] = 0;
				}
				array_splice( $t_roadmap->issues, $k, 1 );
				array_splice( $t_roadmap->issues_parents, $k, 1 );

				$t_cycle_ids = array();
			} else {
				$k++;
			}
			if( count( $t_roadmap->issues ) <= $k ) {
				$k = 0;
			}
		}

		$t_count_ids = count( $t_issue_set_ids );
		for( $j = 0; $j < $t_count_ids; $j++ ) {
			$t_issue_set_id = $t_issue_set_ids[$j];
			$t_issue_set_level = $t_issue_set_levels[$j];

			helper_call_custom_function( 'roadmap_print_issue', array( $t_issue_set_id, $t_issue_set_level ) );

			$t_issues_found = true;
		}

		if( $t_issues_planned > 0 ) {
			echo '<br />';
			echo sprintf( lang_get( 'resolved_progress' ), $t_issues_resolved, $t_issues_planned, $t_progress );
			echo '<br /></tt>';
		}
	}
}


if( !$t_issues_found ) {
	$t_roadmap->print_empty();
}

html_page_bottom();
