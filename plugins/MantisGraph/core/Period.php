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
 * Class for actions dealing with date periods
 *
 * This class encapsulates all actions dealing with time intervals. It handles data
 * storage, and retrieval, as well as formatting and access.
 *
 * @copyright Logical Outcome Ltd. 2005 - 2007
 * @author Glenn Henshaw <thraxisp@logicaloutcome.ca>
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage classes
 */
class Period {
	/**
	 * Period types constants
	 */
	const PERIOD_NONE = null;
	const PERIOD_THIS_MONTH = 1;
	const PERIOD_LAST_MONTH = 2;
	const PERIOD_THIS_QUARTER = 3;
	const PERIOD_LAST_QUARTER = 4;
	const PERIOD_YEAR_TO_DATE = 5;
	const PERIOD_LAST_YEAR = 6;
	const PERIOD_THIS_WEEK = 7;
	const PERIOD_LAST_WEEK = 8;
	const PERIOD_TWO_WEEKS = 9;
	const PERIOD_CUSTOM = 10;

	/**
	 * Period types.
	 * @var array
	 */
	private $periods;
	
	/**
	 * start date
	 * @var string
	 */
	private $start;

	/**
	 * end date
	 * @var string
	 */
	private $end;

	/**
	 * Constructor
	 */
	function __construct() {
		# default to today
		$this->start = '';
		$this->end = '';

		$this->periods = array(
			self::PERIOD_NONE => plugin_lang_get( 'period_none' ),
			self::PERIOD_THIS_WEEK => plugin_lang_get( 'period_this_week' ),
			self::PERIOD_LAST_WEEK => plugin_lang_get( 'period_last_week' ),
			self::PERIOD_TWO_WEEKS => plugin_lang_get( 'period_two_weeks' ),
			self::PERIOD_THIS_MONTH => plugin_lang_get( 'period_this_month' ),
			self::PERIOD_LAST_MONTH => plugin_lang_get( 'period_last_month' ),
			self::PERIOD_THIS_QUARTER => plugin_lang_get( 'period_this_quarter' ),
			self::PERIOD_LAST_QUARTER => plugin_lang_get( 'period_last_quarter' ),
			self::PERIOD_YEAR_TO_DATE => plugin_lang_get( 'period_year_to_date' ),
			self::PERIOD_LAST_YEAR => plugin_lang_get( 'period_last_year' ),
			self::PERIOD_CUSTOM => plugin_lang_get( 'period_select' ),
		);

	}

	/**
	 * set dates for a week
	 *
	 * @param string  $p_when  Date string to expand to a week (Sun to Sat).
	 * @param integer $p_weeks Number of weeks.
	 * @return void
	 */
	function a_week( $p_when, $p_weeks = 1 ) {
		list( $t_year, $t_month, $t_day ) = explode( '-', $p_when );
		$t_now = getdate( mktime( 0, 0, 0, $t_month, $t_day, $t_year ) );
		$this->end = strftime( '%Y-%m-%d 23:59:59', mktime( 0, 0, 0, $t_month, $t_day - $t_now['wday'] + ( $p_weeks * 7 ) - 1, $t_year ) );
		$this->start = strftime( '%Y-%m-%d 00:00:00', mktime( 0, 0, 0, $t_month, $t_day - $t_now['wday'], $t_year ) );
	}

	/**
	 * set dates for this week
	 * @return void
	 */
	function this_week() {
		$this->a_week( date( 'Y-m-d' ) );
	}

	/**
	 * set dates for last week
	 *
	 * @param integer $p_weeks Number of weeks.
	 * @return void
	 */
	function last_week( $p_weeks = 1 ) {
		$this->a_week( date( 'Y-m-d', strtotime( '-' . $p_weeks . ' week' ) ), $p_weeks );
	}

	/**
	 * set dates for this week to date
	 *
	 * @return void
	 */
	function week_to_date() {
		$this->this_week();
		$this->end = date( 'Y-m-d' ) . ' 23:59:59';
	}

	/**
	 * set dates for a month
	 *
	 * @param string $p_when Date string to expand to a month.
	 * @return void
	 */
	function a_month( $p_when ) {
		list( $t_year, $t_month, ) = explode( '-', $p_when );
		$this->end = strftime( '%Y-%m-%d 23:59:59', mktime( 0, 0, 0, $t_month + 1, 0, $t_year ) );
		$this->start = strftime( '%Y-%m-%d 00:00:00', mktime( 0, 0, 0, $t_month, 1, $t_year ) );
	}

