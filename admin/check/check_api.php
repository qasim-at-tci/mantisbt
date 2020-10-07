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
 * Supporting functions for the Check configuration
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

$g_show_all = false;
$g_show_errors = false;

$g_failed_test = false;
$g_passed_test_with_warnings = false;

$g_errors_temporarily_suppressed = false;
$g_errors_raised = array();

define( 'STRINGS_ENGLISH', 'strings_english.txt' );

/**
 * Initialise error handler for checks
 * @return void
 */
function check_init_error_handler() {
	set_error_handler( 'check_error_handler' );
	error_reporting( E_ALL );
}

/**
 * Implement Error handler for check framework
 * @param integer $p_type    Error type.
 * @param string  $p_error   Error number.
 * @param string  $p_file    File error occurred in.
 * @param integer $p_line    Line number.
 * @param string  $p_context Context.
 * @return void
 */
function check_error_handler( $p_type, $p_error, $p_file, $p_line, $p_context ) {
	global $g_errors_raised;
	$g_errors_raised[] = array(
		'type' => $p_type,
		'error' => $p_error,
		'file' => $p_file,
		'line' => $p_line,
		'context' => $p_context
	);
}

/**
 * Check whether any unhandled errors exist
 * @return boolean|integer false if there are no unhandled errors, or the lowest
 *                  unhandled {@see http://php.net/errorfunc.constants Error Type}
 */
function check_unhandled_errors_exist() {
	global $g_errors_raised;
	if( count( $g_errors_raised ) > 0 ) {
		$t_type = E_ALL;
		foreach( $g_errors_raised as $t_error ) {
			$t_type = min( $t_type, $t_error['type'] );
		}
		return $t_type;
	}
	return false;
}

/**
 * Print out errors raised to html
 * @return void
 */
function check_print_error_rows() {
	global $g_show_errors, $g_errors_temporarily_suppressed, $g_errors_raised;
	if( !$g_show_errors || $g_errors_temporarily_suppressed ) {
		$g_errors_raised = array();
		return;
	}
	foreach( $g_errors_raised as $t_error ) {
		# build an appropriate error string
		switch( $t_error['type'] ) {
			case E_WARNING:
				$t_error_type = 'SYSTEM WARNING';
				$t_error_description = htmlentities( $t_error['error'] );
				break;
			case E_NOTICE:
				$t_error_type = 'SYSTEM NOTICE';
				$t_error_description = htmlentities( $t_error['error'] );
				break;
			case E_DEPRECATED:
				$t_error_type = 'DEPRECATED';
				$t_error_description = htmlentities( $t_error['error'] );
				break;
			case E_USER_ERROR:
				$t_error_type = 'APPLICATION ERROR #' . $t_error['error'];
				$t_error_description = htmlentities( error_string( $t_error['error'] ) );
				break;
			case E_USER_WARNING:
				$t_error_type = 'APPLICATION WARNING #' . $t_error['error'];
				$t_error_description = htmlentities( error_string( $t_error['error'] ) );
				break;
			case E_USER_NOTICE:
				# used for debugging
				$t_error_type = 'DEBUG';
				$t_error_description = htmlentities( $t_error['error'] );
				break;
			default:
				# shouldn't happen, display the error just in case
				$t_error_type = 'UNHANDLED ERROR TYPE ' . $t_error['type'];
				$t_error_description = htmlentities( $t_error['error'] );
		}
		echo "\t<tr>\n\t\t<td colspan=\"2\" class=\"alert alert-danger\">";
		echo '<strong>' . $t_error_type . ':</strong> ' . $t_error_description . '<br />';
		echo '<em>Raised in file ' . htmlentities( $t_error['file'] ) . ' on line ' . htmlentities( $t_error['line'] ) . '</em>';
		echo "</td>\n\t</tr>\n";
	}
	$g_errors_raised = array();
}

/**
 * Print section header
 *
 * @param string $p_heading Heading.
 * @return void
 */
function check_print_section_header_row( $p_heading ) {
?>
	<tr>
		<td colspan="2" class="thead2"><strong><?php echo $p_heading ?></strong></td>
	</tr>
<?php
}

/**
 * Print Check result - information only
 *
 * @param string $p_description Description.
 * @param string $p_info        Information.
 * @return void
 */
