<?php
/**
 * GN SCALES — SOCIAL SHARE IMAGE
 *
 * Draws the 1200x630 card that Slack, WhatsApp, LinkedIn, iMessage and every
 * email preview render when somebody pastes a link. A design studio whose link
 * preview is a blank grey box is arguing against itself, and cold outreach that
 * leads with the URL is exactly where that box shows up.
 *
 * Regenerated on every build, so the card cannot drift from the tagline.
 * Requires the GD extension, which is on essentially every cPanel host; where
 * it is missing the build keeps whatever image is already there.
 */

define('GNS_OG_W', 1200);
define('GNS_OG_H', 630);

function gns_og_available()
{
    return function_exists('imagecreatetruecolor')
        && function_exists('imagettftext')
        && function_exists('imagejpeg');
}

function gns_og_font($file)
{
    return GNS_ADMIN . '/assets/fonts/' . $file;
}

/**
 * Render one card. $title is set in the display serif, $kicker and $foot in
 * the UI face. Returns true on success.
 */
function gns_og_render($path, $title, $kicker, $foot)
{
    if (!gns_og_available()) {
        return false;
    }
    $serif = gns_og_font('InstrumentSerif-Regular.ttf');
    $sans  = gns_og_font('SchibstedGrotesk-Variable.ttf');
    if (!is_file($serif) || !is_file($sans)) {
        return false;
    }

    $im = imagecreatetruecolor(GNS_OG_W, GNS_OG_H);
    imageantialias($im, true);

    $navy   = array(0, 8, 26);
    $gold   = imagecolorallocate($im, 210, 173, 92);
    $goldHi = imagecolorallocate($im, 247, 231, 190);
    $ink    = imagecolorallocate($im, 237, 230, 211);
    $ink3   = imagecolorallocate($im, 150, 147, 137);

    gns_og_ground($im);

    // Hairline frame
    imagesetthickness($im, 1);
    imagerectangle($im, 44, 44, GNS_OG_W - 45, GNS_OG_H - 45, imagecolorallocatealpha($im, 210, 173, 92, 96));

    // Lockup: the mark from the same path data the header uses, then the
    // wordmark set the way it is set on the site.
    gns_og_mark($im, 96, 86, 92, $gold);
    imagettftext($im, 38, 0, 176, 136, $gold, $serif, 'GN');
    gns_og_tracked($im, 'SCALES', 13, 179, 164, $gold, $sans, 7.0);

    // Kicker
    if ($kicker !== '') {
        gns_og_tracked($im, mb_strtoupper($kicker, 'UTF-8'), 14, 96, 300, $ink3, $sans, 2.6);
    }

    // Headline, wrapped by measured width rather than by character count.
    $lines = array_slice(gns_og_wrap($title, $serif, 64, GNS_OG_W - 300), 0, 3);
    $y = 400 - (count($lines) - 1) * 40;
    foreach ($lines as $i => $line) {
        imagettftext($im, 64, 0, 96, $y, $i === 0 ? $ink : $goldHi, $serif, $line);
        $y += 80;
    }

    // Rule and footer line
    imagefilledrectangle($im, 96, GNS_OG_H - 122, 156, GNS_OG_H - 121, $gold);
    if ($foot !== '') {
        imagettftext($im, 15, 0, 96, GNS_OG_H - 78, $ink3, $sans, $foot);
    }

    $ok = imagejpeg($im, $path, 86);
    imagedestroy($im);
    return $ok;
}

/**
 * The navy ground with two drifting light bodies, warm gold and cool steel —
 * the same idea as the hero shader, in a still.
 *
 * Computed per pixel at one eighth scale and then resampled up. A true
 * 1200x630 loop in PHP is 756,000 iterations for something that is going to be
 * blurry anyway, and drawing it as concentric circles instead produces visible
 * banding. Small-and-smooth costs about a hundredth as much and looks better.
 */
