#!/usr/bin/php -q
<?php
# MantisBT - A PHP based bugtracking system
# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2013  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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
# See the README and LICENSE files for details

global $g_bypass_headers;
$g_bypass_headers = 1;

require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

require_once 'Console/GetoptPlus.php';
$config = array(
	'options' => array(
		array(
			'long' => 'foo',
			'type' => 'noarg',
			'desc' => array(
				'An option without argument with only the long',
				'name defined.'
			)
		),
		array(
			'long' => 'bar',
			'type' => 'mandatory',
			'short' => 'b',
			'desc' => array(
				'arg',
				'A mandatory option with both the long and',
				'the short names defined.'
			)
		),
	)
);
$options = Console_Getoptplus::getoptplus($config);
var_dump($config);
die;

# -----------------------------------------------------------------------------
# Helper functions
#

function log_msg( $p_msg ) {
	echo "$p_msg\n";
}

function log_result( $p_id, $p_msg ) {
	log_msg ( "	@$p_id: $p_msg" );
}

function error_msg( $p_msg ) {
	log_msg( "ERROR: $p_msg" );
	exit( 1 );
}

function error_no( $p_errno ) {
	$msg = error_string( ERROR_LDAP_AUTH_FAILED );
	error_msg( $msg );
}


# -----------------------------------------------------------------------------
# Initialization and preliminary error checking
#

# Make sure this script doesn't run via the webserver
if( is_cli() ) {
	error_msg( "This script is not allowed to run through the webserver." );
}

if( LDAP != config_get( 'login_method' ) ) {
	error_msg( "This script requires login_method to be LDAP." );
}

if( ldap_simulation_is_enabled() ) {
	error_msg( "LDAP simulation is not supported." );
}


$t_use_ldap_realname   = config_get( 'use_ldap_realname' );
$t_use_ldap_email      = config_get( 'use_ldap_email' );

$t_ldap_organization   = config_get( 'ldap_organization' );
$t_ldap_root_dn        = config_get( 'ldap_root_dn' );
$t_ldap_uid_field      = config_get( 'ldap_uid_field', 'uid' );

$t_fields = array();
if( ON == $t_use_ldap_realname ) {
	$t_ldap_realname_field = config_get( 'ldap_realname_field' );
	$t_fields[] = array(
		'ldap' => $t_ldap_realname_field,
		'user' => 'realname'
	);
}

if( ON == $t_use_ldap_email ) {
	$t_fields[] = array(
		'ldap' => 'mail',
		'user' => 'email'
	);
}

# Define search attributes and SQL columns
$t_search_attrs = array( $t_ldap_uid_field, 'dn' );

$t_sql_columns = 'id, username';
foreach( $t_fields as $t_field ) {
	$t_search_attrs[] = $t_field['ldap'];
	$t_sql_columns .= ', ' . $t_field['user'];
}


# -----------------------------------------------------------------------------
# Error checking before we start
#

if( OFF == $t_use_ldap_realname && OFF == $t_use_ldap_email ) {
	error_msg( "System not configured to use any LDAP fields, nothing to do.");
}


# -----------------------------------------------------------------------------
# Main
#

# Bind LDAP
log_msg( "Connecting to LDAP server" );
$t_ds = @ldap_connect_bind();
if ( $t_ds === false ) {
	error_no( ERROR_LDAP_AUTH_FAILED );
}

# Get active users list

$t_user_table = db_get_table( 'mantis_user_table' );

$t_query = "SELECT $t_sql_columns"
	. " FROM $t_user_table"
	. " WHERE enabled = " . db_param() . " AND protected <> " . db_param()
	. " ORDER BY last_visit";
$t_param = array( true, true );
$t_result = db_query_bound( $t_query, $t_param );


# Check all users against LDAP
log_msg( "Processing " . db_num_rows( $t_result ) . " users..." );
$t_to_update = array();

while( $t_user = db_fetch_array( $t_result ) ) {
	extract( $t_user, EXTR_PREFIX_ALL, 't' );

	log_msg( "Searching: $t_username (@$t_id)" );

	# Search user in LDAP
	$t_search_filter = "(&$t_ldap_organization($t_ldap_uid_field=$t_username))";
	$t_sr = ldap_search( $t_ds, $t_ldap_root_dn, $t_search_filter, $t_search_attrs );
	if( $t_sr === false ) {
		ldap_log_error( $t_ds );
		ldap_unbind( $t_ds );
		error_msg( "ldap search failed" );
	}

	$t_info = @ldap_get_entries( $t_ds, $t_sr );
	if ( $t_info === false ) {
		ldap_log_error( $t_ds );
		ldap_unbind( $t_ds );
		error_msg( "ldap_get_entries() returned false." );
	}
	ldap_free_result( $t_sr );

	if ( $t_info['count'] == 0 ) {
		log_result( $t_id, "No matching entries found in LDAP" );
		continue;
	} else if ( $t_info['count'] > 1 ) {
		log_result( $t_id, "WARNING: multiple LDAP entries found; arbitrarily picking the first one" );
	}

	$t_info = $t_info[0];
	extract( $t_info, EXTR_PREFIX_ALL, 't_ldap' );;
	log_result( $t_id, "found $t_ldap_dn" );

	# Make sure the requested fields exist
	$t_fields_to_update = false;
	foreach( $t_fields as $t_key => $t_field ) {
		extract($t_field, EXTR_PREFIX_ALL, 'k' );

		if( !array_key_exists( $k_ldap, $t_info ) ) {
			log_result( $t_id, "WARNING: field '$k_ldap' not found in LDAP" );
			continue;
		}

		# Compare LDAP vs Mantis user field
		$t_field_user = $t_user[$k_user];
		$t_field_ldap = $t_info[$k_ldap][0];
		if( $t_field_user != $t_field_ldap ) {
			log_result( $t_id, "update needed for '$k_user': '$t_field_user' => '$t_field_ldap'" );
			$t_fields_to_update[$k_user] = $t_field_ldap;
		}
	}

	# Updating the user record if there were differences
	if( $t_fields_to_update !== false ) {
		log_result( $t_id, "	updating " . count($t_fields_to_update) . " fields for user @$t_id" );
		echo user_set_fields( $t_id, $t_fields_to_update );
	}

} # end while

log_msg( "Unbinding from LDAP server" );
ldap_unbind( $t_ds );

log_msg( "Done" );
exit( 0 );
