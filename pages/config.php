<?php
# Mantis Plugin "EasyEnter"
# Copyright (C) 2015 Frithjof Gnas - fg@
#
# Disclaimer & License:
# This plugin - EasyEnter - is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

html_page_top( plugin_lang_get( 'title' ) );

print_manage_menu( );

# Default value, overwritten by GET-Value or previous set session
if( !isset( $_SESSION[ 'selected_project_id' ] ) ) {
	$_SESSION[ 'selected_project_id' ] = 0;
}
if( isset( $_GET['project_id'] ) ) {
	$_SESSION[ 'selected_project_id' ] = (int) $_GET['project_id'];
}


# For include/exclude select-field-list
$list_fieldnames = array(
	'category_id', 'reproducibility', 'severity', 'priority', 'profile_id',
	'platform', 'os', 'os_build', 'handler_id', 'summary', 'description',
	'steps_to_reproduce', 'additional_info', 'ufile[]', 'view_state',
	'report_stay'
);

# For Noscript-warning
$status_colors = config_get('status_colors');
$warn_color    = $status_colors['new'];




/**
 * Build Multiselect with all available bug-report fields and the custom fields
 * that are assigned to current project.
 * To mark the saved options an array with the names of the selected fields has
 * to be passed.
 * @param string $name           Name for the select element
 * @param array $selected_fields
 * @return void                  echos string
 */
function print_select_available_fields( $name, $selected_fields ) {
	global $list_fieldnames;

	#Build dropdown with all available fields for in-/excluding
	$html = '
	<select name="' . $name . '[]" size="11" multiple="multiple" style="width:99%">
		<optgroup label="' .  plugin_lang_get( 'config_fields_default_fields' ) .'">
	';

	for( $i = 0; $i < count( $list_fieldnames ); $i++) {
		$selected = '';
		if( in_array( $list_fieldnames[$i], $selected_fields ) ) {
			$selected = ' selected="selected" ';
		}
		if( lang_exists( $list_fieldnames[$i], lang_get_current() ) ) {
			$html .= "\n\t\t\t" . '<option value="' . $list_fieldnames[$i] . '" '
				. $selected . '>' . lang_get($list_fieldnames[$i]) . '</option>';
		} elseif( lang_exists( str_replace( '_id', '', $list_fieldnames[$i]), lang_get_current() ) ) {
			$html .= "\n\t\t\t" . '<option value="' . $list_fieldnames[$i] . '" '
				. $selected . '>' . lang_get(str_replace( '_id', '', $list_fieldnames[$i])) . '</option>';
		} elseif( lang_exists( $list_fieldnames[$i] . 'rmation', lang_get_current() ) ) {
			#field additional information got the field name additional_info,
			# not matching the approbiate lang-string :-(
			$html .= "\n\t\t\t" . '<option value="' . $list_fieldnames[$i] . '" '
				. $selected . '>' . lang_get( $list_fieldnames[$i] . 'rmation' ) . '</option>';
		}
	}
	$html .= "\n\t\t</optgroup>\n\n";


	#Add custom fields (if specific project is selected only the fields
	# assigned to it)
	$current_project_id = $_SESSION[ 'selected_project_id' ];
	$t_custom_fields = custom_field_get_ids();
	$customfields_options = [];
	foreach( $t_custom_fields as $t_field_id ) {

		$selected = '';
		$field_name = 'custom_field_' . $t_field_id;
		if( in_array( $field_name, $selected_fields ) ) {
			$selected = ' selected="selected" ';
		}

		if( $current_project_id == 0 || in_array($current_project_id, custom_field_get_project_ids( $t_field_id ) ) ) {
			$t_desc = custom_field_get_definition( $t_field_id );

			$customfields_options[] = '<option value="' . $field_name . '" ' . $selected . '>'
				. custom_field_get_display_name( $t_desc['name'] )
				. '</option>';
		}
	}

	#Only add optgroup "custom fields" if there are any of them
	if( count($customfields_options) > 0) {
		$html .= '
			<optgroup label="' . plugin_lang_get('config_fields_custom_fields') . '">
				' . implode( "\n\t\t\t\t", $customfields_options ) . '
			</optgroup>
		';
	}

	echo $html . '</select>';
}


/**
 * Wrapper for plugin_config_get(), just adds parameter "project_id" from
 * Session to origin function call
 * @param string $p_option
 * @return mixed
 */
function plugin_config_get_wpid( $p_option ) {
	$p_project = null;
	if( isset( $_SESSION[ 'selected_project_id' ] )
		&& $_SESSION[ 'selected_project_id' ] != 0 ) {
		$p_project = (int) $_SESSION[ 'selected_project_id' ];
	}
	return plugin_config_get( $p_option, null, null, null, $p_project );
}

/**
 * Checks if $array has $key than return the value otherwise return $default
 * @param string $key
 * @param array $array
 * @param mixed $default (optional) value to set if $key is not set
 * @return mixed
 */
function issetOrDefault( $key, $array, $default = null ) {
	if( isset( $array[$key] ) ) {
		return $array[$key];
	}
	return $default;
}




?>
<!-- NOSCRIPT-Hinweis -->
<noscript><br>
<table class="width100" cellspacing="1"><tbody><tr><td
	style="background-color:<?php echo $warn_color ?>; text-align:center">
	<strong><?php echo plugin_lang_get( 'noscriptwarning' ) ?></strong>
</td></tr></tbody></table>
<br></noscript>
<!-- ENDE // NOSCRIPT-Hinweis -->


<br />
<form action="<?php echo plugin_page( 'config_edit' )?>" method="post">
<?php echo form_security_field( 'plugin_easyenter_config_edit' ) ?>
<table align="center" class="width50" cellspacing="1">