function check_print_info_row( $p_description, $p_info = null ) {
	global $g_show_all;
	if( !$g_show_all ) {
		return;
	}
	echo "\t" . '<tr>' . "\n\t\t";
	echo '<td class="description">' . $p_description . '</td>' . "\n";
	echo "\t\t" . '<td class="info">' . $p_info . '</td>' . "\n";
	echo "\t" . '</tr>' . "\n";
}

/**
 * Print Check Test Result
 * @param integer $p_result One of BAD|GOOD|WARN.
 * @return void
 */
function check_print_test_result( $p_result ) {
	global $g_failed_test, $g_passed_test_with_warnings;
	switch( $p_result ) {
		case BAD:
			echo "\t\t" . '<td class="alert alert-danger">FAIL</td>' . "\n";
			$g_failed_test = true;
			break;
		case GOOD:
			echo "\t\t" . '<td class="alert alert-success">PASS</td>' . "\n";
			break;
		case WARN:
			echo "\t\t" . '<td class="alert alert-warning">WARN</td>' . "\n";
			$g_passed_test_with_warnings = true;
			break;
	}
}

/**
 * Print Check Test Row
 * @param string  $p_description Description.
 * @param boolean $p_pass        Whether test passed.
 * @param string  $p_info        Information.
 * @return boolean
 */
function check_print_test_row( $p_description, $p_pass, $p_info = null ) {
	global $g_show_all;
	$t_unhandled = check_unhandled_errors_exist();
	if( !$g_show_all && $p_pass && !$t_unhandled ) {
		return $p_pass;
	}

	echo "\t<tr>\n\t\t<td>$p_description";
	if( $p_info !== null ) {
		if( is_array( $p_info ) && isset( $p_info[$p_pass] ) ) {
			echo '<br /><em>' . $p_info[$p_pass] . '</em>';
		} else if( !is_array( $p_info ) ) {
			echo '<br /><em>' . $p_info . '</em>';
		}
	}
	echo "</td>\n";

	if( $p_pass && !$t_unhandled ) {
		$t_result = GOOD;
	} elseif( $t_unhandled == E_DEPRECATED ) {
		$t_result = WARN;
	} else {
		$t_result = BAD;
	}
	check_print_test_result( $t_result );
	echo "\t</tr>\n";

	if( $t_unhandled ) {
		check_print_error_rows();
	}
	return $p_pass;
}

/**
 * Print Check Test Warning Row
 * @param string  $p_description Description.
 * @param boolean $p_pass        Whether test passed.
 * @param string  $p_info        Information.
 * @return boolean
 */
function check_print_test_warn_row( $p_description, $p_pass, $p_info = null ) {
	global $g_show_all;
	$t_unhandled = check_unhandled_errors_exist();
	if( !$g_show_all && $p_pass && !$t_unhandled ) {
		return $p_pass;
	}
	echo "\t<tr>\n\t\t<td>$p_description";
	if( $p_info !== null ) {
		if( is_array( $p_info ) && isset( $p_info[$p_pass] ) ) {
			echo '<br /><em>' . $p_info[$p_pass] . '</em>';
		} else if( !is_array( $p_info ) ) {
			echo '<br /><em>' . $p_info . '</em>';
		}
	}
	echo "</td>\n";
	if( $p_pass && !$t_unhandled ) {
		$t_result = GOOD;
	} elseif( !$t_unhandled || $t_unhandled == E_DEPRECATED ) {
		$t_result = WARN;
	} else {
		$t_result = BAD;
	}
	check_print_test_result( $t_result );
	echo "\t</tr>\n";

	if( $t_unhandled ) {
		check_print_error_rows();
	}
	return $p_pass;
}

/**
 * Verifies that the given collation is UTF-8
 * @param string $p_collation
 * @return boolean True if UTF-8
 */
function check_is_collation_utf8( $p_collation ) {
	return substr( $p_collation, 0, 4 ) === 'utf8';
}

/**
 * Formats a number with thousand separators and an optional unit
 * @param float  $p_number Number to print
 * @param string $p_unit   Printed after number
 * @return string
 */
function check_format_number( $p_number, $p_unit = 'bytes' ) {
	return number_format( (float)$p_number ) . ' ' . $p_unit;
}

/**
 * Check language files directory.
 *
 * This will report Warnings rather than Errors, as in most cases language file
 * problems are not blocking; severe issues are likely to break things before
 * we even get here.
 *
 * @param string $p_path        Path where to look for language files,
 *                              including 'lang' and trailing '/'.
 * @param string $p_description What we're testing, for check messages display.
 */
