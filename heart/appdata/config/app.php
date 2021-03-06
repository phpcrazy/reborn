<?php

return array(

	/**
	 * Default Language for Reborn CMS
	 */
	'lang' => function() {
				return \Setting::get('default_language', 'en');
			},

	/**
	 * Supported Language for Reborn CMS
	 * Now only support english and myanmar
	 *
	 */
	'support_langs' => array(
			'en' => 'English',
			'my' => 'Myanmar'
		),

	/**
	 * Default Timezone for Reborn CMS
	 */
	'timezone' => 'UTC',

	/**
	 * Default Locale for Reborn CMS
	 */
	'locale' => 'en',

	/**
	 * Fallback Locale for Reborn CMS
	 * Fallback locale is use on default locale is does not work.
	 * Don't change this locale!
	 *
	 */
	'fallback_locale' => 'en',

	/**
	 * Character Encoding for Reborn CMS
	 */
	'encoding' => 'UTF-8',

	/**
	 * Config Values for Logging process
	 */
	'log' => array(

			// Path for saving log file
			'path' => STORAGES.'logs'.DS,

			// log file name use at saving process (eg: rebornlog-20121216.log)
			'file_name' => 'rebornlog-'.date('Ymd'),

			// Log file extension (default is .log). Don't forget "dot"!
			'ext' => '.log',
		),

	/**
	 * Config for the security
	 */
	'security' => array(
		'csrf_key' => 'csrf_token_key',
		// Supprot Method md5, sha1, random (Reborn\Util\Str::random with md5)
		'token_encrypt' => 'random'
	),

	/**
	 * Config key for the Module
	 */
	'module' => array(
			'cores' => array(
					'admin', 'blog', 'comment', 'contact', 'media', 'module',
					'navigation', 'pages', 'setting', 'tag', 'user', 'field'
				),
			'system' => array('admin', 'pages', 'navigation', 'user', 'module', 'setting', 'field'),
		),

	/**
	 * Gate of the admin panel link
	 *
	 */
	'adminpanel' => 'admin',

	/**
	 * Session Life Time.
	 * Session life time with minute.
	 * Default is 60 minute.
	 *
	 */
	'session_lifetime' => 60,

	/**
	 * Key name for Cartalyst Sentry Authentication Packages.
	 * Default key name is "reborn_cms"
	 *
	 */
	'sentry_keyname' => 'reborn_cms'

);