	/**
 	 * set dates for this month
 	 *
	 * @return void
	 */
	function this_month() {
		$this->a_month( date( 'Y-m-d' ) );
	}

	/**
	 * set dates for last month
	 *
	 * @return void
	 */
	function last_month() {
		$this->a_month( date( 'Y-m-d', strtotime( '-1 month' ) ) );
	}

	/**
	 * set dates for this month to date
	 *
	 * @return void
	 */
	function month_to_date() {
		$this->end = date( 'Y-m-d' ) . ' 23:59:59';
		list( $t_year, $t_month, ) = explode( '-', $this->end );
		$this->start = strftime( '%Y-%m-%d 00:00:00', mktime( 0, 0, 0, $t_month, 1, $t_year ) );
	}

	/**
	 * set dates for a quarter
	 *
	 * @param string $p_when Date string to expand to a quarter.
	 * @return void
	 */
	function a_quarter( $p_when ) {
		list( $t_year, $t_month, ) = explode( '-', $p_when );
		$t_month = ( (int)(( $t_month - 1 ) / 3 ) * 3 ) + 1;
		$this->end = strftime( '%Y-%m-%d 23:59:59', mktime( 0, 0, 0, $t_month + 3, 0, $t_year ) );
		$this->start = strftime( '%Y-%m-%d 00:00:00', mktime( 0, 0, 0, $t_month, 1, $t_year ) );
	}

	/**
	 * set dates for this quarter
	 *
	 * @return void
	 */
	function this_quarter() {
		$this->a_quarter( date( 'Y-m-d' ) );
	}

	/**
	 * set dates for last month
	 *
	 * @return void
	 */
	function last_quarter() {
		$this->a_quarter( date( 'Y-m-d', strtotime( '-3 months' ) ) );
	}

	/**
	 * set dates for this quarter to date
	 *
	 * @return void
	 */
	function quarter_to_date() {
		$this->end = date( 'Y-m-d' ) . ' 23:59:59';
		list( $t_year, $t_month, ) = explode( '-', $this->end );
		$t_month = ( (int)(( $t_month - 1 ) / 3 ) * 3 ) + 1;
		$this->start = strftime( '%Y-%m-%d 00:00:00', mktime( 0, 0, 0, $t_month, 1, $t_year ) );
	}

	/**
	 * set dates for a year
	 *
	 * @param string $p_when Date string to expand to a year.
	 * @return void
	 */
	function a_year( $p_when ) {
		list( $t_year,, ) = explode( '-', $p_when );
		$this->end = strftime( '%Y-%m-%d 23:59:59', mktime( 0, 0, 0, 12, 31, $t_year ) );
		$this->start = strftime( '%Y-%m-%d 00:00:00', mktime( 0, 0, 0, 1, 1, $t_year ) );
	}

	/**
	 * set dates for this year
	 *
	 * @return void
	 */
	function this_year() {
		$this->a_year( date( 'Y-m-d' ) );
	}

	/**
	 * set dates for current year, ending today
	 *
	 * @return void
	 */
	function year_to_date() {
		$this->end = date( 'Y-m-d' ) . ' 23:59:59';
		list( $t_year,, ) = explode( '-', $this->end );
		$this->start = strftime( '%Y-%m-%d 00:00:00', mktime( 0, 0, 0, 1, 1, $t_year ) );
	}

	/**
	 * set dates for last year
	 *
	 * @return void
	 */
	function last_year() {
		$this->a_year( date( 'Y-m-d', strtotime( '-1 year' ) ) );
	}

	/**
	 * get start date in unix timestamp format
	 *
	 * @return integer
	 */
	function get_start_timestamp() {
		return strtotime( $this->start );
	}

	/**
	 * get end date in unix timestamp format
	 *
	 * @return integer
	 */
	function get_end_timestamp() {
		return strtotime( $this->end );
	}

	/**
	 * get formatted start date
	 *
	 * @return string
	 */
	function get_start_formatted() {
		return( $this->start == '' ? '' : strftime( '%Y-%m-%d', $this->get_start_timestamp() ) );
	}

