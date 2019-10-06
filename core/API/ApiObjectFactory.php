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

use \SoapFault;
use Mantis\Exceptions\LegacyApiFaultException;

/**
 * A factory class that can abstract away operations that can behave differently based
 * on the API being accessed (SOAP vs. REST).
 */
class ApiObjectFactory {
	/**
	 * @var bool true: SOAP API, false: REST API
	 */
	static public $soap = true;

	/**
	 * Generate a new fault - this method should only be called from within this factory class.  Use methods for
	 * specific error cases.
	 *
	 * @param string $p_fault_code   SOAP fault code (Server or Client).
	 * @param string $p_fault_string Fault description.
	 * @param integer $p_status_code The http status code.
	 * @return RestFault|SoapFault The fault object.
	 * @access private
	 */
	static function fault( $p_fault_code, $p_fault_string, $p_status_code = null ) {
		# Default status code based on fault code, if not specified.
		if( $p_status_code === null ) {
			$p_status_code = ( $p_fault_code == 'Server' ) ? 500 : 400;
		}

		if( ApiObjectFactory::$soap ) {
			return new SoapFault( $p_fault_code, $p_fault_string );
		}

		return new RestFault( $p_status_code, $p_fault_string );
	}

	/**
	 * Fault generated when a resource doesn't exist.
	 *
	 * @param string $p_fault_string The fault details.
	 * @return RestFault|SoapFault The fault object.
	 */
	static function faultNotFound( $p_fault_string ) {
		return ApiObjectFactory::fault( 'Client', $p_fault_string, HTTP_STATUS_NOT_FOUND );
	}

	/**
	 * Fault generated when an operation is not allowed.
	 *
	 * @param string $p_fault_string The fault details.
	 * @return RestFault|SoapFault The fault object.
	 */
	static function faultForbidden( $p_fault_string ) {
		return ApiObjectFactory::fault( 'Client', $p_fault_string, HTTP_STATUS_FORBIDDEN );
	}

	/**
	 * Fault generated when a request is invalid.
	 *
	 * @param string $p_fault_string The fault details.
	 * @return RestFault|SoapFault The fault object.
	 */
	static function faultBadRequest( $p_fault_string ) {
		return ApiObjectFactory::fault( 'Client', $p_fault_string, HTTP_STATUS_BAD_REQUEST );
	}

	/**
	 * Fault generated when a client hits rate limits.
	 *
	 * @param string $p_fault_string The fault details.
	 * @return RestFault|SoapFault The fault object.
	 */
	static function faultTooManyRequests( $p_fault_string ) {
		return ApiObjectFactory::fault( 'Client', $p_fault_string, HTTP_STATUS_TOO_MANY_REQUESTS );
	}

	/**
	 * Fault generated when the request is failed due to conflict with current state of the data.
	 * This can happen either due to a race condition or lack of checking on client side before
	 * issuing the request.
	 *
	 * @param string $p_fault_string The fault details.
	 * @return RestFault|SoapFault The fault object.
	 */
	static function faultConflict( $p_fault_string ) {
		return ApiObjectFactory::fault( 'Client', $p_fault_string, HTTP_STATUS_CONFLICT );
	}

	/**
	 * Fault generated when a request fails due to server error.
	 *
	 * @param string $p_fault_string The fault details.
	 * @return RestFault|SoapFault The fault object.
	 */
	static function faultServerError( $p_fault_string ) {
		return ApiObjectFactory::fault( 'Server', $p_fault_string, HTTP_STATUS_INTERNAL_SERVER_ERROR );
	}