<tr>
	<td class="form-title" colspan="2">
		<?php echo plugin_lang_get( 'title' ) . ': ' . plugin_lang_get( 'config' )?>
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category" width="60%">
		<?php echo plugin_lang_get( 'config_project' )?>
		<br /><span class="small"><?php echo plugin_lang_get( 'config_project_helptxt' )?></span>
	</td>
	<td class="center" width="40%">
		<select name="project_id" id="project_id" style="width:99%">
			<?php print_project_option_list( $_SESSION[ 'selected_project_id' ] ); ?>
		</select>
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo plugin_lang_get( 'config_include_fields' )?>
	</td>
	<td class="center">
		<?php print_select_available_fields(
			'include_fields', plugin_config_get_wpid( 'include_fields' )
		); ?>
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo plugin_lang_get( 'config_exclude_fields' )?>
	</td>
	<td class="center">
		<?php print_select_available_fields(
			'exclude_fields', plugin_config_get_wpid( 'exclude_fields' )
		); ?>
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo plugin_lang_get( 'config_exclude_specialfields' )?>
	</td>
	<td class="center exclude_special_fields">
		<div style="width:98%;margin:0 auto;text-align:left;float:none">
			<label><input type="checkbox" name="exclude_fields[]"
				<?php if( in_array( 'special.custom_profile', plugin_config_get_wpid( 'exclude_fields' ) ) ) {
					echo 'checked="checked"';
				} ?>
				style="margin-bottom:2.1ex;float:left"
				value="special.custom_profile"><?php echo plugin_lang_get( 'config_exclude_special_profileinput' ) ?></label>
		</div>
		<div style="width:98%;margin:0 auto;text-align:left;float:none">
			<label><input type="checkbox" name="exclude_fields[]"
				<?php if( in_array( 'special.mandatory_asterisks', plugin_config_get_wpid( 'exclude_fields' ) ) ) {
					echo 'checked="checked"';
				}
				?>
				style="margin-bottom:2.1ex;float:left"
				value="special.mandatory_asterisks"><?php echo plugin_lang_get( 'config_exclude_special_asterisks' ) ?></label>
		</div>
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo plugin_lang_get( 'config_max_access_level' )?>
	</td>
	<td class="center">
		<?php # Get available groups with naming via translation string (123:Grpname,456:Grp2,...)
		$access_levels_string = lang_get( 'access_levels_enum_string' );
		$access_levels = explode( ',', $access_levels_string );
		$selected_max_level = plugin_config_get_wpid( 'max_access_level' );
		?>
		<select name="max_access_level" size="1" id="sel__maxaccesslvl">
			<option value=""><?php echo lang_get( 'select_option' ) ?></option>
			<?php for( $i = 0; $i < count( $access_levels ); $i++ ) {
				$level = explode( ':', $access_levels[$i] );
				echo "\n\t\t\t";
				if( $selected_max_level == $level[0]) {
					echo '<option value="' . $level[0] . '" selected="selected">' . $level[1] . '</option>';
				} else {
					echo '<option value="' . $level[0] . '">' . $level[1] . '</option>';
				}
			} ?>
		</select>
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo plugin_lang_get( 'config_field_values' )?>
		<br><span class="small"><?php echo plugin_lang_get( 'config_field_values_helptxt' ) ?></span>
	</td>
	<td class="center">
		<table id="field_values_fields">
		<?php
		$set_field_values = plugin_config_get_wpid( 'field_values' );
		for( $i = 0; $i < count( $list_fieldnames ); $i++ ) {
			if( lang_exists( $list_fieldnames[ $i ], lang_get_current( ) ) ) {
				$field_title = lang_get( $list_fieldnames[$i] );
			} elseif( lang_exists( str_replace( '_id', '', $list_fieldnames[$i]), lang_get_current() ) ) {
				$field_title = lang_get( str_replace( '_id', '', $list_fieldnames[$i]) );
			} elseif( lang_exists( $list_fieldnames[$i] . 'rmation', lang_get_current() ) ) {
				$field_title = lang_get( $list_fieldnames[$i] . 'rmation' );
			} else {
				continue;
			}


			echo '<tr><td>' . $field_title . '</td>
			<td><input type="text" value="' .
				htmlspecialchars( issetOrDefault( $list_fieldnames[ $i ], $set_field_values, '' ) ) . '"
				name="field_values[' . $list_fieldnames[$i] . ']"></td></tr>';
		}
		?>
		</table>
	</td>
</tr>



<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'change_configuration' )?>" />
	</td>
</tr>

</table>
</form>


<script>
function trigger_gray_out_if_empty( elem ) {
	if( elem.val( ).replace( /\s/g, '' ) == '' ) {
		elem.css('background-color', '#cecece');
	} else {
		elem.css('background-color', '#ffffff');
	}
}
/**
 * Gray out field_value-fields without content, add event listener to gray
 * out/whiten the appropriate fields on entering a value
 */
var fvalinp = jQuery('#field_values_fields').find('input')
fvalinp.each(function() {
	trigger_gray_out_if_empty( jQuery(this) );
});
fvalinp.on('blur, keyup', function(e) {
	trigger_gray_out_if_empty( jQuery(this) );
});


/**
 * Event handler for project dropdown select, reload entire form with
 * project_id-GET-parameter
 */
jQuery('#project_id').on( 'change', function() {
	window.location = window.location.protocol + '//'
		+ window.location.host
		+ window.location.pathname
		+ '?page=EasyEnter/config.php&project_id=' + jQuery(this).val();
});

</script>
<?php
html_page_bottom();