	/**
	 * get formatted end date
	 *
	 * @return string
	 */
	function get_end_formatted() {
		return( $this->end == '' ? '' : strftime( '%Y-%m-%d', $this->get_end_timestamp() ) );
	}

	/**
	 * get number of days in interval
     * @return integer
	 */
	function get_elapsed_days() {
		return( $this->get_end_timestamp() - $this->get_start_timestamp() ) / ( 24 * 60 * 60 );
	}

	/**
	 * print a period selector
	 *
	 * @param string $p_control_name Value representing the name of the html control on the web page.
     * @return string
	 */
	function period_selector( $p_control_name ) {
		$t_default = gpc_get_int( $p_control_name, 0 );
		$t_formatted_start = $this->get_start_formatted();
		$t_formatted_end = $this->get_end_formatted();

		# printf mask to display the date entry field + date picker
		$t_lang_locale = lang_get_current_datetime_locale();
		$t_date_field = <<<HTML
<div class="pull-left padding-left-8">
	<label for="%1\$s">%2\$s</label>
	<input type="text" id="%1\$s" name="%1\$s" size="12"
		   class="datetimepicker input-sm"
		   data-picker-locale="$t_lang_locale" data-picker-format="Y-MM-DD"
		   value="%3\$s"
	/>
	<i class="fa fa-calendar fa-lg datetimepicker"></i>
</div>
HTML;

		$t_ret = '<div id="period_menu">' . PHP_EOL;
		$t_ret .= '<div class="pull-left">' . PHP_EOL;
		$t_ret .= get_dropdown( $this->periods, $p_control_name, $t_default, false, false, true ) . PHP_EOL;
		$t_ret .= "</div>\n";
		# Javascript will dynamically show/hide Dates selectors based on
		# selected Period Type
		$t_ret .= '<div id="dates" class="pull-left">' . PHP_EOL;
		$t_ret .= sprintf( $t_date_field,
				'start_date',
				lang_get( 'from_date' ),
				$t_formatted_start
			);
		$t_ret .= sprintf( $t_date_field,
				'end_date',
				lang_get( 'to_date' ),
				$t_formatted_end
			);
		$t_ret .= "</div>\n";
		$t_ret .= "</div>\n";
		return $t_ret;
	}

	/**
	 * set date based on period selector
	 *
	 * @param string $p_control_name Value representing the name of the html control on the web page.
	 * @param string $p_start_field  Name representing the name of the starting field on the date selector i.e. start_date.
	 * @param string $p_end_field    Name representing the name of the ending field on the date selector i.e. end_date.
	 * @return void
	 */
	function set_period_from_selector( $p_control_name, $p_start_field = 'start_date', $p_end_field = 'end_date' ) {
		$t_default = gpc_get_int( $p_control_name, 0 );
		switch( $t_default ) {
			case self::PERIOD_THIS_MONTH:
				$this->month_to_date();
				break;
			case self::PERIOD_LAST_MONTH:
				$this->last_month();
				break;
			case self::PERIOD_THIS_QUARTER:
				$this->quarter_to_date();
				break;
			case self::PERIOD_LAST_QUARTER:
				$this->last_quarter();
				break;
			case self::PERIOD_YEAR_TO_DATE:
				$this->year_to_date();
				break;
			case self::PERIOD_LAST_YEAR:
				$this->last_year();
				break;
			case self::PERIOD_THIS_WEEK:
				$this->week_to_date();
				break;
			case self::PERIOD_LAST_WEEK:
				$this->last_week();
				break;
			case self::PERIOD_TWO_WEEKS:
				$this->last_week( 2 );
				break;
			case self::PERIOD_CUSTOM:
				$t_today = date( 'Y-m-d' );
				if( $p_start_field != '' ) {
					$this->start = gpc_get_string( $p_start_field, '' ) . ' 00:00:00';
					if( $this->start == '' ) {
						$this->start = $t_today . ' 00:00:00';
					}
				}
				if( $p_end_field != '' ) {
					$this->end = gpc_get_string( $p_end_field, '' ) . ' 23:59:59';
					if( $this->end == '' ) {
						$this->end = $t_today . ' 23:59:59';
					}
				}
				break;
			default:
		}
	}
}
