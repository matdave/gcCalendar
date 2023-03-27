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


function getCatTitles()
{
    global $modx;
    $catTitles = array();
    $cats = $modx->newQuery('GcCalendarCats');
    $catItt = $modx->getIterator('GcCalendarCats', $cats);
    $catItt->rewind();
    if ($catItt->valid()) {
        foreach ($catItt as $ct) {
            $id = $ct->get('id');
            $catTitles[$id] = $ct->get('ctitle');
        }
    }
    return $catTitles;
}

function getCatItems($events = array())
{
    global $modx;
    $evCats = array();
    $catItems = $modx->newQuery('GcCalendarCatsConnect');
    if (!empty($events)) {
        $catItems->where(array('evid:IN' => $events));
    }
    $catItems->limit(9999);
    $catItt = $modx->getIterator('GcCalendarCatsConnect', $catItems);
    $catItt->rewind();
    if ($catItt->valid()) {
        foreach ($catItt as $ci) {
            $id = $ci->get('catsid');
            $evCats[$id] = $ci->get('catsid');
        }
    }
    return $evCats;
}

function getCalItems($cals = array(), $items = array())
{
    global $modx;
    $evIDs = array();
    $cal = $modx->newQuery('GcCalendarCalsConnect');
    if (!empty($cals)) {
        $cal->where(array('calid:IN' => $cals));
    }
    if (!empty($items)) {
        $cal->where(array('evid:IN' => $items));
    }
    $cal->limit(9999);
    $calItt = $modx->getIterator('GcCalendarCalsConnect', $cal);
    $calItt->rewind();
    if ($calItt->valid()) {
        foreach ($calItt as $ci) {
            $id = $ci->get('evid');
            $evIDs[$id] = $id;
        }
    }
    return array_values($evIDs);
}

$calid = $modx->getOption('calid', $scriptProperties, null);
$cals = ($calid != null) ? explode(',', $calid) : array();
if (!empty($cals)) {
    $catTitles = getCatTitles();
    $evIDs = getCalItems($cals);
    $catItems = getCatItems($evIDs);
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