<?php
# MantisBT - a php based bugtracking system

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
 * This upgrade moves attachments from the database to the disk
 *
 * @package MantisBT
 * @copyright Copyright (c) 2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */


/**
 * MantisBT Core API's
 */
require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

# Page header, menu
html_page_top( 'MantisBT Administration - Cleanup Attachments' );
echo '<div align="center"><p>';
print_bracket_link( helper_mantis_url( 'admin/system_utils.php' ), 'Back to System Utilities' );
echo '</p></div>';

$t_types = array( 'bug', 'project');

$t_count_global = 0;
foreach( $t_types as $t_type ) {
	$t_file_table = db_get_table( "mantis_${t_type}_file_table" );

	# Determine the number of invalid attachments
	$t_query = "SELECT count(*) FROM $t_file_table WHERE content = '\'\''";
	$t_result = db_query_bound( $t_query );
	$t_result = db_fetch_array( $t_result );

	$t_count_global += $t_tables[$t_file_table] = reset( $t_result );
}
?>

<div align="center">

<?php

if( $t_count_global == 0 ) {
	# Nothing to do
	echo '<p>No invalid attachments were found.</p>';
} else {
?>

<table class="width50">
	<tr>
		<td class="form-title" colspan="2">
			Attachments to clean up
		</td>
	</tr>

	<tr class="row-category">
		<th>Table</th>
		<th width="10%">Count</th>
	</tr>

<?php
	# Printing rows of projects with attachments to move
	foreach( $t_tables as $t_table => $t_count ) {
		echo '<tr ' . helper_alternate_class() . '>';
		printf(
			'<td>%s</td><td class="center">%s</td>',
			$t_table,
			$t_count
		);
		echo "</tr>\n";
	}
?>

</table>
<br />

<form name="cleanup_attachments" method="post" action="cleanup_attachments.php">
	<?php echo form_security_field( 'cleanup_attachments' ); ?>
	<input type="submit" class="button" value="Cleanup Attachments" />
</form>

<?php
}
?>

</div>

<?php
html_page_bottom();
