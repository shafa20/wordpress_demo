<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

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
define( 'AUTH_KEY',         '~yUS9V8nZ4D)5rD1ym:5aB<=cCnQm+SqC_rz>cJ^&aeC tJQ{&Z+XLPy0aK71*Rb' );
define( 'SECURE_AUTH_KEY',  '#zMqpfrFoq-d/<:,*N2ZmFAPImEt@H$g3$]vV(2p8W{oi`4Bm088xjZMu,7WOdm5' );
define( 'LOGGED_IN_KEY',    'w[]SV+3rkXFqZFLpg[TL+&G6S=(i$V7@X#osdhA>e(dkAfKPe1U `75uJP%}&RWa' );
define( 'NONCE_KEY',        'MSKQ]Hw]%(6@7~7sEu/sBb*c{zaqe&^Q708WU=;o`!|j9k7SXO4gQToqa|GC2Np3' );
define( 'AUTH_SALT',        'E*?jwer$[,<##g[ uTj(opA>A&vP46_of%2#c6s|DVN/zE]L`(9q 1jT.zCqu_wE' );
define( 'SECURE_AUTH_SALT', '7|LuC,f$cgP]IyDkFDykb^,hg%{ryY8n87Uv*(dvhMz7|RfY;XqD$6RK/f5($Ii;' );
define( 'LOGGED_IN_SALT',   'mVApOtuxcob5TK&|Q?:ft9r?`hd]OpCE@LFb 9}S]*r{*!iKEJ7*;R9MEKL3Rpm3' );
define( 'NONCE_SALT',       'Sh6b0A&?0/PGBEN_T)GAI&3kc/L2(EhDTB!D=u+YCv%ZR;HC*g WYvV:ekrpl-|i' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
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
