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
 * Roadmap and Changelog API
 *
 * @package CoreAPI
 * @subpackage RoadmapChangelogAPI
 * @copyright Copyright 2015  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses gpc_api.php
 * @uses project_api.php
 * @uses version_api.php
 */

require_api( 'authentication_api.php' );
require_api( 'error_api.php' );
require_api( 'gpc_api.php' );
require_api( 'project_api.php' );
require_api( 'user_api.php' );
require_api( 'version_api.php' );


class RoadmapChangelogClass {

	# @TODO These should be protected, leave them as public for now to help debugging
	public $project_id;
	public $version_id;

	protected $project;
	protected $projects_list;
	protected $project_index = -1;
	protected $project_header_printed;
	protected $project_name;

	protected $version;
	protected $version_rows;
	protected $version_index;
	protected $version_header_printed;

	/**
	 * Initialize the roadmap/changelog object from GPC parameters
	 * @return void
	 */
	public function __construct() {
		$this->set_project_id();
		$this->set_version_id();
		$this->set_projects_list();

		version_cache_array_rows( $this->projects_list );
		category_cache_array_rows_by_project( $this->projects_list );
	}

	/**
	 * Gets the next project to process
	 * @return int|boolean Project ID, false if there are no more projects to process
	 */
	public function get_next_project() {
		$this->project_index += 1;

		if( !array_key_exists( $this->project_index, $this->projects_list ) ) {
			$this->project = false;
			return false;
		}
		$this->project = $this->projects_list[$this->project_index];

		# Set project data
		$this->project_name = project_get_field( $this->project, 'name' );
		$this->project_header_printed = false;

		# Initialize version data
		$this->version_rows = array_reverse( version_get_all_rows( $this->project ) );
		$this->version_index = -1;

		return $this->project;
	}

	/**
	 * Gets the project being processed
	 * @return int|boolean Project ID, false if not set
	 */
	public function get_current_project() {
		return $this->project;
	}

	/*
	 * Gets the next version to process
	 * @return array|boolean Version row, false if there are no more versions to process
	 */
	public function get_next_version() {
		$this->version = false;
		if( $this->version_id != -1 ) {
			if( $this->version_index == -1 ) {
				foreach( $this->version_rows as $t_version ) {
					if( $t_version['id'] == $this->version_id ) {
						break;
					}
				}
				$this->version_index = 0;
				return $this->version = $t_version;
			}
			return false;
		}
		$this->version_index += 1;
		if( !array_key_exists( $this->version_index, $this->version_rows ) ) {
			return false;
		}

		$this->version = $this->version_rows[$this->version_index];
		$this->version_header_printed = false;

		return $this->version;
	}

	/**
	 * Gets the version row being processed
	 * @return int|boolean Version row, false if not set
	 */
	public function get_current_version() {
		return $this->version;
	}

	/**
	 * Sets the project id from GPC parameters
	 * - if 'project' is specified, get the id from the given name;
	 * - if not, retrieve it from 'project_id'
	 * - if 'project_id' is not defined, use the current project's id
	 * @return void
	 */
	private function set_project_id() {
		$t_project_name = gpc_get_string( 'project', '' );

		if( is_blank( $t_project_name ) ) {
			$t_project_id = gpc_get_int( 'project_id', -1 );
		} else {
			$t_project_id = (int)project_get_id_by_name( $t_project_name );

			if( $t_project_id === 0 ) {
				error_parameters( $t_project_name );
				trigger_error( ERROR_PROJECT_NOT_FOUND, ERROR );
			}
		}

		if( $t_project_id == -1 ) {
			$this->project_id = helper_get_current_project();
		} else  {
			$this->project_id = $t_project_id;
		}
	}

	/**
	 * Sets the version id from GPC parameters and updates project id if needed
	 * - if 'version' is specified, get the id from the given name;
	 * - if not, retrieve it from 'version_id'
	 * - if 'version_id' is not defined, use -1
	 * If both version and project are supplied, version takes precedence (i.e.
	 * the project id will be the specified version's)
	 * @return void
	 */
	private function set_version_id() {
		$t_version_name = gpc_get_string( 'version', '' );

		if( is_blank( $t_version_name ) ) {
			$t_version_id = gpc_get_int( 'version_id', -1 );
			if( $t_version_id != -1 ) {
				$this->project_id = version_get_field( $t_version_id, 'project_id' );
			}
		} else {
			$t_version_id = version_get_id( $t_version_name, $p_project_id );
			if( $t_version_id === false ) {
				error_parameters( $t_version_name );
				trigger_error( ERROR_VERSION_NOT_FOUND, ERROR );
			}
		}

		$this->version_id = $t_version_id;
	}

