<?php
/**
 * GN SCALES — REBUILD
 *
 * Regenerates every .html file, the sitemap, robots.txt, the manifest and the
 * custom stylesheet from the content tree.
 *
 * From the shell or a deploy hook:   php admin/rebuild.php
 * Over the web it requires a logged-in admin session.
 */

require __DIR__ . '/lib/boot.php';

$content = gns_content(true);
$result = gns_build($content, $errors);

if (GNS_IS_CLI) {
    foreach ($result['written'] as $file) {
        fwrite(STDOUT, "  wrote  " . $file . "\n");
    }
    foreach ($result['errors'] as $err) {
        fwrite(STDERR, "  ERROR  " . $err . "\n");
    }
    fwrite(STDOUT, sprintf(
        "\n%d file%s written%s\n",
        count($result['written']),
        count($result['written']) === 1 ? '' : 's',
        $result['errors'] ? ', ' . count($result['errors']) . ' failed' : ''
    ));
    exit($result['errors'] ? 1 : 0);
}

gns_require_login();
header('Content-Type: text/plain; charset=utf-8');
foreach ($result['written'] as $file) {
    echo "wrote  " . $file . "\n";
}
foreach ($result['errors'] as $err) {
    echo "ERROR  " . $err . "\n";
}
echo "\n" . count($result['written']) . " files written.\n";
