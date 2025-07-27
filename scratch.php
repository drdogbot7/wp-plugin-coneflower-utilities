// Convert original JPG to WebP before generating sizes, update metadata, and delete original JPG
function cfu_handle_jpg_upload($file) {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext === 'jpg' || $ext === 'jpeg') {
        $webp_path = preg_replace('/\.jpe?g$/i', '.webp', $file['tmp_name']);
        $webp_quality = get_option('cfu_webp_quality', 80);
        $converted = false;
        // Try GD first
        if (function_exists('imagecreatefromjpeg') && function_exists('imagewebp')) {
            $image = @imagecreatefromjpeg($file['tmp_name']);
            if ($image !== false) {
                if (imagewebp($image, $webp_path, $webp_quality)) {
                    imagedestroy($image);
                    $converted = true;
                } else {
                    imagedestroy($image);
                }
            }
        }
        // If GD failed, try ImageMagick
        if (!$converted && class_exists('Imagick')) {
            try {
                $im = new Imagick($file['tmp_name']);
                $im->setImageFormat('webp');
                $im->setImageCompressionQuality($webp_quality);
                if ($im->writeImage($webp_path)) {
                    $converted = true;
                }
                $im->clear();
                $im->destroy();
            } catch (Exception $e) {
                // Do nothing, will warn below if not converted
            }
        }
        if ($converted) {
            // Replace file info to point to WebP
            $file['tmp_name'] = $webp_path;
            $file['name'] = preg_replace('/\.jpe?g$/i', '.webp', $file['name']);
            $file['type'] = 'image/webp';
        } else {
            // Set a flag for admin notice
            update_option('cfu_webp_conversion_error', true);
        }
    }
    return $file;
}
// add_filter('wp_handle_upload_prefilter', 'cfu_handle_jpg_upload');

function cfu_update_metadata_for_webp($metadata, $attachment_id) {
    $file = get_attached_file($attachment_id);
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if ($ext === 'webp') {
        // Remove .jpg/.jpeg from metadata if present
        if (isset($metadata['file'])) {
            $metadata['file'] = preg_replace('/\.jpe?g$/i', '.webp', $metadata['file']);
        }
        if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size => &$sizeinfo) {
                if (isset($sizeinfo['file'])) {
                    $sizeinfo['file'] = preg_replace('/\.jpe?g$/i', '.webp', $sizeinfo['file']);
                }
            }
        }
        // Delete original JPG if it exists
        $jpg_path = preg_replace('/\.webp$/i', '.jpg', $file);
        $jpeg_path = preg_replace('/\.webp$/i', '.jpeg', $file);
        if (file_exists($jpg_path)) @unlink($jpg_path);
        if (file_exists($jpeg_path)) @unlink($jpeg_path);
    }
    return $metadata;
}
// add_filter('wp_generate_attachment_metadata', 'cfu_update_metadata_for_webp', 10, 2);