	/**
	 * Retrieve the list of projects to process
	 * @return void
	 */
	private function set_projects_list() {
		$t_user_id = auth_get_current_user_id();

		if( ALL_PROJECTS == $this->project_id ) {
			$t_project_ids_to_check = user_get_all_accessible_projects( $t_user_id, ALL_PROJECTS );
			$t_projects_list = array();

			foreach( $t_project_ids_to_check as $t_project_id ) {
				$t_access_level = config_get( $this::THRESHOLD, null, null, $t_project_id );
				if( access_has_project_level( $t_access_level, $t_project_id ) ) {
					$t_projects_list[] = $t_project_id;
				}
			}
		} else {
			access_ensure_project_level( config_get( $this::THRESHOLD ), $this->project_id );
			$t_projects_list = user_get_all_accessible_subprojects( $t_user_id, $this->project_id );
			array_unshift( $t_projects_list, $this->project_id );
		}

		$this->projects_list = $t_projects_list;
	}

	/**
	 * Prints the project header
	 * @return void
	 */
	public function print_project_header() {
		if( $this->project_header_printed ) {
			return;
		}

		echo '<h1>'
			. string_display( $this->project_name ) . ' - ' . lang_get( $this::TYPE )
			. '</h1>';
		$this->project_header_printed = true;
		$this->version_header_printed = false;
	}

	/**
	 * Print header for the specified project version.
	 * @param array $p_version_row Array containing project version data
	 * @return void
	 */
	public function print_version_header( array $p_version_row ) {
		if( $this->version_header_printed ) {
			return;
		}

		$t_project_id   = $p_version_row['project_id'];
		$t_version_id   = $p_version_row['id'];
		$t_version_name = $p_version_row['version'];
		$t_description  = $p_version_row['description'];

		$t_release_title = '<a href="roadmap_page.php?project_id=' . $t_project_id . '">'
			. string_display_line( $this->project_name )
			. '</a> - <a href="roadmap_page.php?version_id=' . $t_version_id . '">'
			. string_display_line( $t_version_name )
			. '</a>';

		if( config_get( $this::SHOW_DATES ) ) {
			$t_version_timestamp = $p_version_row['date_order'];
			$t_scheduled_release_date = ' ('
				. lang_get( 'scheduled_release' ) . ' '
				. string_display_line( date( config_get( 'short_date_format' ), $t_version_timestamp ) )
				. ')';
		} else {
			$t_scheduled_release_date = '';
		}

# @TODO open issue for broken temporary filter due to &amp;
		$t_link = 'view_all_set.php?' .
			http_build_query( array(
				'type'                     => 1,
				'temporary'                => 'y',
				FILTER_PROPERTY_PROJECT_ID => $t_project_id,
			) ) .
			'&' .
			filter_encode_field_and_value( FILTER_PROPERTY_TARGET_VERSION, $t_version_name );

		echo '<h2>' . $t_release_title . $t_scheduled_release_date . lang_get( 'word_separator' );
		print_bracket_link( $t_link, lang_get( 'view_bugs_link' ) );
		echo '</h2>';

		# Print version description
		if( !is_blank( $t_description ) ) {
			echo '<p>' . string_display( $t_description ) . '</p>';
		}

# @TODO this tag should be removed eventually
		echo '<tt>';

		$this->version_header_printed = true;
	}


	/**
	 * Prints access-level-specific message when roadmap/changelog is empty
	 */
	public function print_empty() {
		$t_manager = access_has_project_level(
			config_get( 'manage_project_threshold' ),
			$this->project_id
		);

		echo '<p>'
			. lang_get( $this->empty_string( $t_manager ) )
			. '</p>';
	}

	/**
	 * Returns name of the config threshold to view the roadmap/changelog
	 * @return string
	 */
	protected function threshold() {}

	/**
	 * Returns the language string to display when the roadmap/changelog is empty
	 * @param bool $p_manager Manager-specific string if true, default user string otherwise
	 * @return string
	 */
	protected function empty_string( $p_manager ) {}

}


class RoadmapClass extends RoadmapChangelogClass {

	const TYPE = 'roadmap';

	/**
	 * name of the config threshold to view the roadmap
	 */
	const THRESHOLD = 'roadmap_view_threshold';

	/**
	 * name of the config to show the dates
	 */
	const SHOW_DATES = 'show_roadmap_dates';

	/*
	 * Gets the next version to process
	 * Roadmap excludes released versions
	 * @return int|boolean Version ID, false if there are no more versions to process
	 */
	public function get_next_version() {
		while( ( $t_version = parent::get_next_version() ) && $t_version['released'] ) {
		}
		return $t_version;
	}

	/**
	 * Print the progress bar
	 * @param int $p_progress Percent complete
	 * @return void
	 */
	public function print_progress_bar( $p_progress ) {
		# Progress bar handled with jQueryUI widget
		echo '<div class="roadmap-progress" data-progress="' . $p_progress . '"></div>';
	}

	protected function threshold() {
		return 'roadmap_view_threshold';
	}

	protected function empty_string( $p_manager ) {
		return $p_manager ? 'roadmap_empty_manager' : 'roadmap_empty';
	}

}


class ChangelogClass extends RoadmapChangelogClass {

	const TYPE = 'changelog';

	/**
	 * name of the config threshold to view the roadmap
	 */
	const THRESHOLD = 'changelog_view_threshold';

	/**
	 * name of the config to show the dates
	 */
	const SHOW_DATES = 'show_changelog_dates';

	protected function empty_string( $p_manager ) {
		return $p_manager ? 'changelog_empty_manager' : 'changelog_empty';
	}

}
