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
 * Edit Graph Plugin Configuration
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

html_page_top( plugin_lang_get( 'title' ) );

print_manage_menu( );

$g_current_font_selected = array(
	'arial' => false,
	'verdana' => false,
	'trebuchet' => false,
	'verasans' => false,
	'dejavusans' => false,
	'times' => false,
	'georgia' => false,
	'veraserif' => false,
	'dejavuserif' => false,
	'courier' => false,
	'veramono' => false,
	'dejavumono' => false,
);

$t_current_font = plugin_config_get( 'font' );
if( isset( $g_current_font_selected[$t_current_font] ) ) {
	$g_current_font_selected[$t_current_font] = true;
} else {
	$g_current_font_selected['arial'] = true;
}

/**
 * Prints checked="checked" to the end of a HTML <option> tag if the supplied
 * font name matches the current font configuration value.
 * @param string $p_font_name The name of the font to check.
 * @return string Either checked="checked" for a match or otherwise an empty string
 */
function print_font_checked( $p_font_name ) {
	global $g_current_font_selected;

	if( isset( $g_current_font_selected[$p_font_name] ) ) {
		if( $g_current_font_selected[$p_font_name] ) {
			return ' checked="checked"';
		}
	}

	return '';
}

?>

<<<<<<< HEAD
<div id="graph-config-div" class="form-container">
	<form id="graph-config-form" action="<?php echo plugin_page( 'config_edit' )?>" method="post">
		<fieldset>
			<legend><span><?php echo plugin_lang_get( 'title' ) . ': ' . plugin_lang_get( 'config' )?></span></legend>
			<?php echo form_security_field( 'plugin_graph_config_edit' ) ?>

			<div class="field-container">
				<label><span><?php echo plugin_lang_get( 'library' )?></span></label>
				<span class="radio">
					<input type="radio" id="ecz-library" name="eczlibrary" value="1" <?php echo( ON == plugin_config_get( 'eczlibrary' ) ) ? 'checked="checked" ' : ''?>/>
					<label for="ecz-library"><?php echo plugin_lang_get( 'bundled' )?></label>
					<input type="radio" id="jpgraph-library" name="eczlibrary" value="0" <?php echo( OFF == plugin_config_get( 'eczlibrary' ) ) ? 'checked="checked" ' : ''?>/>
					<label for="jpgraph-library">JpGraph</label>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label><span><?php echo plugin_lang_get( 'window_width' )?></span></label>
				<span class="input">
					<input type="text" name="window_width" value="<?php echo plugin_config_get( 'window_width' )?>" />
				</span>
				<span class="label-style"></span>
			</div>

			<div class="field-container">
				<label><span><?php echo plugin_lang_get( 'bar_aspect' )?></span></label>
				<span class="input">
					<input type="text" name="bar_aspect" value="<?php echo plugin_config_get( 'bar_aspect' )?>" />
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label><span><?php echo plugin_lang_get( 'summary_graphs_per_row' )?></span></label>
				<span class="input">
					<input type="text" name="summary_graphs_per_row" value="<?php echo plugin_config_get( 'summary_graphs_per_row' )?>" />
				</span>
				<span class="label-style"></span>
			</div>

			<div class="field-container">
				<label><span><?php echo plugin_lang_get( 'font' )?></span></label>
				<span class="radio">
					Sans-serif:<br />
					<label><input type="radio" name="font" value="arial"<?php echo print_font_checked( 'arial' )?>/>Arial</label><br />
					<label><input type="radio" name="font" value="verdana"<?php echo print_font_checked( 'verdana' )?>/>Verdana</label><br />
					<label><input type="radio" name="font" value="trebuchet"<?php echo print_font_checked( 'trebuchet' )?>/>Trebuchet</label><br />
					<label><input type="radio" name="font" value="verasans"<?php echo print_font_checked( 'verasans' )?>/>Vera Sans</label>
					<label><input type="radio" name="font" value="dejavusans"<?php echo print_font_checked( 'dejavusans' )?>/>DejaVu Sans</label>
					Serif:<br />
					<label><input type="radio" name="font" value="times"<?php echo print_font_checked( 'times' )?>/>Times</label><br />
					<label><input type="radio" name="font" value="georgia"<?php echo print_font_checked( 'georgia' )?>/>Georgia</label><br />
					<label><input type="radio" name="font" value="veraserif"<?php echo print_font_checked( 'veraserif' )?>/>Vera Serif</label><br />
					<label><input type="radio" name="font" value="dejavusans"<?php echo print_font_checked( 'dejavusans' )?>/>DejaVu Sans</label>
					<br />Monospace:<br />
					<label><input type="radio" name="font" value="courier"<?php echo print_font_checked( 'courier' )?>/>Courier</label><br />
					<label><input type="radio" name="font" value="veramono"<?php echo print_font_checked( 'veramono' )?>/>Vera Mono</label>
					<label><input type="radio" name="font" value="dejavumono"<?php echo print_font_checked( 'dejavumono' )?>/>DejaVu Mono</label>
				</span>
				<span class="label-style"></span>
			</div>

			<?php if( current_user_is_administrator() ) {?>
				<div class="field-container">
					<label><span><?php echo plugin_lang_get( 'jpgraph_path' )?>
					<br /><span class="small"><?php echo plugin_lang_get( 'jpgraph_path_default' )?></span>
					</span></label>
					<span class="input">
						<input type="text" name="jpgraph_path" value="<?php echo plugin_config_get( 'jpgraph_path' )?>" />
					</span>
					<span class="label-style"></span>
				</div>
			<?php } ?>

			<div class="field-container">
				<label><span><?php echo plugin_lang_get( 'jpgraph_antialias' )?>
				<br /><span class="small"><?php echo plugin_lang_get( 'jpgraph_antialias_info' )?></span>
				</span></label>
				<span class="radio">
					<label><input type="radio" name="jpgraph_antialias" value="1" <?php echo( ON == plugin_config_get( 'jpgraph_antialias' ) ) ? 'checked="checked" ' : ''?>/><?php echo plugin_lang_get( 'enabled' )?></label>
					<label><input type="radio" name="jpgraph_antialias" value="0" <?php echo( OFF == plugin_config_get( 'jpgraph_antialias' ) ) ? 'checked="checked" ' : ''?>/><?php echo plugin_lang_get( 'disabled' )?></label>
				</span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'change_configuration' )?>" /></span>
		</fieldset>
	</form>
