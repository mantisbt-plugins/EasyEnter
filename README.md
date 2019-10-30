# EasyEnter #

A MantisBT plugin to make the **submission** of bugs/feature requests a little bit
easier for less IT-skilled users (mantisbt-2.6.x, so far).

Often there's a problem for "noob-users" to know what to do with a bugtracker.
Even if it is really easy and there is a pictured documentation the users just
don't get it to enter their wishes/feature requests, bugs etc. into the
bugtracker. Instead they send you dozens of mails or worse: they call you to
tell you about an idea they just got.

![Screenshot of bug report page, slimmed down](https://github.com/mantisbt-plugins/EasyEnter/blob/master/files/easy_enter_bug_report_form.png)

Even the users goodwilled capitulate seeing a bug tracker interface the first
time. From the thinking that any bug-report is better than nothing or doing
the user's work (enter the tickets yourself), this plugin wants to present the
reporters an easier flattened bug report form. Everything else stays the same,
but the hurdle of entering bugs is lowered significantly!


## Requirements ##
Mantis v2.6.x

## Installation ##
Copy the folder "`EasyEnter`" in your plugin directory and open the plugin
management in your Mantis installation. Click "Installation" and you are done.
Make sure the file "easyenter_plugin_configuration.js" in the files folder is
writable by the web server.

## Configuration ##
From the start there is already a global reasonable configuration for the
EasyEnter-plugin set. Nevertheless the configuration could be changed entirely,
whether globally or on a per-project base.
To open the configuration form, just click on the EasyEnter plugin in the plugin
management overview.



## Known issues: ##
 * Fields with array-name like Multiselects, Checkbox-Collections (name="foo[]")
   are not supported correctly (take care at custom fields!)
 * Setting field_values for multiselects, many checkboxes not implemented so far
 * Performance issue due configuration written in JS file on each bug_report-request 


## Next development steps: ##
 * Check if Mantis' code guideline is met everywhere
 * Write JS-configuration file only once when configuration is saved
 * One JS-configuration file for all projects



Contact:
Feel free to contact me if you experience any bugs, got questions or similar:
Via github https://github.com/fg-ok or mail fg@prae-sensation.de 
