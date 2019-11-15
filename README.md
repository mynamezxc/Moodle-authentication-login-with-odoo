# Moodle authentication plugin for Odoo
This plugin let your users to login to Moodle using their login and passwords from Odoo.
Basic personal info (like names and e-mail addresses) is also imported.
Support for Moodle versions 3.7 and below

## Configuration
1. Place the `odoo` directory inside the `auth` dir of your Moodle installation.
2. Login as admin. Moodle should automatically detect the new plugin.
3. Go to `Site Administration / Plugins / Authentication / Manage Authentication` and click "enable" (or the eyes in new Moodle) next to "Odoo login and password integration".
4. Go to Site `Administration / Plugins / Authentication / Odoo login and password integration` to provide plugin with information about your Moodle installation. You should probably also block users from editing fields that are automatically imported from Odoo.
5. Check it out and don't forget to star this plugin
