<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'eastsidebub' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'djgoexVauykd8XWUo8odPn9DXEFlZHKQxTLsrX90WSbJFsRr3ttT36IYH0jKbktJ');
define('SECURE_AUTH_KEY',  'm2O9UIWv9CeowXFxgR9HGTFZE9UsxuENp3WVLQx1o8Dtf7lcZMInnXwcwtf0nEbK');
define('LOGGED_IN_KEY',    '4E6LZcVoHJhnXuThPGByhM1O3SJk20vZ7ZxQvKVZ8Hqm67KT2f2Ner7SqqmkT6Uu');
define('NONCE_KEY',        '33SnkBX9QFOIQjrGdWeYuwGssCtNTHSDd29Xb6shyUJC3cz3htzxJiu3Rl5iuu6f');
define('AUTH_SALT',        '7VVl7Bk26sK2u4mmKkBrmOe1uI5LaivI5uNGBE9086W9ah56Ldxk2kv3hO16ekS3');
define('SECURE_AUTH_SALT', 'XbpWNIncXrMr3L3YI4mpHMRKA2mP4ApKOEF8Vfw2APs2pcaG6bxbiAngbyEayI2j');
define('LOGGED_IN_SALT',   '27FdB0PymKdDJTNPS7bUDsu33ffIVmnRahMW8pp2VC3HC1M0y9cfKQadcMgo7bwc');
define('NONCE_SALT',       'wbFJPMEBoRyGqGUkY0fr39PLe6CZZ7YQajwtRPhaaPbMKfIDvhzJI1sJUk8upvXt');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
