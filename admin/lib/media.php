<?php
/**
 * GN SCALES — MEDIA LIBRARY
 *
 * Uploads land in assets/uploads and are referenced by path. Validation is by
 * actual image content rather than by filename, because a file called
 * portrait.jpg is not necessarily a JPEG.
 */

function gns_media_types()
{
    return array(
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_GIF  => 'gif',
        IMAGETYPE_WEBP => 'webp',
    );
}

function gns_max_upload()
{
    $limits = array(8 * 1024 * 1024);
    foreach (array(ini_get('upload_max_filesize'), ini_get('post_max_size')) as $value) {
        $bytes = gns_ini_bytes($value);
        if ($bytes > 0) {
            $limits[] = $bytes;
        }
    }
    return min($limits);
}

function gns_ini_bytes($value)
{
    $value = trim((string)$value);
    if ($value === '') {
        return 0;
    }
    $unit = strtolower(substr($value, -1));
    $num = (int)$value;
    if ($unit === 'g') { return $num * 1024 * 1024 * 1024; }
    if ($unit === 'm') { return $num * 1024 * 1024; }
    if ($unit === 'k') { return $num * 1024; }
    return $num;
}

function gns_bytes($n)
{
    $n = (float)$n;
    if ($n >= 1048576) { return round($n / 1048576, 1) . ' MB'; }
    if ($n >= 1024)    { return round($n / 1024) . ' KB'; }
    return (int)$n . ' B';
}

/** Everything currently in assets/uploads, newest first. */
function gns_media_list()
{
    $out = array();
    foreach (glob(GNS_UPLOADS . '/*') ?: array() as $path) {
        if (!is_file($path) || basename($path)[0] === '.') {
            continue;
        }
        $name = basename($path);
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $w = $h = '?';
        if ($ext !== 'svg') {
            $size = @getimagesize($path);
            if ($size) {
                $w = $size[0];
                $h = $size[1];
            }
        } else {
            $w = $h = 'svg';
        }
        $out[] = array(
            'name' => $name,
            'url'  => '/assets/uploads/' . rawurlencode($name),
            'size' => filesize($path),
            'time' => filemtime($path),
            'w'    => $w,
            'h'    => $h,
        );
    }
    usort($out, function ($a, $b) {
        return $b['time'] <=> $a['time'];
    });
    return $out;
}

/**
 * Accept one upload. Returns the stored file's details, or a string describing
 * why it was refused.
 */
function gns_media_store(array $file)
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return gns_upload_error(isset($file['error']) ? $file['error'] : -1);
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return 'That upload did not arrive properly.';
    }
    if ($file['size'] > gns_max_upload()) {
        return 'That file is larger than ' . gns_bytes(gns_max_upload()) . '.';
    }

    $original = (string)$file['name'];
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));

    if ($ext === 'svg') {
        // SVG is XML, so it can carry script. Only accept one that survives a
        // strict scrub, and store the scrubbed version rather than the upload.
        $svg = gns_clean_svg(file_get_contents($file['tmp_name']));
        if ($svg === null) {
            return 'That SVG contains script or external references, so it was refused.';
        }
        $name = gns_media_name($original, 'svg');
        if (!gns_write_atomic(GNS_UPLOADS . '/' . $name, $svg)) {
            return 'Could not write to assets/uploads. Check the directory is writable.';
        }
    } else {
        // Validated by content, not by filename.
        $info = @getimagesize($file['tmp_name']);
        $types = gns_media_types();
        if (!$info || !isset($types[$info[2]])) {
            return 'That is not an image this site can use. Try JPG, PNG, WebP, GIF or SVG.';
        }
        $name = gns_media_name($original, $types[$info[2]]);
        if (!@move_uploaded_file($file['tmp_name'], GNS_UPLOADS . '/' . $name)) {
            return 'Could not write to assets/uploads. Check the directory is writable.';
        }
        @chmod(GNS_UPLOADS . '/' . $name, 0644);
    }

    gns_log('media.upload', $name);
    return array(
        'name' => $name,
        'url'  => '/assets/uploads/' . rawurlencode($name),
        'size' => filesize(GNS_UPLOADS . '/' . $name),
    );
}

/** A safe, unique filename derived from what was uploaded. */
function gns_media_name($original, $ext)
{
    $stem = pathinfo($original, PATHINFO_FILENAME);
    $stem = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $stem));
    $stem = trim($stem, '-');
    if ($stem === '') {
        $stem = 'image';
    }
    $stem = substr($stem, 0, 48);

    $name = $stem . '.' . $ext;
    $n = 2;
    while (file_exists(GNS_UPLOADS . '/' . $name)) {
        $name = $stem . '-' . $n . '.' . $ext;
        $n++;
    }
    return $name;
}

/**
 * Strip an SVG down to drawing instructions. Returns null if anything
 * executable or remote is present rather than trying to repair it — a broken
 * logo is a better outcome than a stored cross-site script.
 */
function gns_clean_svg($svg)
{
    if (!is_string($svg) || stripos($svg, '<svg') === false) {
        return null;
    }
    $lower = strtolower($svg);
    foreach (array('<script', '<foreignobject', '<iframe', '<embed', '<object', 'javascript:', '<!entity', '<!doctype svg system') as $bad) {
        if (strpos($lower, $bad) !== false) {
            return null;
        }
    }
    if (preg_match('/\son[a-z]+\s*=/i', $svg)) {
        return null;   // inline event handler
    }
    if (preg_match('/(href|src)\s*=\s*["\']\s*(https?:)?\/\//i', $svg)) {
        return null;   // pulls in something remote
    }
    return $svg;
}

function gns_media_delete($name)
{
    $name = basename((string)$name);
    $path = GNS_UPLOADS . '/' . $name;
    if ($name === '' || !is_file($path)) {
        return false;
    }
    if (@unlink($path)) {
        gns_log('media.delete', $name);
        return true;
    }
    return false;
}

function gns_upload_error($code)
{
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'That file is larger than this server accepts (' . gns_bytes(gns_max_upload()) . ').';
        case UPLOAD_ERR_PARTIAL:
            return 'The upload was cut off. Try again.';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was selected.';
        case UPLOAD_ERR_NO_TMP_DIR:
        case UPLOAD_ERR_CANT_WRITE:
            return 'The server could not write the temporary file.';
        default:
            return 'That upload failed.';
    }
}
