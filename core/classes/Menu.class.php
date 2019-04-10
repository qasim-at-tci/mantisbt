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
 * Menu class.
 * @copyright Copyright 2019 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage classes
 */

/**
 * A class to menus
 */
class Menu {
	/**
	 * Collection of Menu items target (URL or page name).
	 * @var MenuItem[]
	 */
	protected $items = array();

	/**
	 * @var int|string
	 */
	protected $level;

	protected $header;
	protected $item;
	protected $footer;

	/**
	 * CSS classes to style the menu item
	 * @var string
	 */
	protected $css_item;

	/**
	 * Menu level constants.
	 * Used to determine menu item's styling when rendering.
	 */
	const MENU_CUSTOM = 0;
	const MENU_MAIN = 1;
	const MENU_SUB = 2;

	/**
	 * Menu constructor.
	 *
	 * @param int|string $p_level Optional; one of the MENU_xxx constants for
	 *                            predefined menu levels, or CSS classes to
	 *                            apply custom styling
	 */
	function __construct( $p_level = self::MENU_MAIN ) {
		$this->level = $p_level;

		# Set CSS based on Menu level
		switch( $p_level ) {
			case self::MENU_MAIN:
				$t_css = '';
				break;
			case self::MENU_SUB;
				$t_css = 'btn btn-sm btn-white btn-primary';
				break;
			default:
				$t_css = self::MENU_CUSTOM;
		}
		switch( $this->level ) {
			case self::MENU_MAIN:
				$this->header = '<ul class="nav nav-tabs padding-18">';
				$this->item = "<li>%s</li>\n";
				$this->footer = '</ul>';
				break;
			case self::MENU_SUB:
				$t_head = '<div class="space-10"></div>' . PHP_EOL
					. '<div class="col-md-12 col-xs-12 center">' . PHP_EOL
					. '<div class="btn-group">';
				$t_elem = "%s\n";
				$t_foot = '</div></div>';
				break;
		}

	}

	/**
	 * Set custom CSS classes to render menu items
	 *
	 * @param $p_css_item
	 */
	function setCustomLayout( $p_head, $p_foot, $p_elem, $p_css_item ) {
		$this->css_item = $p_css_item;
		$this->level = self::MENU_CUSTOM;
	}

	/**
	 * Append a new Menu Item at the end of the menu.
	 *
	 * @param MenuItem|string $p_item MenuItem object to append to the menu, or
	 *                                a string to use as target (URL or page name)
	 * @param string $p_label Menu item's label
	 * @param string $p_icon  Optional FontAwesome icon to display next to label
	 *
	 * @throws BadMethodCallException
	 */
	function addItem( $p_item, $p_label = '', $p_icon = '' ) {
		if( $p_item instanceof MenuItem ) {
			$this->items[] = $p_item;
		} else {
			if( !$p_label ) {
				throw new BadMethodCallException( "Menu item's label must be provided" );
			}
			$this->items[] = new MenuItem( $p_item, $p_label, $p_icon );
		}
	}

	/**
	 * Render the menu as HTML.
	 *
	 * If a menu item's URL matches the specified current page, it will be
	 * rendered as "active".
	 *
	 * @param string $p_current_page Name/URL of active page
	 * @return string
	 */
	function render( $p_current_page = '' ) {

		$t_html = $t_head . PHP_EOL;
		foreach( $this->items as $t_item ) {
			$t_html .= sprintf( $t_elem, $t_item->render( $p_current_page ) );
		}
		$t_html .= $t_foot . PHP_EOL;

		return $t_html;
	}
}
