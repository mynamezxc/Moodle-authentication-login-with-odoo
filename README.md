# Moodle authentication plugin for Odoo
This plugin let your users to login to Moodle using their login and passwords from Odoo.
Basic personal info (like names and e-mail addresses) is also imported.

## Configuration
1. Place the `odoo` directory inside the `auth` directory of your Moodle instalation.
2. Login as admin. Moodle should automatically detect the new plugin.
3. Go to `Site Administration / Plugins / Authentication / Manage Authentication` and click "enable" next to "Odoo login and password integration".
4. Go to Site `Administration / Plugins / Authentication / Odoo login and password integration` to provide plugin with information about your Moodle instalation. You should probably also block users from editing fields that are automatically imported from Odoo.
5. You are all set.
