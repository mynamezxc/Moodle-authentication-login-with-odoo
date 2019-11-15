<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/authlib.php');
require_once('xmlrpc.php');


class auth_plugin_odoo extends auth_plugin_base {

    function auth_plugin_odoo() {
        $this->authtype = 'odoo';
        $this->roleauth = 'auth_odoo';
        $this->errorlogtag = '[AUTH odoo] ';
        $this->config = get_config('auth/odoo');

        set_config('field_updatelocal_firstname', 'onlogin', 'auth/odoo');
        set_config('field_updatelocal_lastname', 'onlogin', 'auth/odoo');
        set_config('field_updatelocal_city', 'onlogin', 'auth/odoo');
        set_config('field_updatelocal_email', 'onlogin', 'auth/odoo');
        set_config('field_updatelocal_country', 'onlogin', 'auth/odoo');
        set_config('field_updatelocal_institution', 'onlogin', 'auth/odoo');
    }

    function odoo_read($uid, $model, $ids, $fields) {
        $objs = xmlrpc_request(
            $this->config->url . '/xmlrpc/2/object',
            'execute_kw',
            array(
                $this->config->db,
                $uid,
                $this->config->password,
                $model,
                'read',
                array($ids),
                array(
                    'fields' => $fields
                )
            )
        );
        return $objs;
    }

    function user_login($username, $password) {
        $user_id = xmlrpc_request(
            $this->config->url . '/xmlrpc/2/common',
            'authenticate',
            array(
                $this->config->db,
                $username,
                $password,
                array()
            )
        );
        return $user_id && is_numeric($user_id);
    }

    function is_internal() {
        return false;
    }

    function get_userinfo($username) {
        $userinfo = array();

        /* Get admin user's id */
        $uid = xmlrpc_request(
            $this->config->url . '/xmlrpc/2/common',
            'authenticate',
            array(
                $this->config->db,
                $this->config->user,
                $this->config->password,
                array()
            )
        );

        /* Get logged-in user's id */
        $user_ids = xmlrpc_request(
            $this->config->url . '/xmlrpc/2/object',
            'execute_kw',
            array(
                $this->config->db,
                $uid,
                $this->config->password,
                'res.users',
                'search',
                array(
                    array(array('login', '=', $username)),
                ),
            )
        );
        /* Get user info */
        if($user_ids) {
            $users = $this->odoo_read(
                $uid,
                'res.users',
                $user_ids,
                array(
                    'name',
                    'email',
                    'city',
                    'city_gov',
                    'country',
                    'country_gov',
                    'coordinated_org',
                    'organizations',
                )
            );
            $user = $users[0];
            /* Basic fields */
            $name = explode(' ', $user['name'], 2);
            $userinfo['firstname'] = isset($name[0]) ? $name[0] : "";
            $userinfo['lastname'] = isset($name[1]) ? $name[1] : "";
            $userinfo['email'] = $user['email'];

            /* Non-standard fields */
            if(isset($user['city']) && $user['city']) {
                $userinfo['city'] = $user['city'];
            } elseif(isset($user['city_gov']) && $user['city_gov']) {
                $userinfo['city'] = $user['city_gov'];
            }

            /* get country code */
            $country_id = null;
            if(isset($user['country']) && $user['country']) {
                $country_id = $user['country'][0];
            } elseif(isset($user['country_gov']) && $user['country_gov']) {
                $country_id = $user['country_gov'][0];
            }
            if($country_id) {
                $countries = $this->odoo_read(
                    $uid,
                    'res.country',
                    array($country_id),
                    array('code')
                );
                $userinfo['country'] = strtoupper($countries[0]['code']);
            }

            /* Get organizations */
            $organization_ids = array();
            if(isset($user['coordinated_org']) && $user['coordinated_org']) {
                $organization_ids = array_merge($organization_ids, $user['coordinated_org']);
            }
            if(isset($user['organizations']) && $user['organizations']) {
                $organization_ids = array_merge($organization_ids, $user['organizations']);
            }
            $organization_ids = array_unique($organization_ids);

            if($organization_ids) {
                $organization_objs = $this->odoo_read(
                    $uid,
                    'organization',
                    $organization_ids,
                    array('name')
                );

                $organizations = array();
                foreach($organization_objs as $organization) {
                    $organizations[] = $organization['name'];
                }
                $userinfo['institution'] = implode(', ', $organizations);
            }
        }
        return $userinfo;
    }


    function config_form($config, $err, $user_fields) {
        global $OUTPUT;

        include "config.html";
    }

    function process_config($config) {
        // set to defaults if undefined
        if (!isset ($config->db)) {
            $config->db = '';
        }
        if (!isset ($config->url)) {
            $config->url = '';
        }
        if (!isset ($config->password)) {
            $config->password = '';
        }
        if (!isset ($config->user)) {
            $config->password = '';
        }

        // save settings 
        set_config('db',       $config->db,       'auth/odoo');
        set_config('url',      $config->url,      'auth/odoo');
        set_config('password', $config->password, 'auth/odoo');
        set_config('user',     $config->user,     'auth/odoo');

        return true;
    }
}
