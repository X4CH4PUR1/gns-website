<?php
/**
 * Inline SVG used in more than one place. Keeping them in functions means the
 * brand mark exists once rather than ten times, and the favicon is generated
 * from the same path data the header draws.
 */

/** The chess-queen mark. Also the source of favicon.svg. */
function gns_brand_paths()
{
    return array(
        'M37.4 1H42.6V4.6H48.6V8.8H42.6V13.2H37.4V8.8H31.4V4.6H37.4Z',
        'M23 17C24.5 22 27 25.6 28 30H52C53 25.6 55.5 22 57 17C53 18 50 20.6 49 23.6C47.5 17.6 44 14.1 40 14.1C36 14.1 32.5 17.6 31 23.6C30 20.6 27 18 23 17Z',
        'M28 32H52V35.6H28Z',
        'M29.6 37.4H50.4V40.4H29.6Z',
        'M31.8 42.4H48.2C48.2 55 50.6 63.6 55 69H25C29.4 63.6 31.8 55 31.8 42.4Z',
        'M26.4 71H53.6L57.2 82.4H22.8Z',
        'M20.6 85H59.4V91.4H20.6Z',
    );
}

function gns_brand_mark($class = 'brand-mark', $indent = '        ')
{
    $out = '<svg class="' . e($class) . '" viewBox="19 0 42 93" aria-hidden="true" focusable="false">' . "\n";
    foreach (gns_brand_paths() as $d) {
        $out .= $indent . '  <path d="' . $d . '"/>' . "\n";
    }
    return $out . $indent . '</svg>';
}

function gns_icon_arrow()
{
    return '<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M2.5 8h11M9 3.5 13.5 8 9 12.5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

function gns_icon_tick()
{
    return '<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8.4 6.4 11.8 13 5.2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

function gns_icon_info()
{
    return '<svg viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="8" r="6.4" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M8 7.4v3.4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="8" cy="5.2" r="0.6" fill="currentColor"/></svg>';
}

function gns_icon_shield()
{
    return '<svg viewBox="0 0 24 24"><path d="M12 2.6 20 5.6v6.2c0 4.9-3.4 8.3-8 9.6-4.6-1.3-8-4.7-8-9.6V5.6z"/><path d="M8.6 11.8 11 14.2l4.6-4.6"/></svg>';
}

function gns_icon_linkedin()
{
    return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4.5 9h3v10.5h-3zM6 4.5a1.75 1.75 0 1 1 0 3.5 1.75 1.75 0 0 1 0-3.5zM10 9h2.9v1.5c.5-.9 1.7-1.8 3.4-1.8 3 0 3.7 1.9 3.7 4.5v6.3h-3v-5.6c0-1.4-.3-2.4-1.7-2.4-1.4 0-2.1 1-2.1 2.5v5.5h-3z" fill="currentColor" stroke="none"/></svg>';
}

/** One capability/service icon by key. Unknown keys fall back to the mark. */
function gns_visual_icon($key)
{
    $icons = array(
        'funnel'   => '<svg viewBox="0 0 24 24"><path d="M3 14.5c0-4 2.2-7.5 4.8-7.5 3.4 0 4.6 10 8.4 10C18.8 17 21 13.5 21 9.5S18.8 2 16.2 2C12.8 2 11.6 12 7.8 12 5.2 12 3 8.5 3 4.5"/></svg>',
        'serp'     => '<svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.4 15.4 21 21"/></svg>',
        'tiktok'   => '<svg viewBox="0 0 24 24"><rect x="3.5" y="3.5" width="17" height="17" rx="4.5"/><path d="M10 16.2a2.6 2.6 0 1 0 0-5.2v-4l1 1.4 2 .9"/></svg>',
        'sequence' => '<svg viewBox="0 0 24 24"><rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M3 7.5 12 13.5 21 7.5"/></svg>',
        'minipage' => '<svg viewBox="0 0 24 24"><rect x="2.5" y="4" width="19" height="16" rx="2.5"/><path d="M2.5 8.6h19"/><circle cx="5.8" cy="6.3" r="0.7" fill="currentColor" stroke="none"/><circle cx="8.2" cy="6.3" r="0.7" fill="currentColor" stroke="none"/></svg>',
        'retainer' => gns_icon_shield(),
        'orbit'    => gns_icon_shield(),
        'browser'  => '<svg viewBox="0 0 24 24"><rect x="2.5" y="4" width="19" height="16" rx="2.5"/><path d="M2.5 8.6h19"/></svg>',
        'none'     => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.4"/><path d="M12 7.6v8.8M7.6 12h8.8"/></svg>',
    );
    return isset($icons[$key]) ? $icons[$key] : $icons['none'];
}
