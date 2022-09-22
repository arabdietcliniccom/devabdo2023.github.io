<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'last_db' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'NV^EQ&$J{RW~048 zr4(8,%CA(T$5+<hM! .HY(|$iJ=e~czG2PxcW3rvp}~*JXG' );
define( 'SECURE_AUTH_KEY',  '-,0^i:M2hiny&|:;+fw{5|1/Vb.a#s7EOxl[&Hgj@$D=&nn,%nccd<qT:3w(gC9`' );
define( 'LOGGED_IN_KEY',    'M<%g(x/rNdELRIUX!| /t^fyKFq6kGjcB0}bb)4ZN2qAmzk9!} .[TCSg%u&^~1E' );
define( 'NONCE_KEY',        'L)GRgpf.QzG9F:s.k{Yo|;a+7Yuyjn4(A4Srtcg{``NJ@W<a4|CO<zV}1s$K@lsw' );
define( 'AUTH_SALT',        '7WD%_p5k&}n]m,XB,*i~3(7/4j9R!}c-5{eWVHnwG4?Pl)k(1(]iTP!euH@~KYU(' );
define( 'SECURE_AUTH_SALT', 'lb0%3il/+HD9JDIA#$l4G0XX<X9F]l{I*hlCiP6qp@u@5`_1P,z_`m=Q-h{^#DMs' );
define( 'LOGGED_IN_SALT',   '.hxzU*cD6gFngfty8v]-,f{ROwl:A), Cl o4,Ul5=NZcR44#:[C-%vcFzno>F1A' );
define( 'NONCE_SALT',       'k`G$Jz:T_Vki)w,/bkD~f2k=}.npxF8W.)EHEaMAv7=x^].AEUR$HM+#/e#(h4P}' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