</div>
=======
<div id="graph-config" class="form-container">

<form action="<?php echo plugin_page( 'config_edit' )?>" method="post">
<fieldset>
	<legend>
		<span><?php
			echo plugin_lang_get( 'title' ) . ': ' . plugin_lang_get( 'config' );
		?></span>
	</legend>

	<?php echo form_security_field( 'plugin_graph_config_edit' ) ?>

	<!-- Graph Library -->
	<fieldset class="field-container">
		<label><span><?php echo plugin_lang_get( 'library' )?></span></label>

		<span class="radio">
			<input id="library-ecz" type="radio" name="eczlibrary" value="1" <?php
				check_checked( ON, plugin_config_get( 'eczlibrary' ) ); ?>/>
			<label for="library-ecz">
				<span><?php echo plugin_lang_get('bundled'); ?></span>
			</label>
			<input id="library-jpgraph" type="radio" name="eczlibrary" value="0" <?php
				check_checked( OFF, plugin_config_get( 'eczlibrary' ) ); ?>/>
			<label for="library-jpgraph">
				<span>JpGraph</span>
			</label>
		</span>

		<span class="label-style"></span>
	</fieldset>

	<div class="spacer"></div>

	<!-- Graph width -->
	<div class="field-container">
		<label for="window_width"><span><?php echo plugin_lang_get( 'window_width' ); ?></span></label>
		<span class="input">
			<input id="window_width" type="text" name="window_width" value="<?php
				echo plugin_config_get( 'window_width' ); ?>" />
		</span>
		<span class="label-style"></span>
	</div>

	<!-- Aspect ratio -->
	<div class="field-container">
		<label for="bar_aspect"><span><?php echo plugin_lang_get( 'bar_aspect' ); ?></span></label>
		<span class="input">
			<input id="bar_aspect" type="text" name="bar_aspect" value="<?php
				echo plugin_config_get( 'bar_aspect' ); ?>" />
		</span>
		<span class="label-style"></span>
	</div>

	<!-- Graphs per row -->
	<div class="field-container">
		<label for="summary_graphs_per_row"><span><?php echo plugin_lang_get( 'summary_graphs_per_row' ); ?></span></label>
		<span class="input">
			<input id="summary_graphs_per_row" type="text" name="summary_graphs_per_row" value="<?php
				echo plugin_config_get( 'summary_graphs_per_row' ); ?>" />
		</span>
		<span class="label-style"></span>
	</div>

	<!-- Font -->
	<fieldset class="field-container">
		<label><span><?php echo plugin_lang_get( 'font' )?></span></label>

		<span class="radio">
			Sans-serif:<br />
			<input id="font-arial" type="radio" name="font" value="arial"<?php echo print_font_checked( 'arial' )?>/>
			<label for="font-arial">Arial</label>
			<br />
			<input id="font-verdana" type="radio" name="font" value="verdana"<?php echo print_font_checked( 'verdana' )?>/>
			<label for="font-verdana">Verdana</label>
			<br />
			<input id="font-trebuchet" type="radio" name="font" value="trebuchet"<?php echo print_font_checked( 'trebuchet' )?>/>
			<label for="font-trebuchet">Trebuchet</label>
			<br />
			<input id="font-verasans" type="radio" name="font" value="verasans"<?php echo print_font_checked( 'verasans' )?>/>
			<label for="font-verasans">Vera Sans</label>
			<br />
			<input id="font-dejavusans" type="radio" name="font" value="dejavusans"<?php echo print_font_checked( 'dejavusans' )?>/>
			<label for="font-dejavusans">DejaVu Sans</label>
		</span>
		<span class="radio">
			Serif:<br />
			<input id="font-times" type="radio" name="font" value="times"<?php echo print_font_checked( 'times' )?>/>
			<label for="font-times">Times</label>
			<br />
			<input id="font-georgia" type="radio" name="font" value="georgia"<?php echo print_font_checked( 'georgia' )?>/>
			<label for="font-georgia">Georgia</label>
			<br />
			<input id="font-veraserif" type="radio" name="font" value="veraserif"<?php echo print_font_checked( 'veraserif' )?>/>
			<label for="font-veraserif">Vera Serif</label>
			<br />
			<input id="font-dejavuserif" type="radio" name="font" value="dejavuserif"<?php echo print_font_checked( 'dejavuserif' )?>/>
			<label for="font-dejavuserif">DejaVu Serif</label>
		</span>
		<span class="radio">
			Monospace:<br />
			<input id="font-courier" type="radio" name="font" value="courier"<?php echo print_font_checked( 'courier' )?>/>
			<label for="font-courier">Courier</label>
			<br />
			<input id="font-veramono" type="radio" name="font" value="veramono"<?php echo print_font_checked( 'veramono' )?>/>
			<label for="font-veramono">Vera Mono</label>
			<br />
			<input id="font-dejavumono" type="radio" name="font" value="dejavumono"<?php echo print_font_checked( 'dejavumono' )?>/>
			<label for="font-dejavumono">DejaVu Mono</label>
		</span>

		<span class="label-style"></span>
	</fieldset>

	<div class="spacer"></div>

	<!-- JpGraph Path -->
	<?php if ( current_user_is_administrator() ) { ?>
	<div class="field-container">
		<label for="jpgraph_path">
			<span><?php echo plugin_lang_get( 'jpgraph_path' ); ?></span>
			<br />
			<span class="small"><?php echo plugin_lang_get( 'jpgraph_path_default' )?></span>
		</label>
		<span class="input">
			<input id="jpgraph_path" type="text" size="50" name="jpgraph_path" value="<?php
				echo plugin_config_get( 'jpgraph_path' ); ?>" />
		</span>
		<span class="label-style"></span>
	</div>
	<?php } ?>

	<!-- JpGraph anti-aliasing -->
	<fieldset class="field-container">
		<label>
			<span><?php echo plugin_lang_get( 'jpgraph_antialias' )?></span>
			<br />
			<span class="small"><?php echo plugin_lang_get( 'jpgraph_antialias_info' ); ?>
		</label>

		<span class="radio">
			<input id="antialias-enabled" type="radio" name="jpgraph_antialias" value="1" <?php
				check_checked( ON, plugin_config_get( 'jpgraph_antialias' ) ); ?>/>
			<label for="antialias-enabled">
				<span><?php echo plugin_lang_get('enabled'); ?></span>
			</label>
			<input id="antialias-disabled" type="radio" name="jpgraph_antialias" value="0" <?php
				check_checked( OFF, plugin_config_get( 'jpgraph_antialias' ) ); ?>/>
			<label for="antialias-disabled">
				<span><?php echo plugin_lang_get('disabled'); ?></span>
			</label>
		</span>

		<span class="label-style"></span>
	</fieldset>

	<span class="submit-button">
		<input type="submit" class="button" value="<?php echo lang_get( 'change_configuration' )?>" />
	</span>
</fieldset>
</form>

</div>

<?php
html_page_bottom();