function gns_og_ground($im)
{
    $sw = 150;
    $sh = 79;
    $small = imagecreatetruecolor($sw, $sh);

    for ($y = 0; $y < $sh; $y++) {
        for ($x = 0; $x < $sw; $x++) {
            // Normalised, aspect-corrected coordinates centred on the canvas.
            $px = ($x / $sw - 0.5) * (GNS_OG_W / GNS_OG_H);
            $py = $y / $sh - 0.5;

            $g1 = exp(-2.6 * hypot($px + 0.52, $py + 0.20));   // warm, upper left
            $g2 = exp(-3.0 * hypot($px - 0.60, $py - 0.22));   // cool, lower right
            $g3 = exp(-3.4 * hypot($px - 0.05, $py + 0.62));   // warm, top centre

            $warm = $g1 * 0.78 + $g3 * 0.34;
            $cool = $g2 * 0.46;
            $r = 0 + 210 * $warm + 103 * $cool;
            $g = 8 + 173 * $warm + 133 * $cool;
            $b = 26 + 92 * $warm + 196 * $cool;

            // Vignette, then a scrim across the left two thirds so the
            // headline always has something dark to sit on.
            $v = 1.0 - 0.42 * pow(hypot($px * 0.8, $py), 1.8);
            $scrim = 1.0 - 0.46 * max(0.0, 1.0 - max(0.0, ($x / $sw - 0.10)) / 0.66);

            $r = (int)max(0, min(255, $r * $v * $scrim));
            $g = (int)max(0, min(255, $g * $v * $scrim));
            $b = (int)max(0, min(255, $b * $v * $scrim));
            imagesetpixel($small, $x, $y, imagecolorallocate($small, $r, $g, $b));
        }
    }

    imagecopyresampled($im, $small, 0, 0, 0, 0, GNS_OG_W, GNS_OG_H, $sw, $sh);
    imagedestroy($small);
}

/**
 * Letter-spaced text. GD has no tracking control, so each glyph is placed
 * individually — which the wordmark and the uppercase mono labels need.
 */
function gns_og_tracked($im, $text, $size, $x, $y, $colour, $font, $tracking)
{
    $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($chars as $char) {
        imagettftext($im, $size, 0, (int)round($x), $y, $colour, $font, $char);
        $box = imagettfbbox($size, 0, $font, $char);
        $x += abs($box[2] - $box[0]) + $tracking;
        if ($char === ' ') {
            $x += $size * 0.3;
        }
    }
}

/**
 * The chess-queen mark. The SVG paths are mostly straight lines; the one
 * curved path is approximated with a polygon, which at this size is
 * indistinguishable and avoids shipping an SVG rasteriser.
 */
function gns_og_mark($im, $x, $y, $size, $colour)
{
    // Source viewBox is "19 0 42 93": 42 wide, 93 tall.
    $s = $size / 93;
    $ox = $x - 19 * $s;

    $poly = function (array $pts) use ($im, $colour, $ox, $y, $s) {
        $flat = array();
        foreach ($pts as $p) {
            $flat[] = (int)round($ox + $p[0] * $s);
            $flat[] = (int)round($y + $p[1] * $s);
        }
        imagefilledpolygon($im, $flat, $colour);
    };

    $poly(array(array(37.4,1),array(42.6,1),array(42.6,4.6),array(48.6,4.6),array(48.6,8.8),array(42.6,8.8),array(42.6,13.2),array(37.4,13.2),array(37.4,8.8),array(31.4,8.8),array(31.4,4.6),array(37.4,4.6)));
    $poly(array(array(23,17),array(28,19),array(31,23.6),array(33,17.5),array(36.5,14.1),array(40,14.1),array(43.5,14.1),array(47,17.5),array(49,23.6),array(52,19),array(57,17),array(55.5,22),array(53,25.6),array(52,30),array(28,30),array(27,25.6),array(24.5,22)));
    $poly(array(array(28,32),array(52,32),array(52,35.6),array(28,35.6)));
    $poly(array(array(29.6,37.4),array(50.4,37.4),array(50.4,40.4),array(29.6,40.4)));
    $poly(array(array(31.8,42.4),array(48.2,42.4),array(48.2,55),array(50.6,63.6),array(55,69),array(25,69),array(29.4,63.6),array(31.8,55)));
    $poly(array(array(26.4,71),array(53.6,71),array(57.2,82.4),array(22.8,82.4)));
    $poly(array(array(20.6,85),array(59.4,85),array(59.4,91.4),array(20.6,91.4)));
}

/** Greedy wrap using the real rendered width of each candidate line. */
function gns_og_wrap($text, $font, $size, $maxWidth)
{
    $words = preg_split('/\s+/', trim($text));
    $lines = array();
    $line = '';
    foreach ($words as $word) {
        $try = $line === '' ? $word : $line . ' ' . $word;
        $box = imagettfbbox($size, 0, $font, $try);
        $width = abs($box[2] - $box[0]);
        if ($width > $maxWidth && $line !== '') {
            $lines[] = $line;
            $line = $word;
        } else {
            $line = $try;
        }
    }
    if ($line !== '') {
        $lines[] = $line;
    }
    return $lines;
}

/**
 * Build the default card, plus one for the pricing page: pricing links get
 * pasted into internal threads at prospect companies more than home pages do.
 * Returns the list of files written.
 */
