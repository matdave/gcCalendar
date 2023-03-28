<?php
$gcCal = $modx->getService(
    'gccalendar',
    'GcCalendar',
    $modx->getOption(
        'gccalendar.core_path',
        null,
        $modx->getOption('core_path') . 'components/gccalendar/'
    ) . 'model/gccalendar/',
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

/* Get Category ID*/
$calid = $modx->getOption('calid', $scriptProperties, null);
$cals = ($calid != null) ? explode(',', $calid) : array();
$catid = (isset($_GET['cat']) && is_numeric($_GET['cat'])) ? $_GET['cat'] : null;
if (!empty($cals)) {
    $catTitles = $gcCal->getCatTitles();
    $catItems = $gcCal->getCatItems($catid);
    $limitEv = ($catid != null) ? array_values($catItems['ids']) : array();
    $evIDs = $gcCal->getCalItems($cals, $limitEv);
    $events = $gcCal->getEventDetails($evIDs);
    $dates = $gcCal->getEventDates($evIDs);
    if (!empty($events) && !empty($dates)) {
        $output = 'BEGIN:VCALENDAR' . "\r\n";
        $output .= 'VERSION:2.0' . "\r\n";
        $output .= 'PRODID:-//gccalendar//IdeaBank Marketing//EN_US' . "\r\n";
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
            $output .= 'DTEND;TZID=America/Chicago:' . $gcCal->dateToCal($d['end']) . "\r\n";
            $output .= 'UID:' . $id . "\r\n";
            $output .= 'DTSTAMP:' . $gcCal->dateToCal(time()) . "\r\n";
            $output .= 'LOCATION:' . $gcCal->escapeString($events[$evid]['locationname'] . ' ' . $events[$evid]['locationaddr'] . ' ' . $events[$evid]['locationcity'] . ', ' . $events[$evid]['locationstate'] . ' ' . $events[$evid]['locationzip']) . "\r\n";
            $output .= 'DESCRIPTION:"' . $gcCal->wpse63611EscIcalText($events[$evid]['notes']) . '"' . "\r\n";
            $link = $events[$evid]['link'];
            if (!empty($link) && strpos($link, 'http') === false) {
                $link = "http://" . $link;
            }
            $output .= 'URL;VALUE=URI:' . $gcCal->wpse63611EscIcalText($link) . "\r\n";
            $output .= 'SUMMARY:' . $gcCal->escapeString($events[$evid]['title']) . "\r\n";
            $output .= 'DTSTART;TZID=America/Chicago:' . $gcCal->dateToCal($d['start']) . "\r\n";
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
