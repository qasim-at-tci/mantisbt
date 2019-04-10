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
 * Menu Item class.
 * @copyright Copyright 2019 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage classes
 */

/**
 * A class to manage menu items
 */
class MenuItem {
	/**
	 * Menu item's target (URL or page name).
	 * @var string
	 */
	public $url;

	/**
	 * Menu item's label.
	 * @var string
	 */
	public $label;

	/**
	 * FontAwesome icon to display next to label.
	 * @var string
	 */
	public $icon;

	/**
	 * Templates for HTML rendering.
	 * @see render()
	 */
	const TEMPLATE_LINK = '<a class="%s" href="%s">%s</a>';
	const TEMPLATE_ICON = '<i class="fa %s"></i>';

	/**
	 * Menu level constants.
	 * Used to determine menu item's styling when rendering.
	 */
	const MENU_MAIN = 1;
	const MENU_SUB = 2;

	/**
	 * MenuItem constructor.
	 *
	 * @param string $p_url
	 * @param string $p_label
	 * @param string $p_icon
	 */
	function __construct( $p_url, $p_label, $p_icon = '' ) {
		$this->url = $p_url;
		$this->label = $p_label;
		$this->icon = $p_icon;
	}

	/**
	 * Render the menu item as HTML.
	 *
	 * If the menu item's URL matches the specified current page, it will be
	 * rendered as "active".
	 *
	 * @param string $p_current_page Name/URL of active page
	 * @param int|string $p_level Optional; one of the MENU_xxx constants for
	 *                            predefined menu levels, or CSS classes to
	 *                            apply custom styling
	 * @return string
	 */
	function render( $p_current_page = '', $p_level = self::MENU_MAIN ) {
		$t_html = $this->label;

		if( $this->icon ) {
			$t_icon = sprintf( self::TEMPLATE_ICON, $this->icon );
			if( layout_is_rtl() ) {
				$t_html .= '&nbsp;' . $t_icon;
			}
			else {
				$t_html = $t_icon . '&nbsp;' . $t_html;
			}
		}

		# CSS
		switch( $p_level ) {
			case self::MENU_MAIN:
				$t_css = '';
				break;
			case self::MENU_SUB;
				$t_css = 'btn btn-sm btn-white btn-primary';
				break;
			default:
				$t_css = $p_level;
		}
		if( $p_current_page == $this->url ) {
			$t_css .= ' active';
		}

		return sprintf(
			self::TEMPLATE_LINK,
			$t_css,
			$this->url,
			$t_html
		);
	}
}
