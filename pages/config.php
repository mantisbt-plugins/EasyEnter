<?php
# Mantis Plugin "EasyEnter"
# Copyright (C) 2015 Frithjof Gnas - fg@prae-sensation.de
#
# Description:
# Configuration form for EasyEnter plugin. Set the fields to include, the values
# to populate fields with an defining the threshold when the plugin should
# become active.
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

layout_page_header( plugin_lang_get( 'title' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_plugin_page.php' );



# Default value, overwritten by GET-Value or previous set session
if( !isset( $_SESSION['selected_project_id'] ) ) {
	$_SESSION['selected_project_id'] = 0;
}

if( isset( $_GET['project_id'] ) ) {
	$_SESSION['selected_project_id'] = (int) $_GET['project_id'];
}


# For include/exclude select-field-list
$g_list_fieldnames = array(
	'category_id', 'reproducibility', 'severity', 'priority', 'due_date', 
	'profile_id', 'platform', 'os', 'os_build', 'handler_id', 'summary',
	'description', 'steps_to_reproduce', 'additional_info', 'ufile[]',
	'view_state', 'report_stay'
);
# For translation of fields where the fieldname and the corresponding language
# .string differ to much (Key->Value; fieldname->langstring
$g_list_fields_langstring = [
	'category_id' => 'category', 'profile_id' => 'profile',
	'handler_id' => 'handler', 'additional_info' => 'additional_information',
	'ufile[]' => 'upload_file'
];

# For Noscript-warning; background-color
$t_status_colors = config_get('status_colors');
$warn_color    = $t_status_colors['new'];




/**
 * Build multiselect with all available bug-report fields and the custom fields
 * that are assigned to current project.
 * To mark the saved options an array with the names of the selected fields has
 * to be passed.
 * @param string $p_name			Name for the select element
 * @param array $p_selected_fields	Array with field names that were set so far (to
 * 									show them as selected)
 * @return void						echos string
 */
function print_select_available_fields( $p_name, $p_selected_fields ) {
	global $g_list_fieldnames, $g_list_fields_langstring;

	#Build dropdown with all available fields for in-/excluding
	$t_html = '
	<select name="' . $p_name . '[]" size="11" multiple="multiple" class="form-control">
		<optgroup label="' .  plugin_lang_get( 'config_fields_default_fields' ) .'">
	';

	for( $i = 0; $i < count( $g_list_fieldnames ); $i++) {
		$t_selected = '';
		if( in_array( $g_list_fieldnames[$i], $p_selected_fields ) ) {
			$t_selected = ' selected="selected" ';
		}

		#Generate <option>, because of some inconsistencies an additional array
		# "$g_list_fields_langstring" is used to get translation-strings for
		# some fields (like "category" instead "category_id" or "upload_file"
		# instead "ufile[]"
		$t_option = '';
		if ( isset( $g_list_fields_langstring[$g_list_fieldnames[$i]] ) ) {
			$t_field_name = $g_list_fields_langstring[$g_list_fieldnames[$i]];
		} else {
			$t_field_name = $g_list_fieldnames[$i];
		}
		if( lang_exists( $t_field_name, lang_get_current( ) ) ) {
			$t_option = "\n\t\t\t"
				. '<option value="' . $g_list_fieldnames[$i] . '" %s>'
					. lang_get( $t_field_name )
				. '</option>';

		}

		$t_html .= sprintf( $t_option, $t_selected );
	}
	$t_html .= "\n\t\t</optgroup>\n\n";


	#Add custom fields (if specific project is selected only the fields
	# assigned to it)
	$current_project_id = $_SESSION['selected_project_id'];
	$t_custom_fields = custom_field_get_ids();
	$t_customfields_options = [];
	foreach( $t_custom_fields as $t_field_id ) {
		$t_selected = '';
		$t_field_name = 'custom_field_' . $t_field_id;
		if( in_array( $t_field_name, $p_selected_fields ) ) {
			$t_selected = ' selected="selected" ';
		}

		$t_field_in_selected_project_id = in_array(
			$current_project_id, custom_field_get_project_ids( $t_field_id )
		);
		if( $current_project_id == 0 || $t_field_in_selected_project_id ) {
			$t_desc = custom_field_get_definition( $t_field_id );

			$t_customfields_options[] = "\n\t\t"
				.'<option value="' . $t_field_name . '" ' . $t_selected . '>'
					. custom_field_get_display_name( $t_desc['name'] )
				. '</option>';
		}
	}

	#Only add optgroup "custom fields" if there are any of them
	if( count( $t_customfields_options ) > 0) {
		$t_html .= '
			<optgroup label="' . plugin_lang_get( 'config_fields_custom_fields' ) . '">
				' . implode( "\n\t\t\t\t", $t_customfields_options ) . '
			</optgroup>
		';
	}

	echo $t_html . '</select>';
}


/**
 * Wrapper for plugin_config_get(), just adds parameter "project_id" from
 * Session to origin function call
 * @param string $p_option
 * @return mixed
 */
function plugin_config_get_wpid( $p_option ) {
	$p_project = null;
	if( $_SESSION['selected_project_id'] != 0 ) {
		$p_project = $_SESSION['selected_project_id'];
	}
	return plugin_config_get( $p_option, null, null, null, $p_project );
}

/**
 * Checks if $array has $key than return the value otherwise return $default
 * @param string $p_key
 * @param array $p_array
 * @param mixed $p_default (optional) value to set if $key is not set
 * @return mixed
 */
function issetOrDefault( $p_key, $p_array, $p_default = null ) {
	if( isset( $p_array[$p_key] ) ) {
		return $p_array[$p_key];
	}
	return $p_default;
}




?>
<!-- NOSCRIPT-Hinweis -->
<noscript><br>
<div class="col-md-12 col-xs-12">
    <div class="panel panel-warning text-center">
	<strong><?php echo plugin_lang_get( 'noscriptwarning' ) ?></strong>
    </div>
</div></noscript>
<!-- ENDE // NOSCRIPT-Hinweis -->
<?php if(!is_writable( dirname( __DIR__  ) . '/files/easyenter_plugin_configuration.js' ) ): ?>
    <div class="alert alert-danger" role="alert"><strong>Please adjust file permission</strong>
        <br>File <q>easyenter_plugin_configuration.js</q> has to be writable, please adjust the file permissions!</div>
<?php endif; ?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container" >

<form id="formatting-config-form" action="<?php echo plugin_page( 'config_edit' )?>" method="post">
    <?php echo form_security_field( 'plugin_EasyEnter_config_edit' ) ?>

<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
    <h4 class="widget-title lighter">
        <i class="ace-icon fa fa-medkit"></i>
        <?php echo plugin_lang_get( 'title' ) . ': ' . plugin_lang_get( 'config' )?>
    </h4>
</div>
<div class="widget-body">
<div class="widget-main no-padding">
<div class="table-responsive">
<table class="table table-bordered table-condensed table-striped">
<tr>
    <th class="category width-60">
		<?php echo plugin_lang_get( 'config_project' )?>
		<br /><span class="small"><?php echo plugin_lang_get( 'config_project_helptxt' )?></span>
	</th>
	<td class="center width-40">
		<select name="project_id" id="project_id" class="form-control">
			<?php print_project_option_list( $_SESSION['selected_project_id'] ); ?>
		</select>
	</td>
</tr>

<tr>
    <th class="category">
		<?php echo plugin_lang_get( 'config_include_fields' )?>
	</th>
	<td class="center">
		<?php
		print_select_available_fields(
			'include_fields', plugin_config_get_wpid( 'include_fields' )
		);
		?>
	</td>
</tr>

<tr>
	<th class="category">
		<?php echo plugin_lang_get( 'config_exclude_specialfields' )?>
	</th>
	<td class="center exclude_special_fields">
		<div style="width:98%;margin:0 auto;text-align:left;float:none">
			<label><input type="checkbox" name="exclude_fields[]"
				<?php if( in_array( 'special.custom_profile', plugin_config_get_wpid( 'exclude_fields' ) ) ) {
					echo 'checked="checked"';
				}
				?>
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

<tr>
	<th class="category">
		<?php echo plugin_lang_get( 'config_max_access_level' )?>
	</th>
	<td class="center">
		<?php # Get available groups with naming via translation string (123:Grpname,456:Grp2,...)
		$t_access_levels_string = lang_get( 'access_levels_enum_string' );
		$t_access_levels = explode( ',', $t_access_levels_string );
		$selected_max_level = plugin_config_get_wpid( 'max_access_level' );
		?>
		<select name="max_access_level" size="1" id="sel__maxaccesslvl" class="form-control">
			<option value=""><?php echo lang_get( 'select_option' ) ?></option>
			<?php for( $i = 0; $i < count( $t_access_levels ); $i++ ) {

				$t_level = explode( ':', $t_access_levels[$i] );
				echo "\n\t\t\t";

				if( $selected_max_level == $t_level[0]) {
					echo '<option value="' . $t_level[0] . '" selected="selected">'
						. $t_level[1]
					. '</option>';
				} else {
					echo '<option value="' . $t_level[0] . '">'
						. $t_level[1]
					. '</option>';
				}
			} ?>
		</select>
	</td>
</tr>

<tr>
	<th class="category">
		<?php echo plugin_lang_get( 'config_field_values' )?>
		<br><span class="small"><?php echo plugin_lang_get( 'config_field_values_helptxt' ) ?></span>
	</th>
	<td class="center">
		<table id="field_values_fields" class="table table-condensed">
		<?php
		$set_field_values = plugin_config_get_wpid( 'field_values' );
		for( $i = 0; $i < count( $g_list_fieldnames ); $i++ ) {

			$field_name = $g_list_fieldnames[$i];
			$field_name_wo_id = str_replace( '_id', '', $g_list_fieldnames[$i] );
			$field_name_w_rmation = $g_list_fieldnames[$i] . 'rmation';
			if( lang_exists( $field_name, lang_get_current( ) ) ) {
				$field_title = lang_get( $field_name );
			} elseif( lang_exists( $field_name_wo_id, lang_get_current() ) ) {
				$field_title = lang_get( $field_name_wo_id );
			} elseif( lang_exists( $field_name_w_rmation, lang_get_current() ) ) {
				$field_title = lang_get( $field_name_w_rmation );
			} else {
				continue;
			}


			echo '<tr><td class="text-left">' . $field_title . '&nbsp;</td>
			<td><input type="text" value="' .
				htmlspecialchars(
					issetOrDefault(
						$g_list_fieldnames[$i], $set_field_values, ''
					)
				) . '" name="field_values[' . $g_list_fieldnames[$i] . ']">
			</td></tr>';
		}
		?>
		</table>
	</td>
</tr>
</table>
</div>
</div>
    <div class="widget-toolbox padding-8 clearfix">
        <input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'change_configuration' )?>" />
    </div>
</div>
</div>
</form>
</div>
</div>


<script type="text/javascript" src="<?php echo plugin_file( 'easyenter_config.js' ); ?>"></script>
<?php

layout_page_end();
