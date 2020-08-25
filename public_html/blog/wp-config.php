<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'allamerica_cust');

/** MySQL database username */
define('DB_USER', 'allamerica_cust');

/** MySQL database password */
define('DB_PASSWORD', 'Honk4bal');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         ';k,XEb@Lxd2zQY3_|`IoA3D-IR$N3W;Y$_JrX{%SYR}kOpoLbM.[=y@Xt=a.UF|k');
define('SECURE_AUTH_KEY',  'ZYO03@$2GCLpMaq `]7(@@QMCQX|qZ^#927YKrxyaw5u82cO!m[4_4GimU@ttW$J');
define('LOGGED_IN_KEY',    'z8ef<()h[{l9vSq7W/l<W_Y>pQq/mE`g=;BGZRn?03Gb76Q-luQ^G!^ArGBg}X@O');
define('NONCE_KEY',        '<)kLs9@Gng BufD!U6s1Hy~a6Q.dGrsDjV7BOS&o 6}.?Jn5n>y}KeHZBAmAr1`S');
define('AUTH_SALT',        '~08[cEQbOwBw0xeN^?-sYHvKtamP8^QB|#R!kgmivF_c.*Z;YCb^5Mx%If_(r$%U');
define('SECURE_AUTH_SALT', 'b}lj}vnlBWa:M1Dhqfn@kA:@WKz(O0h}@v;$FeH+9[5Z/6!Cea*?WB{y%,fR>Wx%');
define('LOGGED_IN_SALT',   'qTdS070FYX_.0]kOYtzX:*}X.r)V:399s+x0(71YC^*a AERt<qUR+4qhT.tNzuK');
define('NONCE_SALT',       '<+I&QJUQjkME&*aTJ4.ei4`X~N4MYzmakyl/=Iq:(x&:F[YR2&Nwt8s4-e<0s![%');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'blog_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
