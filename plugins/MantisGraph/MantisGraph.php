<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

/**
 * Mantis Graph plugin
 */
class MantisGraphPlugin extends MantisPlugin  {

	/**
	 * A method that populates the plugin information and minimum requirements.
	 * @return void
	 */
	function register() {
		$this->name = lang_get( 'plugin_graph_title' );
		$this->description = lang_get( 'plugin_graph_description' );
		$this->page = 'config';

		$this->version = '1.3.0';
		$this->requires = array(
			'MantisCore' => '1.3.0',
		);

		$this->author = 'MantisBT Team';
		$this->contact = 'mantisbt-dev@lists.sourceforge.net';
		$this->url = 'http://www.mantisbt.org';
	}

	/**
	 * Default plugin configuration.
	 * @return array
	 */
	function config() {
		return array(
			'eczlibrary' => ON,

			'window_width' => 800,
			'bar_aspect' => 0.9,
			'summary_graphs_per_row' => 2,
			'font' => 'arial',

			'jpgraph_path' => '',
			'jpgraph_antialias' => ON,
		);
	}

	/**
	 * init function
	 * @return void
	 */
	function init() {
		spl_autoload_register( array( 'MantisGraphPlugin', 'autoload' ) );
	}

	/**
	 * class auto loader
	 * @param string $p_class Class name to autoload.
	 * @return void
	 */
	public static function autoload( $p_class ) {
		if( class_exists( 'ezcBase' ) ) {
			ezcBase::autoload( $p_class );
		}
	}

	/**
	 * plugin hooks
	 * @return array
	 */
	function hooks() {
		$t_hooks = array(
			'EVENT_MENU_SUMMARY' => 'summary_menu',
			'EVENT_SUBMENU_SUMMARY' => 'summary_submenu',
			'EVENT_MENU_FILTER' => 'graph_filter_menu',
		);
		return $t_hooks;
	}

	/**
	 * Generate an array with html code for menu elements
	 * @param array $p_menu_data menu elements (url, icon, text)
	 * @return array
	 */
	protected function generate_menu_array( $p_menu_data ) {
		$t_icon_path = config_get( 'icon_path' );
		$t_menu_items = array();

		foreach( $p_menu_data as $t_item ) {
			$t_text = plugin_lang_get( $t_item[2] );
			$t_icon = empty( $t_item[1] )
				? ''
				: '<img class="menu-icon" src="' . $t_icon_path . $t_item[1] . '" alt="" />';
			$t_menu_items[] = sprintf( '<a href="%s">%s%s</a>', $t_item[0], $t_icon, $t_text );
		}

		return $t_menu_items;
	}

	/**
	 * generate summary menu
	 * @return array
	 */
	function summary_menu() {
		return $this->generate_menu_array( array(
			array( plugin_page( 'summary_graph_page.php' ), 'synthgraph.gif', 'menu_advanced_summary' ),
		) );
	}

	/**
	 * generate graph filter menu
	 * @return array
	 */
	function graph_filter_menu() {
		return $this->generate_menu_array( array(
			array( plugin_page( 'bug_graph_page.php' ), 'synthgraph.gif', 'graph_bug_page_link' ),
		) );
	}

	/**
	 * generate summary submenu
	 * @return array
	 */
	function summary_submenu() {
		$t_menu_data = array(
			array( helper_mantis_url( 'summary_page.php' ), 'synthese.gif', 'synthesis_link' ),
			array( plugin_page( 'summary_graph_imp_status.php' ), 'synthgraph.gif', 'status_link' ),
			array( plugin_page( 'summary_graph_imp_priority.php' ), 'synthgraph.gif', 'priority_link' ),
			array( plugin_page( 'summary_graph_imp_severity.php' ), 'synthgraph.gif', 'severity_link' ),
			array( plugin_page( 'summary_graph_imp_category.php' ), 'synthgraph.gif', 'category_link' ),
			array( plugin_page( 'summary_graph_imp_resolution.php' ), 'synthgraph.gif', 'resolution_link' ),
		);

		return $this->generate_menu_array( $t_menu_data );
	}
}
