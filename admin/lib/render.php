<?php
/**
 * GN SCALES — STATIC SITE RENDERER
 *
 * Turns the content tree plus the templates in admin/templates into the plain
 * .html files that Apache serves. The public site stays static on purpose: it
 * is the fastest thing we can ship, it matches what we sell, and if PHP or the
 * admin ever falls over the website carries on serving.
 *
 * Run it from the admin, or from the shell / a deploy hook:
 *     php admin/rebuild.php
 */

/** Every page the renderer owns: content key => output file + template. */
function gns_pages()
{
    return array(
        'home'     => array('file' => 'index.html',   'tpl' => 'page-home.php',     'css' => 'index.css',    'canonical' => '/',              'index' => true,  'priority' => '1.0'),
        'services' => array('file' => 'services.html','tpl' => 'page-services.php', 'css' => 'services.css', 'canonical' => '/services.html', 'index' => true,  'priority' => '0.9'),
        'pricing'  => array('file' => 'pricing.html', 'tpl' => 'page-pricing.php',  'css' => 'pricing.css',  'canonical' => '/pricing.html',  'index' => true,  'priority' => '0.9'),
        'work'     => array('file' => 'work.html',    'tpl' => 'page-work.php',     'css' => 'work.css',     'canonical' => '/work.html',     'index' => true,  'priority' => '0.8'),
        'about'    => array('file' => 'about.html',   'tpl' => 'page-about.php',    'css' => 'about.css',    'canonical' => '/about.html',    'index' => true,  'priority' => '0.7'),
        'contact'  => array('file' => 'contact.html', 'tpl' => 'page-contact.php',  'css' => 'contact.css',  'canonical' => '/contact.html',  'index' => true,  'priority' => '0.8'),
        'thanks'   => array('file' => 'thanks.html',  'tpl' => 'page-thanks.php',   'css' => 'legal.css',    'canonical' => '/thanks.html',   'index' => false, 'priority' => null),
        'privacy'  => array('file' => 'privacy.html', 'tpl' => 'page-legal.php',    'css' => 'legal.css',    'canonical' => '/privacy.html',  'index' => true,  'priority' => '0.3'),
        'terms'    => array('file' => 'terms.html',   'tpl' => 'page-legal.php',    'css' => 'legal.css',    'canonical' => '/terms.html',    'index' => true,  'priority' => '0.3'),
        'notfound' => array('file' => '404.html',     'tpl' => 'page-404.php',      'css' => 'legal.css',    'canonical' => null,             'index' => false, 'priority' => null),
    );
}

/**
 * Append a short content hash to an asset URL.
 *
 * .htaccess tells browsers to cache CSS, JS and fonts for a year. Without this
 * a returning visitor would keep the old stylesheet after a redesign, so the
 * URL has to change when the bytes do.
 */
function gns_asset($path)
{
    static $cache = array();
    $rel = ltrim($path, '/');
    if (!isset($cache[$rel])) {
        $full = GNS_ROOT . '/' . $rel;
        $cache[$rel] = is_file($full) ? substr(md5_file($full), 0, 8) : '';
    }
    return '/' . $rel . ($cache[$rel] === '' ? '' : '?v=' . $cache[$rel]);
}

/**
 * Normalise an internal link to a root-relative one.
 *
 * Two reasons. 404.html is served at whatever URL the visitor mistyped, so a
 * relative href there would resolve against a directory that does not exist.
 * And it settles the duplicate-content question the old markup created, where
 * every nav link pointed at /index.html while the outside world linked to /.
 */
function gns_href($href)
{
    $href = trim((string)$href);
    if ($href === '') {
        return '/';
    }
    if ($href === '/' || $href[0] === '/' || $href[0] === '#'
        || preg_match('#^(https?:|mailto:|tel:)#i', $href)) {
        return $href;
    }
    if (strpos($href, 'index.html') === 0) {
        return '/' . substr($href, strlen('index.html'));
    }
    return '/' . $href;
}

/** Absolute URL for a site-relative path. */
function gns_abs($c, $path)
{
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return gns_site_url($c) . '/' . ltrim($path, '/');
}

/**
 * Build the per-page view model the templates and partials read from. Doing it
 * here means titles, canonicals and social tags can never drift page to page.
 */
