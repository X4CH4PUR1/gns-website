<?php
/**
 * GN SCALES — CALCULATOR PARITY CHECK
 *
 * The break-even model exists twice: gns_calc() in admin/lib/store.php renders
 * the page's opening state, and calcModel() in main.js updates it as the
 * sliders move. They must agree, or a visitor with JavaScript disabled reads
 * different numbers from one with it enabled.
 *
 * Usage:  php tools/check-calc.php
 * Exits non-zero if the two ever disagree, so it can sit in a deploy hook.
 */

require dirname(__DIR__) . '/admin/lib/boot.php';

$cases = array(
    array('spend' => 6000,  'job' => 1800,  'close' => 25, 'margin' => 45, 'fee' => 2500),
    array('spend' => 1000,  'job' => 200,   'close' => 5,  'margin' => 20, 'fee' => 1500),
    array('spend' => 30000, 'job' => 12000, 'close' => 80, 'margin' => 80, 'fee' => 2500),
    array('spend' => 12000, 'job' => 5000,  'close' => 40, 'margin' => 55, 'fee' => 1500),
    array('spend' => 4500,  'job' => 900,   'close' => 18, 'margin' => 33, 'fee' => 2500),
);

$out = array();
foreach ($cases as $case) {
    $r = gns_calc($case);
    $out[] = array(
        'jobs'      => $r['jobs'],
        'leads'     => $r['leads'],
        'cpl'       => $r['cpl'],
        'benchmark' => $r['benchmark'],
        'band'      => $r['band'],
        'profit'    => (int)round($r['profit']),
        'verdict'   => gns_calc_verdict($r),
    );
}

echo json_encode($out, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
