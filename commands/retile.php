<?php

/**
 * Calls RetileXYZ::retile using arguments from the supplied json file
 */

require __DIR__ . '/../vendor/autoload.php';

use Bahamut\Tiles\RetileXYZ;

$jsonFileName = $argv[1];
$args = json_decode( file_get_contents( $jsonFileName ), true );

RetileXYZ::retile( $args['baseTilePath'], $args['retilePaths'], $args['startZoomLevel'], $args['endZoomLevel']);