function check_lang_dir( $p_path, $p_description ) {
	$t_text = sprintf( 'Checking %s language files', $p_description );

	# Some plugins may not have any language strings, so the absence of the
	# 'lang' directory should not be reported as an error
	if( !is_dir( $p_path ) ) {
		check_print_info_row(
			$t_text,
			"'lang' directory not found"
		);
		return;
	}

	# Suppress the Checks error handler
	set_error_handler(null);
	$t_lang_files = @scandir( $p_path );
	restore_error_handler();
	if( false === $t_lang_files ) {
		check_print_test_row(
			$t_text,
			false,
			"language dir '$p_path' is not accessible"
		);
		return;
	}


	# Exclude README and hidden files, put English first if present
	$t_has_english_strings = in_array( STRINGS_ENGLISH, $t_lang_files );
	$t_lang_files = array_filter( $t_lang_files,
		function( $p_value ) {
			return $p_value[0] != '.'
				&& $p_value != STRINGS_ENGLISH
				&& $p_value != 'README'
				&& $p_value != 'Web.config';
		}
	);
	if( $t_has_english_strings ) {
		array_unshift( $t_lang_files, STRINGS_ENGLISH );
	}

	# Display the number of language files found
	check_print_info_row( $t_text, count( $t_lang_files ) . ' files' );

	foreach( $t_lang_files as $t_file ) {
		$t_result = check_lang_file( $p_path, $t_file );
		check_print_test_warn_row(
			sprintf( "Checking %s '%s' language file", $p_description, $t_file ),
			empty( $t_result ),
			implode( '<br>', $t_result )
		);
		flush();
	}
}

/**
 * Check Language File
 *
 * @param string  $p_path  Path.
 * @param string  $p_file  File.
 *
 * @return array List of error/warning messages
 */
function check_lang_file( $p_path, $p_file ) {
	$t_file = $p_path . $p_file;

	$t_result = check_lang_file_token( $t_file, ($p_file == STRINGS_ENGLISH ) );
	if( $t_result ) {
		return $t_result;
	}

	ob_start();
	$t_result = eval( "require_once( '$t_file' );" );
	$t_data = ob_get_contents();
	ob_end_clean();

	if( $t_result === false ) {
		return array( "Language file '$p_file' failed at eval" ); // 'FAILED';
	}

	if( !empty( $t_data ) ) {
		return array( "Language file '$p_file' failed at require_once (data output: "
			. var_export( $t_data, true ) . ')' ); // 'FAILED'
	}
	return array();
}

/**
 * Check Language File Tokens
 *
 * @param string  $p_file Language file to tokenize.
 * @param boolean $p_base Whether language file is default (aka english).
 *
 * @return array List of error/warning messages
 */
