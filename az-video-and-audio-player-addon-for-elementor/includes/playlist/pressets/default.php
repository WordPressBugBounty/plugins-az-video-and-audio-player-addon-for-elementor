<?php

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$defaults = leanpl_get_playlist_defaults();

return array_merge( $defaults, [
    'id'    => 'default',
    'label' => 'Default',
] );
