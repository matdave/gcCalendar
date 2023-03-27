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
/**
 * Created by JetBrains PhpStorm.
 * User: video2
 * Date: 10/9/13
 * Time: 2:44 PM
 * To change this template use File | Settings | File Templates.
 */

/* FUNCTIONS!!*/
// requires 24-hour time (see RFC 5545 section 3.3.12 for info).
function dateToCal($timestamp)
{
    return date('Ymd\THis', $timestamp);
}

// Escapes a string of characters
function escapeString($string)
{
    return preg_replace('/([\,;])/', '\\\$1', $string);
}

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

function getCatItems($catid = null)
{
    global $modx;
    $evCats = array();
    $catItems = $modx->newQuery('GcCalendarCatsConnect');
    if ($catid != null) {
        $catItems->where(array('catsid' => $catid));
    }
    $catItems->limit(9999);
    $catItt = $modx->getIterator('GcCalendarCatsConnect', $catItems);
    $catItt->rewind();
    if ($catItt->valid()) {
        foreach ($catItt as $ci) {
            $id = $ci->get('evid');
            $evCats['cats'][$id][] = $ci->get('catsid');
            $evCats['ids'][$id] = $id;
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

function getEventDetails($ids = array())
{
    global $modx;
    $events = array();
    $ev = $modx->newQuery('GcCalendarEvents');
    $ev->where(array('id:IN' => $ids));
    $ev->limit(9999);
    $evItt = $modx->getIterator('GcCalendarEvents', $ev);
    $evItt->rewind();
    if ($evItt->valid()) {
        foreach ($evItt as $e) {
            $id = $e->get('id');
            $event = array();
            $event['locationname'] = $e->get('locationname');
            $event['locationaddr'] = $e->get('locationaddr');
            $event['locationcity'] = $e->get('locationcity');
            $event['locationstate'] = $e->get('locationstate');
            $event['locationzip'] = $e->get('locationzip');
            $event['notes'] = $e->get('notes');
            $event['link'] = $e->get('link');
            $event['title'] = $e->get('title');
            $events[$id] = $event;
        }
    }
    return $events;
}

function getEventDates($ids = array())
{
    global $modx;
    $dates = array();
    $dt = $modx->newQuery('GcCalendarDates');
    $dt->where(array('evid:IN' => $ids));
    $dt->sortby('start', 'ASC');
    $dt->limit(9999);
    $dtItt = $modx->getIterator('GcCalendarDates', $dt);
    $dtItt->rewind();
    if ($dtItt->valid()) {
        foreach ($dtItt as $d) {
            $date = array();
            $date['end'] = $d->get('end');
            $date['start'] = $d->get('start');
            $date['evid'] = $d->get('evid');
            $date['id'] = $d->get('id');
            $dates[] = $date;
        }
    }
    return $dates;
}

function wpse63611_esc_ical_text($text = '')
{
    $text = strip_tags($text);
    $text = str_replace("\\", "", $text);
    $text = str_replace(",", "\,", $text);
    $text = str_replace(";", "\;", $text);
    $text = str_replace("\n", "\n ", $text);
    $text = str_replace("\r", "\r\n ", $text);
    $text = str_replace("\n", "\r\n ", $text);
    return $text;
}

/* Get Category ID*/
$calid = $modx->getOption('calid', $scriptProperties, null);
$cals = ($calid != null) ? explode(',', $calid) : array();
$catid = (isset($_GET['cat']) && is_numeric($_GET['cat'])) ? $_GET['cat'] : null;
if (!empty($cals)) {
    $catTitles = getCatTitles();
    $catItems = getCatItems($catid);
    $limitEv = ($catid != null) ? array_values($catItems['ids']) : array();
    $evIDs = getCalItems($cals, $limitEv);
    $events = getEventDetails($evIDs);
    $dates = getEventDates($evIDs);
    if (!empty($events) && !empty($dates)) {
        $output = 'BEGIN:VCALENDAR' . "\r\n";
        $output .= 'VERSION:2.0' . "\r\n";
        $output .= 'PRODID:-//gcCalendar//IdeaBank Marketing//EN_US' . "\r\n";
        $output .= 'METHOD:PUBLISH' . "\r\n";
        $output .= 'CALSCALE:GREGORIA' . "\r\n";
        foreach ($dates as $d) {
            $evid = $d['evid'];
            $id = $d['id'];
            $output .= 'BEGIN:VEVENT' . "\r\n";
            $categories = $catItems['cats'][$evid];
            if (!empty($categories)) {
                $cts = array();
                foreach ($categories as $c) {
                    $cts[] = $catTitles[$c];
                }
                $output .= 'CATEGORIES:' . implode(',', $cts) . "\r\n";
            }
            $output .= 'DTEND;TZID=America/Chicago:' . dateToCal($d['end']) . "\r\n";
            $output .= 'UID:' . $id . "\r\n";
            $output .= 'DTSTAMP:' . dateToCal(time()) . "\r\n";
            $output .= 'LOCATION:' . escapeString($events[$evid]['locationname'] . ' ' . $events[$evid]['locationaddr'] . ' ' . $events[$evid]['locationcity'] . ', ' . $events[$evid]['locationstate'] . ' ' . $events[$evid]['locationzip']) . "\r\n";
            $output .= 'DESCRIPTION:"' . wpse63611_esc_ical_text($events[$evid]['notes']) . '"' . "\r\n";
            $link = $events[$evid]['link'];
            if (!empty($link) && strpos($link, 'http') === false) {
                $link = "http://" . $link;
            }
            $output .= 'URL;VALUE=URI:' . wpse63611_esc_ical_text($link) . "\r\n";
            $output .= 'SUMMARY:' . escapeString($events[$evid]['title']) . "\r\n";
            $output .= 'DTSTART;TZID=America/Chicago:' . dateToCal($d['start']) . "\r\n";
            $output .= 'END:VEVENT' . "\r\n";

        }
        $output .= 'END:VCALENDAR';

    } else {
        $output .= 'No Events Found!';
    }
    /* PROCESS */

} else {
    $output .= 'Please enter a valid category ID!';
}

return $output;