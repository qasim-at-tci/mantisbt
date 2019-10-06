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

namespace Mantis\API;

/**
* A class to capture a RestFault
*/
class RestFault {
	/**
	 * @var integer The http status code
	 */
	public $status_code;

	/**
	 * @var string The http status string
	 */
	public $fault_string;

	/**
	 * RestFault constructor.
	 *
	 * @param integer $p_status_code  The http status code
	 * @param string  $p_fault_string The error description
	 */
	function __construct( $p_status_code, $p_fault_string = '' ) {
		$this->status_code = $p_status_code;
		$this->fault_string = $p_fault_string === null ? '' : $p_fault_string;
	}
}
