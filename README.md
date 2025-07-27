# Coneflower Utilities

A WordPress plugin that provides a set of useful utilities that should really be in Wordpress core but are not. Developed by Jeremy Mullis, Coneflower Consulting.

## Features

- **Image Upload Controls**

  - Set a maximum image size for uploads. Images larger than this will be scaled down automatically.
  - Set JPEG, WebP, and AVIF quality for generated images.
  - Optionally convert new JPEG uploads to WebP or AVIF (if supported by your server). No conversion is performed if neither format is supported.
  - The plugin automatically detects and warns if your server does not support WebP or AVIF image formats.

- **Comment Controls**

  - Option to disable all comments site-wide, both on the front-end and in the WordPress admin.

- **Security**
  - Option to force strong passwords by hiding the "confirm use of weak password" checkbox on login and user profile screens.

## Settings

All settings are available in the WordPress dashboard under **Settings > Coneflower Utilities**. You can:

- Set the maximum image size for uploads.
- Set the quality for JPEG, WebP, and AVIF images.
- Choose whether to convert new JPEG uploads to WebP, AVIF, or not convert at all.
- Enable or disable comments site-wide.
- Enable or disable the requirement for strong passwords.

## Requirements

- WordPress 5.8 or later recommended.
- PHP GD or ImageMagick extension for image conversion (WebP/AVIF support depends on your server's configuration).

## Author

Jeremy Mullis, Coneflower Consulting  
https://www.coneflower.org

## License

GPL2