function check_lang_file_token( $p_file, $p_base = false ) {
	$t_variables = array();
	static $s_base_variables;
	$t_current_var = null;
	$t_line = 1;
	$t_last_token = 0;
	$t_set_variable = false;
	$t_variable_array = false;
	$t_two_part_string = false;
	$t_need_end_variable = false;
	$t_expect_end_array = false;
	$t_setting_variable = false;
	$t_errors = array();
	$t_fatal = false;

	# Suppress the Checks error handler
	set_error_handler(null);
	$t_source = @file_get_contents( $p_file );
	restore_error_handler();
	if( $t_source === false ) {
		return array( "Could not read '$p_file'" );
	}
	try {
		$t_tokens = token_get_all( $t_source, TOKEN_PARSE );
	}
	catch( ParseError $e ) {
		return array( $e->getMessage() );
	}

	foreach( $t_tokens as $t_token ) {
		$t_last_token2 = 0;
		if( is_string( $t_token ) ) {
			switch( $t_token ) {
				case '=':
					if( $t_last_token != T_VARIABLE ) {
						$t_errors[] = "'=' sign without variable (line $t_line)";
					}
					$t_set_variable = true;
					break;
				case '[':
					if( $t_last_token != T_VARIABLE ) {
						$t_errors[] = "unexpected opening square bracket '[' (line $t_line)";
					}
					$t_variable_array = true;
					break;
				case ']':
					if( !$t_expect_end_array ) {
						$t_errors[] = "unexpected closing square bracket ']' (line $t_line)";
					}
					$t_expect_end_array = false;
					$t_variable_array = false;
					break;
				case ';':
					if( !$t_need_end_variable ) {
						$t_errors[] = "function separator found at unexpected location (line $t_line)";
					}
					$t_need_end_variable = false;
					break;
				case '.':
					if( $t_last_token == T_CONSTANT_ENCAPSED_STRING ) {
						$t_two_part_string = true;
					} else {
						$t_errors[] = "string concatenation found at unexpected location (line $t_line)";
					}
					break;
				default:
					$t_errors[] = "unknown token '$t_token' (line $t_line)";
					break;
			}
		} else {
			# token array
			list( $t_id, $t_text, $t_line ) = $t_token;

			if( $t_id == T_WHITESPACE || $t_id == T_COMMENT || $t_id == T_DOC_COMMENT ) {
				continue;
			}
			if( $t_need_end_variable ) {
				if( $t_two_part_string && $t_id == T_CONSTANT_ENCAPSED_STRING ) {
					$t_two_part_string = false;
					continue;
				}
				if( $t_setting_variable && $t_id == T_STRING ) {
					$t_last_token = T_VARIABLE;
					$t_expect_end_array = true;
					continue;
				}

				$t_errors[] = "token# $t_id: " . token_name( $t_id ) . " = $t_text (line $t_line)";
			}

			switch( $t_id ) {
				case T_OPEN_TAG:
				case T_CLOSE_TAG:
					break;
				case T_INLINE_HTML:
					$t_errors[] = "Whitespace in language file outside of PHP code block (line $t_line)";
					break;
				case T_VARIABLE:
					if( $t_set_variable && $t_current_var != null ) {
						$t_need_end_variable = true;
						$t_setting_variable = true;
						$t_current_var = null;
						break;
					}
					$t_current_var = $t_text;
					break;
				case T_STRING:
					if( $t_variable_array ) {
						$t_current_var .= $t_text;
						if( !defined( $t_text ) ) {
							$t_errors[] = "undefined constant: $t_text (line $t_line)";
						}
					} else {
						$t_errors[] = "unexpected T_STRING (line $t_line)";
					}
					if( strpos( $t_current_var, "\n" ) !== false ) {
						$t_errors[] = "NEW LINE in string: $t_id " . token_name( $t_id ) . " = $t_text (line $t_line)"; # PARSER;
						$t_fatal = true;
					}
					$t_last_token2 = T_VARIABLE;
					$t_expect_end_array = true;
					break;
				case T_CONSTANT_ENCAPSED_STRING:
					if( $t_token[1][0] != "'" ) {
						$t_errors[] = "Language strings should be single-quoted (line $t_line)";
					}
					if( $t_variable_array ) {
						$t_current_var .= $t_text;
						$t_last_token2 = T_VARIABLE;
						$t_expect_end_array = true;
						break;
					}

					if( $t_last_token == T_VARIABLE && $t_set_variable && $t_current_var != null ) {
						if( isset( $t_variables[$t_current_var] ) ) {
							$t_errors[] = "duplicate language string '$t_current_var' (line $t_line)";
						} else {
							$t_variables[$t_current_var] = $t_text;
						}

						if( $p_base ) {
							# english
							#if( isset( $s_base_variables[$t_current_var] ) ) {
							#	print_error( "WARN: english string redefined - plugin? $t_current_var" );
							#}
							$s_base_variables[$t_current_var] = true;
						} else {
							if( !isset( $s_base_variables[$t_current_var] ) ) {
								$t_errors[] = "'$t_current_var' is not defined in the English language file"; # 'WARNING'
								#} else {
								#  missing translation
							}
						}

					}
					if( strpos( $t_current_var, "\n" ) !== false ) {
						$t_errors[] = "NEW LINE in string: $t_id " . token_name( $t_id ) . " = $t_text (line $t_line)"; # PARSER;
						$t_fatal = true;
					}
					$t_current_var = null;
					$t_need_end_variable = true;
					break;
				default:
					$t_errors[] = $t_id . ' ' . token_name( $t_id ) . " = $t_text (line $t_line)"; # PARSER;
					break;
			}

			$t_last_token = $t_id;
			if( $t_last_token2 > 0 ) {
				$t_last_token = $t_last_token2;
			}
		}

		# Stop processing the file if a fatal error was found
		if( $t_fatal ) {
			break;
		}
	}

	return $t_errors;
}
