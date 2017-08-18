<?php
/**
 * RetileXYZ
 *
 * If you're looking at tile x,y and want to zoom in, the subtiles are (in the next zoom-level's coordinate system):
 *
 * 2x, 2y   |  2x+1, 2y
 * -----------------------
 * 2x, 2y+1 |  2x+1, 2y+1
 *
 * So {197,81}gets split up into:
 *
 * 394,162	395,162
 * 394,163	395,163
 *
 * @author Doug Moody - @dm00dy
 * @author Martin Magana - @stereocilia
 *
 */

namespace Bahamut\Tiles;

class RetileXYZ
{
	/**
	 * Main positions used to describe a quadrant of an image
	 */
	const TOP = "top";
	const BOTTOM = "bottom";
	const LEFT = "left";
	const RIGHT = "right";

	/**
	 * @var array Human readable positions mapped to integer values
	 */
	static private $positionValues = [
		self::TOP => 0,
		self::BOTTOM => 128,
		self::LEFT => 0,
		self::RIGHT => 128
	];

	/**
	 * @var array Positions for the x-axis
	 */
	static private $xPositions = [
		self::LEFT,
		self::RIGHT
	];

	/**
	 * @var array Positions for the y-axis
	 */
	static private $yPositions = [
		self::TOP,
		self::BOTTOM
	];

	/**
	 * Main retile function
	 *
	 * Example:
	 *
	 * RetileXYZ::retile("/var/www/html", ["myTiles1", "myTiles2"], 11, 15);
	 *
	 * This example would start in "/var/www/html/myTiles1/Z11" and retile until it reached "Z15". Then, it would do
	 * then same for "/var/www/html/myTiles2/Z11"
	 *
	 * @param string $baseTilePath The path that all $retilePaths are relative to
	 * @param array $retilePaths An array of paths that contain zoom directories to be retiled
	 * @param integer $startZoomLevel The first zoom level to be retiled
	 * @param integer $endZoomLevel The maximum zoom level
	 */
	static public function retile( $baseTilePath, Array $retilePaths,  $startZoomLevel, $endZoomLevel )
	{

		ini_set('memory_limit', -1);


		//Loop through each path that has tiles for us to retile
		foreach ($retilePaths as $retilePath) {

			$targetPath = "{$baseTilePath}/{$retilePath}";
			self::createTilesForPath( $targetPath, $startZoomLevel, $endZoomLevel );

		}

		echo "*drops mic* (done)\n";

	}

	/**
	 * Create tiles for the given path. This path should have zoom directories in the form of Z{n}
	 *
	 * @param string $path Path containing zoom level directories
	 * @param integer $startZoomLevel
	 * @param integer $endZoomLevel
	 */
	static private function createTilesForPath( $path, $startZoomLevel, $endZoomLevel)
	{
		for($i = $startZoomLevel;$i < $endZoomLevel;$i++) {
			self::createTilesForZoomLevel( $i, $path );
		}
	}

	/**
	 * Create tiles for the given zoom level in the given path
	 *
	 * @param integer $currentZoomLevel
	 * @param string $basePath
	 */
	static private function createTilesForZoomLevel( $currentZoomLevel, $basePath )
	{
		echo $currentZoomLevel;
		$targetZoomLevel = $currentZoomLevel + 1;
		$currentPath     = "{$basePath}/Z{$currentZoomLevel}";
		chdir($currentPath);

		self::removeBlankTilesFromPath( $currentPath );

		// scan the directory now that blank tiles have been removed.
		$fileNamesInCurrentPath = scandir($currentPath);
		foreach ($fileNamesInCurrentPath as $fileName) {
			self::createZoomedTilesFromFile( $fileName, $targetZoomLevel, $basePath);
		}
	}


	/**
	 * Create an new image from a quadrant on the given image and save it with a file name that has the new coordinates
	 *
	 * @param resource $tileImage A PNG image resource
	 * @param integer $xCoordinate
	 * @param integer $yCoordinate
	 * @param integer $xPosition
	 * @param integer $yPosition
	 * @param string $newPath
	 */
	static private function createZoomedTilesFromImage( $tileImage, $xCoordinate, $yCoordinate, $xPosition, $yPosition, $newPath )
	{
		$sourceX = self::$positionValues[$xPosition];
		$sourceY = self::$positionValues[$yPosition];
		$zoomedImage = imagecreatetruecolor(256, 256);
		imagealphablending($zoomedImage, false);
		imagecopyresized($zoomedImage, $tileImage, 0, 0, $sourceX, $sourceY, 256, 256, 128, 128);
		imagesavealpha($zoomedImage, true);
		list($newX, $newY) = self::getNewCoordinates( $xCoordinate, $yCoordinate, $xPosition, $yPosition);

		//save the new, zoomed imaged
		imagepng($zoomedImage, "{$newPath}/{$newY}_{$newX}.png");

	}

	/**
	 * Calculates the new x and y coordinates for the given x and y position
	 *
	 * @param integer $xCoordinate
	 * @param integer $yCoordinate
	 * @param integer $xPosition
	 * @param integer $yPosition
	 *
	 * @return array New x coordinate at index 0, and new y coordinate at index 1
	 */
	static private function getNewCoordinates( $xCoordinate, $yCoordinate, $xPosition, $yPosition)
	{
		$newX = (2 * $yCoordinate) + ($xPosition === self::LEFT ? 0 : 1);
		$newY = (2 * $xCoordinate) + ($yPosition === self::TOP ? 0 : 1);
		return [$newX, $newY];
	}


	/**
	 *
	 * Create a 4 images from the given file name for the target zoom level and save them to the correct path
	 *
	 * @param $fileName
	 * @param $targetZoomLevel
	 * @param $basePath
	 */
	static private function createZoomedTilesFromFile( $fileName, $targetZoomLevel, $basePath)
	{


		if ( substr_count($fileName,".png") == 1) {

			// Split the filename into the constituent x and y parts.
			list($head, $tail) = explode(".", $fileName);
			list($x, $y) = explode("_", $head);
			echo "Z{$targetZoomLevel} {$x} {$y}\n";

			$newPath = "{$basePath}/Z{$targetZoomLevel}";
			if( ! is_dir($newPath) )
			{
				mkdir($newPath);
			}

			$imageFromFile = imagecreatefrompng($fileName);

			foreach(self::$xPositions as $xPosition)
			{
				foreach(self::$yPositions as $yPosition)
				{
					self::createZoomedTilesFromImage($imageFromFile, $x, $y, $xPosition, $yPosition, $newPath);
				}
			}

			// purge the images from memory.  php will thank you.
			$imageFromFile = NULL;
			unset($imageFromFile);

		}
	}

	static private function removeBlankTilesFromPath( $path )
	{
		$filesInCurrentPath = scandir($path);
		foreach ($filesInCurrentPath as $thisTile)
		{
			if (substr_count($thisTile,".png") === 1 && filesize($thisTile) === 334)
			{
				// The size of a blank tile created by gdal2tile.py is 334 bytes.
				//  So we can easily delete the numerous blank tiles generated in each Z level.
				unlink($thisTile);
			}
		}
	}
}