function gns_page_meta($key, array $c)
{
    $pages = gns_pages();
    $reg = $pages[$key];
    $page = gns_get($c, 'pages.' . $key, array());

    $ogImage = gns_get($page, 'og_image', '');
    if ($ogImage === '') {
        $ogImage = gns_get($c, 'site.og_image', '/assets/og/og-default.jpg');
    }

    return array(
        'key'         => $key,
        'file'        => $reg['file'],
        'css'         => $reg['css'],
        'index'       => $reg['index'],
        'canonical'   => $reg['canonical'] === null ? null : gns_site_url($c) . $reg['canonical'],
        'title'       => plain(gns_get($page, 'title', gns_get($c, 'site.name', 'GN Scales'))),
        'description' => plain(gns_get($page, 'description', gns_get($c, 'site.blurb', ''))),
        'og_title'    => plain(gns_get($page, 'og_title', gns_get($page, 'title', ''))),
        'og_image'    => gns_abs($c, $ogImage),
    );
}

/** Render one page to a string. */
function gns_render_page($key, array $c)
{
    $pages = gns_pages();
    if (!isset($pages[$key])) {
        throw new RuntimeException('Unknown page: ' . $key);
    }
    $reg = $pages[$key];
    $meta = gns_page_meta($key, $c);
    $page = gns_get($c, 'pages.' . $key, array());

    ob_start();
    include GNS_TEMPLATES . '/' . $reg['tpl'];
    return gns_tidy(ob_get_clean());
}

/** Collapse the blank lines that PHP's own tags leave behind. */
function gns_tidy($html)
{
    $html = preg_replace("/\r\n?/", "\n", $html);
    $html = preg_replace("/[ \t]+\n/", "\n", $html);
    $html = preg_replace("/\n{3,}/", "\n\n", $html);
    return rtrim($html) . "\n";
}

/* ---------------------------------------------------------------
   Whole-site build
   --------------------------------------------------------------- */

/**
 * Regenerate every public file. Pages are rendered into memory first: if one
 * template throws, nothing is written and the live site is untouched.
 */
function gns_build(array $c = null, &$errors = null)
{
    $errors = array();
    if ($c === null) {
        $c = gns_content(true);
    }

    $rendered = array();
    foreach (gns_pages() as $key => $reg) {
        try {
            $rendered[$reg['file']] = gns_render_page($key, $c);
        } catch (Throwable $err) {
            $errors[] = $reg['file'] . ': ' . $err->getMessage();
        }
    }
    if ($errors) {
        return array('written' => array(), 'errors' => $errors);
    }

    // Icons and share cards first: the pages reference them with a content
    // hash, so they have to exist before the HTML is written.
    $assets = array_merge(gns_favicon_build(), gns_og_build($c));

    $rendered['sitemap.xml']     = gns_build_sitemap($c);
    $rendered['robots.txt']      = gns_build_robots($c);
    $rendered['site.webmanifest']= gns_build_manifest($c);
    $rendered['assets/site.css'] = gns_build_custom_css($c);

    $written = array();
    foreach ($rendered as $file => $body) {
        $path = GNS_ROOT . '/' . $file;
        if (gns_write_atomic($path, $body)) {
            $written[] = $file;
        } else {
            $errors[] = 'Could not write ' . $file . ' — check file permissions.';
        }
    }

    $written = array_merge($assets, $written);
    if (!gns_og_available()) {
        $errors[] = 'PHP has no GD extension, so icons and social share images were not regenerated. '
            . 'Enable gd in cPanel under "Select PHP Version" if you want them rebuilt here.';
    }

    gns_log('build', count($written) . ' files' . ($errors ? ', ' . count($errors) . ' errors' : ''));
    return array('written' => $written, 'errors' => $errors);
}

function gns_build_sitemap(array $c)
{
    $base = gns_site_url($c);
    $out = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
         . "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach (gns_pages() as $key => $reg) {
        if (!$reg['index'] || $reg['priority'] === null) {
            continue;
        }
        $out .= '  <url><loc>' . e($base . $reg['canonical']) . '</loc>'
              . '<priority>' . $reg['priority'] . "</priority></url>\n";
    }
    return $out . "</urlset>\n";
}

function gns_build_robots(array $c)
{
    return implode("\n", array(
        'User-agent: *',
        'Allow: /',
        '',
        '# The admin is ours, not Google\'s. It is also password protected —',
        '# this line is tidiness, not a security control.',
        'Disallow: /admin/',
        'Disallow: /data/',
        'Disallow: /thanks.html',
        '',
        'Sitemap: ' . gns_site_url($c) . '/sitemap.xml',
        '',
    ));
}

