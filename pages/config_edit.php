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


form_security_validate( 'plugin_easyenter_config_edit' );

auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );


/**
 * Wrapper for plugin_config_get(), just adds parameter "project_id" to
 * function call
 * @param string $p_option
 * @return mixed
 */
function plugin_config_get_wpid( $p_option, $pid = null ) {
    if( $pid == 0 ) {
        $pid = null;
    }
    return plugin_config_get( $p_option, null, null, null, $p_project );
}

/**
 * Wrapper for plugin_config_set(), just adds parameter "project_id" to
 * function call
 * @param string $p_option
 * @param int $pid
 */
function plugin_config_set_wpid( $p_option, $p_value, $pid = 0 ) {
    $p_project = null;
    plugin_config_set( $p_option, $p_value, NO_USER, $pid );
}









$project_id = gpc_get_int( 'project_id', 0);

$f_include_fields = gpc_get_string_array('include_fields');
$f_exclude_fields = gpc_get_string_array('exclude_fields');
$f_max_access_level = gpc_get_int('max_access_level');
$f_field_values = gpc_get_string_array('field_values');


if ( plugin_config_get_wpid( 'include_fields', $project_id ) != $f_include_fields ) {
    plugin_config_set_wpid( 'include_fields', $f_include_fields, $project_id );
}
if ( plugin_config_get_wpid( 'exclude_fields', $project_id ) != $f_exclude_fields ) {
    plugin_config_set_wpid( 'exclude_fields', $f_exclude_fields, $project_id );
}
if ( plugin_config_get_wpid( 'max_access_level', $project_id ) != $f_max_access_level ) {
    plugin_config_set_wpid( 'max_access_level', $f_max_access_level, $project_id );
}
if ( plugin_config_get_wpid( 'field_values', $project_id ) != $f_field_values ) {
    plugin_config_set_wpid( 'field_values', $f_field_values, $project_id );
}






print_successful_redirect( plugin_page( 'config', true ) );