	/**
	 * Generate fault based on provided exception.
	 *
	 * @param \Exception $p_exception The exception to process.
	 * @return RestFault|SoapFault The fault object.
	 */
	static function faultFromException( \Exception $p_exception ) {
		$t_code = $p_exception->getCode();

		switch( $t_code ) {
			case ERROR_NO_FILE_SPECIFIED:
			case ERROR_FILE_DISALLOWED:
			case ERROR_DUPLICATE_PROJECT:
			case ERROR_EMPTY_FIELD:
			case ERROR_INVALID_REQUEST_METHOD:
			case ERROR_INVALID_SORT_FIELD:
			case ERROR_INVALID_DATE_FORMAT:
			case ERROR_INVALID_RESOLUTION:
			case ERROR_FIELD_TOO_LONG:
			case ERROR_CONFIG_OPT_NOT_FOUND:
			case ERROR_CONFIG_OPT_CANT_BE_SET_IN_DB:
			case ERROR_CONFIG_OPT_BAD_SYNTAX:
			case ERROR_GPC_VAR_NOT_FOUND:
			case ERROR_GPC_ARRAY_EXPECTED:
			case ERROR_GPC_ARRAY_UNEXPECTED:
			case ERROR_GPC_NOT_NUMBER:
			case ERROR_FILE_TOO_BIG:
			case ERROR_FILE_NOT_ALLOWED:
			case ERROR_FILE_DUPLICATE:
			case ERROR_FILE_NO_UPLOAD_FAILURE:
			case ERROR_PROJECT_NAME_NOT_UNIQUE:
			case ERROR_PROJECT_NAME_INVALID:
			case ERROR_PROJECT_RECURSIVE_HIERARCHY:
			case ERROR_USER_NAME_NOT_UNIQUE:
			case ERROR_USER_CREATE_PASSWORD_MISMATCH:
			case ERROR_USER_NAME_INVALID:
			case ERROR_USER_DOES_NOT_HAVE_REQ_ACCESS:
			case ERROR_USER_CHANGE_LAST_ADMIN:
			case ERROR_USER_REAL_NAME_INVALID:
			case ERROR_USER_EMAIL_NOT_UNIQUE:
			case ERROR_BUG_DUPLICATE_SELF:
			case ERROR_BUG_RESOLVE_DEPENDANTS_BLOCKING:
			case ERROR_BUG_CONFLICTING_EDIT:
			case ERROR_EMAIL_INVALID:
			case ERROR_EMAIL_DISPOSABLE:
			case ERROR_CUSTOM_FIELD_NAME_NOT_UNIQUE:
			case ERROR_CUSTOM_FIELD_IN_USE:
			case ERROR_CUSTOM_FIELD_INVALID_VALUE:
			case ERROR_CUSTOM_FIELD_INVALID_DEFINITION:
			case ERROR_CUSTOM_FIELD_NOT_LINKED_TO_PROJECT:
			case ERROR_CUSTOM_FIELD_INVALID_PROPERTY:
			case ERROR_CATEGORY_DUPLICATE:
			case ERROR_CATEGORY_NO_ACTION:
			case ERROR_CATEGORY_NOT_FOUND_FOR_PROJECT:
			case ERROR_VERSION_DUPLICATE:
			case ERROR_SPONSORSHIP_NOT_ENABLED:
			case ERROR_SPONSORSHIP_AMOUNT_TOO_LOW:
			case ERROR_SPONSORSHIP_SPONSOR_NO_EMAIL:
			case ERROR_RELATIONSHIP_ALREADY_EXISTS:
			case ERROR_RELATIONSHIP_SAME_BUG:
			case ERROR_LOST_PASSWORD_CONFIRM_HASH_INVALID:
			case ERROR_LOST_PASSWORD_NO_EMAIL_SPECIFIED:
			case ERROR_LOST_PASSWORD_NOT_MATCHING_DATA:
			case ERROR_SIGNUP_NOT_MATCHING_CAPTCHA:
			case ERROR_TAG_DUPLICATE:
			case ERROR_TAG_NAME_INVALID:
			case ERROR_TAG_NOT_ATTACHED:
			case ERROR_TAG_ALREADY_ATTACHED:
			case ERROR_COLUMNS_DUPLICATE:
			case ERROR_COLUMNS_INVALID:
			case ERROR_API_TOKEN_NAME_NOT_UNIQUE:
			case ERROR_INVALID_FIELD_VALUE:
			case ERROR_PROJECT_SUBPROJECT_DUPLICATE:
			case ERROR_PROJECT_SUBPROJECT_NOT_FOUND:
				return ApiObjectFactory::faultBadRequest( $p_exception->getMessage() );

			case ERROR_BUG_NOT_FOUND:
			case ERROR_FILE_NOT_FOUND:
			case ERROR_BUGNOTE_NOT_FOUND:
			case ERROR_PROJECT_NOT_FOUND:
			case ERROR_USER_PREFS_NOT_FOUND:
			case ERROR_USER_PROFILE_NOT_FOUND:
			case ERROR_USER_BY_NAME_NOT_FOUND:
			case ERROR_USER_BY_ID_NOT_FOUND:
			case ERROR_USER_BY_EMAIL_NOT_FOUND:
			case ERROR_USER_BY_REALNAME_NOT_FOUND:
			case ERROR_NEWS_NOT_FOUND:
			case ERROR_BUG_REVISION_NOT_FOUND:
			case ERROR_CUSTOM_FIELD_NOT_FOUND:
			case ERROR_CATEGORY_NOT_FOUND:
			case ERROR_VERSION_NOT_FOUND:
			case ERROR_SPONSORSHIP_NOT_FOUND:
			case ERROR_RELATIONSHIP_NOT_FOUND:
			case ERROR_FILTER_NOT_FOUND:
			case ERROR_TAG_NOT_FOUND:
			case ERROR_TOKEN_NOT_FOUND:
				return ApiObjectFactory::faultNotFound( $p_exception->getMessage() );

			case ERROR_ACCESS_DENIED:
			case ERROR_PROTECTED_ACCOUNT:
			case ERROR_HANDLER_ACCESS_TOO_LOW:
			case ERROR_USER_CURRENT_PASSWORD_MISMATCH:
			case ERROR_AUTH_INVALID_COOKIE:
			case ERROR_BUG_READ_ONLY_ACTION_DENIED:
			case ERROR_LDAP_AUTH_FAILED:
			case ERROR_LDAP_USER_NOT_FOUND:
			case ERROR_CATEGORY_CANNOT_DELETE_DEFAULT:
			case ERROR_CATEGORY_CANNOT_DELETE_HAS_ISSUES:
			case ERROR_SPONSORSHIP_HANDLER_ACCESS_LEVEL_TOO_LOW:
			case ERROR_SPONSORSHIP_ASSIGNER_ACCESS_LEVEL_TOO_LOW:
			case ERROR_RELATIONSHIP_ACCESS_LEVEL_TO_DEST_BUG_TOO_LOW:
			case ERROR_LOST_PASSWORD_NOT_ENABLED:
			case ERROR_LOST_PASSWORD_MAX_IN_PROGRESS_ATTEMPTS_REACHED:
			case ERROR_FORM_TOKEN_INVALID:
				return ApiObjectFactory::faultForbidden( $p_exception->getMessage() );

			case ERROR_SPAM_SUSPECTED:
				return ApiObjectFactory::faultTooManyRequests( $p_exception->getMessage() );

			case ERROR_CONFIG_OPT_INVALID:
			case ERROR_FILE_INVALID_UPLOAD_PATH:
				# TODO: These are configuration or db state errors.
				return ApiObjectFactory::faultServerError( $p_exception->getMessage() );

			default:
				return ApiObjectFactory::faultServerError( $p_exception->getMessage() );
		}
	}