function gns_build_manifest(array $c)
{
    $data = array(
        'name'             => plain(gns_get($c, 'site.name', 'GN Scales')),
        'short_name'       => plain(gns_get($c, 'site.name', 'GN Scales')),
        'description'      => plain(gns_get($c, 'site.blurb', '')),
        'start_url'        => '/',
        'display'          => 'standalone',
        'background_color' => gns_get($c, 'site.theme_color', '#00081a'),
        'theme_color'      => gns_get($c, 'site.theme_color', '#00081a'),
        'icons' => array(
            array('src' => '/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'),
            array('src' => '/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png'),
            array('src' => '/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'),
        ),
    );
    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
}

/** The "custom CSS" escape hatch, written out as a real cacheable file. */
function gns_build_custom_css(array $c)
{
    $css = trim((string)gns_get($c, 'custom.css', ''));
    $head = "/* GN Scales — custom CSS, edited in the admin under Advanced.\n"
          . "   Loaded last on every page, so anything in here overrides the\n"
          . "   design system. Generated file: edit it in /admin, not by hand. */\n";
    if ($css === '') {
        return $head . "\n";
    }
    // Neutralise the one construct that could pull in a third-party origin.
    $css = preg_replace('/@import[^;]*;/i', '', $css);
    return $head . "\n" . $css . "\n";
}

/* ---------------------------------------------------------------
   Head fragments shared by every template
   --------------------------------------------------------------- */

/** Analytics and pixel tags. Each is emitted only when its ID is filled in. */
function gns_tracking_head(array $c)
{
    $i = gns_get($c, 'integrations', array());
    $consent = !empty($i['consent_banner']);
    $out = '';

    $ga = trim((string)gns_get($i, 'ga4_id', ''));
    $ads = trim((string)gns_get($i, 'google_ads_id', ''));
    $pixel = trim((string)gns_get($i, 'meta_pixel_id', ''));
    $clarity = trim((string)gns_get($i, 'clarity_id', ''));

    if ($ga === '' && $ads === '' && $pixel === '' && $clarity === '') {
        return '';
    }

    $out .= "\n  <!-- Measurement. IDs are set in the admin under Integrations. -->\n";
    if ($consent) {
        $out .= "  <script>window.gnsConsent=(function(){try{return localStorage.getItem('gns-consent')==='yes';}catch(e){return false;}})();</script>\n";
    } else {
        $out .= "  <script>window.gnsConsent=true;</script>\n";
    }

    if ($ga !== '' || $ads !== '') {
        $primary = $ga !== '' ? $ga : $ads;
        $out .= '  <script async src="https://www.googletagmanager.com/gtag/js?id=' . e($primary) . "\"></script>\n";
        $out .= "  <script>\n"
              . "    window.dataLayer = window.dataLayer || [];\n"
              . "    function gtag(){dataLayer.push(arguments);}\n"
              . "    gtag('js', new Date());\n"
              . "    gtag('consent', 'default', { ad_storage: window.gnsConsent ? 'granted' : 'denied', analytics_storage: window.gnsConsent ? 'granted' : 'denied', ad_user_data: window.gnsConsent ? 'granted' : 'denied', ad_personalization: window.gnsConsent ? 'granted' : 'denied' });\n";
        if ($ga !== '') {
            $out .= "    gtag('config', '" . e($ga) . "');\n";
        }
        if ($ads !== '') {
            $out .= "    gtag('config', '" . e($ads) . "');\n";
        }
        $out .= "  </script>\n";
    }

    if ($pixel !== '') {
        $out .= "  <script>\n"
              . "    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?\n"
              . "    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;\n"
              . "    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;\n"
              . "    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,\n"
              . "    document,'script','https://connect.facebook.net/en_US/fbevents.js');\n"
              . "    if (window.gnsConsent) { fbq('init', '" . e($pixel) . "'); fbq('track', 'PageView'); }\n"
              . "  </script>\n"
              . '  <noscript><img height="1" width="1" style="display:none" alt="'
              . '" src="https://www.facebook.com/tr?id=' . e($pixel) . "&ev=PageView&noscript=1\"></noscript>\n";
    }

    if ($clarity !== '') {
        $out .= "  <script>\n"
              . "    if (window.gnsConsent) { (function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};\n"
              . "    t=l.createElement(r);t.async=1;t.src='https://www.clarity.ms/tag/'+i;\n"
              . "    y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y)})(window,document,'clarity','script','" . e($clarity) . "'); }\n"
              . "  </script>\n";
    }

    return $out;
}

