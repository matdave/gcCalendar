<?php

$gcCal = $modx->getService(
    'gccalendar',
    'GcCalendar',
    $modx->getOption(
        'gccalendar.core_path',
        null,
        $modx->getOption('core_path') . 'components/gcCalendar/'
    ) . 'model/gcCalendar/',
    $scriptProperties
);
$output = null;

$calid = $modx->getOption('calid', $scriptProperties, null);
$cals = ($calid != null) ? explode(',', $calid) : array();
if (!empty($cals)) {
    $catTitles = $gcCal->getCatTitles();
    $evIDs = $gcCal->getCalItems($cals);
    $catItems = $gcCal->getCatItems($evIDs);
    if (!empty($catItems)) {
        $output .= "<h2>Categories with links</h2><ul>";
        $output .= '<li><a href="' . $modx->makeURL(1460) . '" target="_blank"><b>All Categories</b> - ' . $modx->makeURL(1460, '', '', 'full') . '</a></li>';
        foreach ($catItems as $c) {
            $url = $modx->makeUrl(1460, '', array('cat' => $c), 'full');
            $output .= '<li><a href="' . $url . '" target="_blank"><b>' . $catTitles[$c] . '</b> - ' . $url . '</a></li>';
        }
        $output .= "</ul>";
    }
}

return $output;