function gns_og_build(array $c)
{
    if (!gns_og_available()) {
        return array();
    }
    if (!is_dir(GNS_OG)) {
        @mkdir(GNS_OG, 0755, true);
    }

    $written = array();
    $cards = array(
        'og-default.jpg' => array(
            'title'  => 'Craft first. Then the numbers.',
            'kicker' => plain(gns_get($c, 'site.tagline', '')),
            'foot'   => 'Automotive aftermarket and jewelry · ' . preg_replace('#^https?://#', '', gns_site_url($c)),
        ),
        'og-pricing.jpg' => array(
            'title'  => 'Two tiers. Published.',
            'kicker' => 'Pricing',
            'foot'   => plain(gns_get($c, 'site.price_range', '')) . ' · month to month, no markup on ad spend',
        ),
    );

    foreach ($cards as $file => $card) {
        if (gns_og_render(GNS_OG . '/' . $file, $card['title'], $card['kicker'], $card['foot'])) {
            $written[] = 'assets/og/' . $file;
        }
    }
    return $written;
}

/* ---------------------------------------------------------------
   Favicons
   --------------------------------------------------------------- */

/** favicon.svg, straight from the same path data the header renders. */
function gns_favicon_svg()
{
    $paths = '';
    foreach (gns_brand_paths() as $d) {
        $paths .= '  <path d="' . $d . '"/>' . "\n";
    }
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="14 -4 52 101">' . "\n"
         . '  <rect x="14" y="-4" width="52" height="101" fill="#00081a"/>' . "\n"
         . '  <g fill="#d2ad5c">' . "\n" . $paths . '  </g>' . "\n"
         . "</svg>\n";
}

/** A square PNG of the mark on navy, at whatever size is asked for. */
function gns_favicon_png($size)
{
    if (!gns_og_available()) {
        return null;
    }
    $im = imagecreatetruecolor($size, $size);
    imagealphablending($im, true);
    imageantialias($im, true);
    imagefilledrectangle($im, 0, 0, $size, $size, imagecolorallocate($im, 0, 8, 26));

    // Supersample: draw at 4x and scale down, because GD has no antialiasing
    // on filled polygons and a hard-edged 32px mark looks broken.
    $scale = 4;
    $big = imagecreatetruecolor($size * $scale, $size * $scale);
    imageantialias($big, true);
    imagefilledrectangle($big, 0, 0, $size * $scale, $size * $scale, imagecolorallocate($big, 0, 8, 26));
    $gold = imagecolorallocate($big, 210, 173, 92);

    $mark = (int)round($size * $scale * 0.74);
    gns_og_mark($big, (int)round($size * $scale * 0.5 - ($mark * 42 / 93) / 2), (int)round($size * $scale * 0.13), $mark, $gold);

    imagecopyresampled($im, $big, 0, 0, 0, 0, $size, $size, $size * $scale, $size * $scale);
    imagedestroy($big);
    return $im;
}

/**
 * Write the whole icon set. The .ico is assembled by hand — GD cannot produce
 * one, and it is only a 22-byte header plus a PNG.
 */
function gns_favicon_build()
{
    $written = array();

    if (gns_write_atomic(GNS_ROOT . '/favicon.svg', gns_favicon_svg())) {
        $written[] = 'favicon.svg';
    }
    if (!gns_og_available()) {
        return $written;
    }

    $sizes = array('apple-touch-icon.png' => 180, 'icon-192.png' => 192, 'icon-512.png' => 512);
    foreach ($sizes as $file => $size) {
        $im = gns_favicon_png($size);
        if (!$im) {
            continue;
        }
        if (@imagepng($im, GNS_ROOT . '/' . $file, 9)) {
            $written[] = $file;
        }
        imagedestroy($im);
    }

    $ico = gns_favicon_ico(32);
    if ($ico !== null && gns_write_atomic(GNS_ROOT . '/favicon.ico', $ico)) {
        $written[] = 'favicon.ico';
    }
    return $written;
}

/** A single-image .ico wrapping a PNG, which every browser since IE11 reads. */
function gns_favicon_ico($size)
{
    $im = gns_favicon_png($size);
    if (!$im) {
        return null;
    }
    ob_start();
    imagepng($im, null, 9);
    $png = ob_get_clean();
    imagedestroy($im);

    // ICONDIR: reserved, type 1 (icon), one image.
    $out = pack('vvv', 0, 1, 1);
    // ICONDIRENTRY: width, height (0 means 256), colours, reserved, planes,
    // bit depth, byte length, offset.
    $out .= pack('CCCCvvVV', $size % 256, $size % 256, 0, 0, 1, 32, strlen($png), 22);
    return $out . $png;
}