/** A short, human-readable list of the tags that are live, for the privacy page. */
function gns_tracking_summary(array $c)
{
    $i = gns_get($c, 'integrations', array());
    $names = array();
    if (trim((string)gns_get($i, 'ga4_id', '')) !== '')        { $names[] = 'Google Analytics 4 (Google LLC), for aggregate traffic measurement'; }
    if (trim((string)gns_get($i, 'google_ads_id', '')) !== '') { $names[] = 'Google Ads conversion tracking (Google LLC)'; }
    if (trim((string)gns_get($i, 'meta_pixel_id', '')) !== '') { $names[] = 'Meta Pixel (Meta Platforms, Inc.), for advertising measurement and audiences'; }
    if (trim((string)gns_get($i, 'clarity_id', '')) !== '')    { $names[] = 'Microsoft Clarity (Microsoft Corporation), for anonymised session playback'; }

    if (!$names) {
        return 'No analytics or advertising tags are running on this site at the moment. '
             . 'If that changes, this section will name every provider before the tag goes live.';
    }
    return 'The following are running on this site: ' . implode('; ', $names) . '. '
         . 'Each provider sets its own cookies and receives the data its tag collects.';
}

/** Structured data. Generated from content so it can never disagree with the page. */
function gns_jsonld($key, array $c)
{
    $blocks = array();
    $base = gns_site_url($c);
    $page = gns_get($c, 'pages.' . $key, array());

    if ($key === 'home') {
        $founders = array();
        foreach (gns_get($c, 'founders', array()) as $f) {
            $founders[] = array(
                '@type'    => 'Person',
                'name'     => plain($f['name']),
                'jobTitle' => plain($f['role_full']),
            );
        }
        $org = array(
            '@context'    => 'https://schema.org',
            '@type'       => 'ProfessionalService',
            '@id'         => $base . '/#studio',
            'name'        => plain(gns_get($c, 'site.name', 'GN Scales')),
            'url'         => $base . '/',
            'logo'        => $base . '/favicon.svg',
            'image'       => gns_abs($c, gns_get($c, 'site.og_image', '/assets/og/og-default.jpg')),
            'description' => plain(gns_get($c, 'site.blurb', '')),
            'areaServed'  => array('@type' => 'Country', 'name' => 'United States'),
            'priceRange'  => plain(gns_get($c, 'site.price_range', '')),
            'email'       => plain(gns_get($c, 'site.email', '')),
            'founder'     => $founders,
            'knowsAbout'  => array(
                'Meta Ads', 'Google Ads', 'TikTok Ads', 'landing page design',
                'paint protection film marketing', 'ceramic coating marketing',
                'vehicle wrap marketing', 'jewelry marketing',
            ),
        );
        $phone = trim((string)gns_get($c, 'site.phone', ''));
        if ($phone !== '') {
            $org['telephone'] = $phone;
        }
        $social = array_values(array_filter(array_map('trim', (array)gns_get($c, 'site.social', array()))));
        if ($social) {
            $org['sameAs'] = $social;
        }
        $blocks[] = $org;
    }

    $faq = gns_get($page, 'faq.items', array());
    if ($faq) {
        $items = array();
        foreach ($faq as $qa) {
            $items[] = array(
                '@type' => 'Question',
                'name'  => plain($qa['q']),
                'acceptedAnswer' => array('@type' => 'Answer', 'text' => plain($qa['a'])),
            );
        }
        $blocks[] = array(
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $items,
        );
    }

    if ($key === 'pricing') {
        $offers = array();
        foreach (gns_get($page, 'tiers', array()) as $tier) {
            $offers[] = array(
                '@type'         => 'Offer',
                'name'          => plain($tier['name']),
                'description'   => plain($tier['for']),
                'price'         => (string)(int)$tier['price'],
                'priceCurrency' => 'USD',
                'url'           => $base . '/pricing.html',
                'availability'  => 'https://schema.org/InStock',
                'category'      => 'Monthly retainer',
            );
        }
        if ($offers) {
            $blocks[] = array(
                '@context'    => 'https://schema.org',
                '@type'       => 'Service',
                'name'        => 'Design-led performance marketing retainer',
                'provider'    => array('@type' => 'ProfessionalService', '@id' => $base . '/#studio'),
                'areaServed'  => array('@type' => 'Country', 'name' => 'United States'),
                'offers'      => $offers,
            );
        }
    }

    if (!$blocks) {
        return '';
    }
    $out = '';
    foreach ($blocks as $b) {
        $out .= "  <script type=\"application/ld+json\">\n"
              . json_encode($b, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
              . "\n  </script>\n";
    }
    return $out;
}
