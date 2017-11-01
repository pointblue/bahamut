# Retile Example

Call the retile script with the path of the JSON file that has the script arguments.
For an example of the JSON file, see `glc.json` in this path.

Command line example: `me@computer:~/bahamut/examples/retile$ retile glc.json`

This examples assumes that you've soft linked 

## JSON file

The JSON file has four property, described here.

  - `baseTilePath` - The absolute path that has the directories that will be retiled.
  It should not end with a `/` character.
  - `retilePaths` - An array of paths that will be retiled. Each path should be relative
  to `baseTilePath`, and contain zoom directories prefixed with `Z`. The paths should not
  end with a `/` character.
  - `startZoomLevel` - An integer representing the first zoom level that will be retiled.
  - `endZoomLevel` - The last zoom level that will be created.
  
Example:

```
{
  "baseTilePath":"/var/www/html/bahamut/examples/retile",
  "retilePaths":[
    "glc"
  ],
  "startZoomLevel":11,
  "endZoomLevel":12
}
```

This JSON file will retile `/var/www/html/bahamut/examples/retile/glc/Z11`, which
will create a folder `/var/www/html/bahamut/examples/retile/glc/Z12` with the new
tiles. The new `Z12` folder will not be retiled because it is the `endZoomLevel`.