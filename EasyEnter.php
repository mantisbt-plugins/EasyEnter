<?php
# Mantis Plugin "EasyEnter"
# Copyright (C) 2017 Frithjof Gnas - fg@prae-sensation.de
#
# Description:
# Often there's a problem for noob-users to know what to do with a bugtracker.
# Even if it is really easy and there is a pictured documentation the users just
# don't get it to enter their wishes/feature requests, bugs etc. into the
# bugtracker. Instead they send you dozens of mails or worse: they call you to
# tell you about an idea they just got.
#
# Even the users goodwilled capitulate seeing a bug tracker interface the first
# time. From the thinking that any bug-report is better than nothing or doing
# the user's work (enter the tickets yourself), this plugin wants to present the
# reporters an easier flattened bug report form. Everything else stays the same,
# but the hurdle of entering bugs is lowered significantly!
#
#
# Disclaimer & License:
# This plugin - EasyEnter - is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.



require_once( config_get( 'class_path' ) . 'MantisPlugin.class.php' );


class EasyEnterPlugin extends MantisPlugin  {

	/**
	 * @var array
	 */
	var $current_config = [];



	/**
	 * @var int
	 */
	var $project_id;



	/**
	 *  A method that populates the plugin information and minimum requirements.
	 */
	function register( ) {
		$this->name = lang_get( 'plugin_easyenter_title' );
		$this->description = lang_get( 'plugin_easyenter_description' );
		$this->page = 'config';

		$this->version = '1.1';
		$this->requires = array(
			'MantisCore' => '2.6.0',
		);

		$this->author = 'Frithjof Gnas';
		$this->contact = 'fg@prae-sensation.de';
	}



	function config( ) {
		return array(
			'include_fields' => array(
				'summary', 'description'
			),
			# Exclude fields: Entries with prefix "special." are for special
			# elements that should be excluded additionally
			'exclude_fields' => array(
				# hide the "profile ...or enter"-row
				'special.custom_profile',
				# hide the asterisks at the mandatory field-labels
				#'special.mandatory_asterisks'
			),
			'field_values' => array(
				'category_id' => 1,
				'priority' => NORMAL,
				'additional_info' => 'Submitted using "EasyEnter"',
				'report_stay' => 'CHECKED',
			),
			# EasyEnter active for users with (including) maximum access level
			'max_access_level' => UPDATER
		);
	}


	function events()
    {
        return array(
            'EVENT_LAYOUT_CONTENT_BEGIN' => EVENT_TYPE_OUTPUT,
            'EVENT_LAYOUT_PAGE_FOOTER' => EVENT_TYPE_OUTPUT,
        );
    }

    /**
	 * @return array
	 */
	function hooks( ) {
		return array(
			'EVENT_LAYOUT_CONTENT_BEGIN' => 'show_noscript_warning',
			'EVENT_LAYOUT_PAGE_FOOTER' => 'replace_bug_report_page',
		);
	}



	/**
	 * Insert warning into page about EasyEnter requiring javascript to work
	 * @param string $p_event
	 * @return string
	 */
	function show_noscript_warning( $p_event ) {
		$this->set_current_project( );
		if( !$this->plugin_requirements_fulfilled( ) ) {
			return '';
		}

		return '<noscript>
			<br>
			<table class="width100" cellspacing="1"><tbody><tr><td
				style="background-color:#fcbdbd; text-align:center">
				<strong>' . plugin_lang_get( 'noscriptwarning' ) . '</strong>
			</td></tr></tbody></table>
			<br>
		</noscript>
		<script>document.write(\'<div class="powered_easyenter" '
			. 'style="text-align:right;font-style:italic;">powered by '
			. 'EasyEnter</div>\')</script>
		';
	}



	/**
	 * Replace bug report page with version where the mandatory and shown fields
	 * are notable reduced.
	 * In configuration is set which fields should be hidden and for what
	 * usergroup the "EasyEnter" should become active.
	 * @param string $p_event
	 * @return string
	 */
	function replace_bug_report_page( $p_event ) {

		$this->set_current_project( );
		if( !$this->plugin_requirements_fulfilled( ) ) {
			return '';
		}

		return $this->jquery_rebuild_bug_report_page( );
	}



	/**
	 * Ensure the requirements for executing the plugin's main hook
	 * ("replace_bug_report_page") are fulfilled.
	 * Requirements are met if...
	 *  ..the current page is the bug_report_page and
	 *  ..the current user's access level is lower or equal the configured one
	 *
	 * @access protected
	 * @return bool
	 */
	function plugin_requirements_fulfilled( ) {

		if( !is_page_name( 'bug_report_page' ) ) {
			return false;
		}

		# access levels (defined in core/constant_in.php)
		$t_user_id = auth_get_current_user_id( );
		$t_user_access_level = user_get_access_level( $t_user_id, $this->project_id );
		$t_max_access_level = $this->get_current_config( 'max_access_level' );
		if( $t_user_access_level > $t_max_access_level ) {
			return false;
		}

		return true;
	}



	/**
	 * Sets the current globally set project id to class-property
	 */
	function set_current_project( ) {
		$this->project_id = (int) helper_get_current_project();;
	}



	/**
	 * Get the configuration valid for current project.
	 * @param string $p_key
	 * @return mixed
	 */
	function get_current_config( $p_key ) {
		return plugin_config_get( $p_key, null, null, null, $this->project_id );
	}



	function jquery_rebuild_bug_report_page()
	{
		$t_easyenter_config = array(
			'include_fields'=> $this->get_current_config('include_fields'),
			'exclude_fields'=> $this->get_current_config('exclude_fields'),
			'field_values'=> $this->get_current_config('field_values'),
			'max_access_level'=> $this->get_current_config('max_access_level'),
		);
		$t_easyenter_plugin_configuration = 'var easyenter_config='.json_encode($t_easyenter_config).';';
		$t_easyenter_plugin_configuration .= "var label_selectprofile = '" . lang_get( 'select_profile' ) . "';";
		file_put_contents( dirname(__FILE__) . '/files/easyenter_plugin_configuration.js', $t_easyenter_plugin_configuration );

		$t_html = '
            <script src="' . plugin_file( 'easyenter_plugin_configuration.js' ) . '"></script>
			<script type="text/javascript" src="'
				. plugin_file( 'easyenter_page.js' ) . '"></script>';
		return $t_html;
	}
}