	/**
	 * Convert a soap object to an array
	 * @param \stdClass|array $p_object Object.
	 * @param boolean $p_recursive
	 * @return array
	 */
	static function objectToArray( $p_object, $p_recursive = false ) {
		$t_object = is_object( $p_object ) ? get_object_vars( $p_object ) : $p_object;
		if( $p_recursive && is_array( $t_object ) ) {
			foreach( $t_object as $t_key => $t_value ) {
				if( is_object( $t_object[$t_key] ) || is_array( $t_object[$t_key] ) ) {
					$t_object[$t_key] = ApiObjectFactory::objectToArray( $t_object[$t_key], $p_recursive );
				}
			}
		}

		return $t_object;
	}

	/**
	 * Convert a timestamp to a soap DateTime variable
	 * @param integer $p_value Integer value to return as date time string.
	 * @return datetime in expected API format.
	 */
	static function datetime($p_value ) {
		$t_string_value = self::datetimeString( $p_value );

		if( ApiObjectFactory::$soap ) {
			return new \SoapVar($t_string_value, XSD_DATETIME, 'xsd:dateTime');
		}

		return $t_string_value;
	}

	/**
	 * Convert a timestamp to a DateTime string
	 * @param integer $p_timestamp Integer value to format as date time string.
	 * @return string for provided timestamp
	 */
	static function datetimeString( $p_timestamp ) {
		if( $p_timestamp == null || date_is_null( $p_timestamp ) ) {
			return null;
		}

		return date( 'c', (int)$p_timestamp );
	}

	/**
	 * Checks if an object is a SoapFault
	 * @param mixed $p_maybe_fault Object to check whether it is a SOAP/REST fault.
	 * @return boolean
	 */
	static function isFault( $p_maybe_fault ) {
		if( !is_object( $p_maybe_fault ) ) {
			return false;
		}

		if( ApiObjectFactory::$soap && get_class( $p_maybe_fault ) == 'SoapFault') {
			return true;
		}

		if( !ApiObjectFactory::$soap && get_class( $p_maybe_fault ) == 'RestFault') {
			return true;
		}

		return false;
	}

	/**
	 * Throw if the provided parameter is a SoapFault or RestFault/
	 *
	 * @param mixed $p_maybe_fault Object to check whether it is a SOAP/REST fault.
	 * @return void
	 * @throws LegacyApiFaultException
	 */
	static function throwIfFault( $p_maybe_fault ) {
		if( ApiObjectFactory::isFault( $p_maybe_fault ) ) {
			throw new LegacyApiFaultException( $p_maybe_fault->getMessage(), $p_maybe_fault->getCode() );
		}
	}
}
