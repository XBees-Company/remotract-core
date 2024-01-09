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
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'remo_main' );

/** Database username */
define( 'DB_USER', 'remo_admin' );

/** Database password */
define( 'DB_PASSWORD', 'ar0DrjsI4oNsfpN8' );

/** Database hostname */
define( 'DB_HOST', 'localhost:3306' );

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
define( 'AUTH_KEY',         'm~ee+bPofh%Z*+OE;< ! S/|_G(Y<_Lw7PWf*$,Qg<}k68Fg,7NgXT*G{$$O$$_%' );
define( 'SECURE_AUTH_KEY',  ' y B*3mn[]/LIXr/w+//OY*PCT!Pl]_AY)ck!@sTmS2Sj%%QKG[jc~ |.Ykd?H78' );
define( 'LOGGED_IN_KEY',    'lJKShPx-x>XCtLXtwz(``jA2lrPxR<d0[pu:^1b?0x},[(:?|G3c~c;)@dHC1PY6' );
define( 'NONCE_KEY',        'BEIeH3#_=B09!G<LG*8sXX@%vfr$xhG&N0LfCVY) ymwAflP@$XA3>fv[njin@C*' );
define( 'AUTH_SALT',        'C iG=#f< Ptl}}=FINa8dv=hrFW)y:tpS{:rjJCJJKRj0nRYR!8)7kH y/az=rX{' );
define( 'SECURE_AUTH_SALT', '-0~f0NH(XQN_Oxpg|{eaKJS=KKFB)eIlR=/{va8P)wKoqthB;0s>AZ2QJiLFt(wp' );
define( 'LOGGED_IN_SALT',   'v2ez[^Z[G+(On)!kwcS.1Z?9YqwwTg=]]r)+ 3L@%]tEMDSu9#A`8E${$yO09qD ' );
define( 'NONCE_SALT',       'Xn1BFFTyCU5PUg%+p2Dnw0Ty<p%=B9U2cHA7MJ-%,eijmvpJyQp5SK,A9_L,*L^L' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wprt23_';

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
