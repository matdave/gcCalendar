<?php

$corePath = $modx->getOption(
    'gccalendar.core_path',
    null,
    $modx->getOption('core_path') . 'components/gccalendar/'
);
$gcCal = $modx->getService(
    'gccalendar',
    'GcCalendar',
    $corePath . 'model/gccalendar/',
    $scriptProperties
);

if (!($gcCal instanceof GcCalendar)) return '';

/* Get ID*/
$evid = (isset($_GET['item_id']) && is_numeric($_GET['item_id'])) ? $_GET['item_id'] : null;
if ($evid != null) {
    /* PROCESS */
    $dates = $modx->newQuery('GcCalendarDates');
    // $dates->limit($limit,$offset);
    $dates->where(array('evid:=' => $evid));
    $dates->sortby('start', 'ASC');
    $dateArr = $modx->getIterator('GcCalendarDates', $dates);
    $event = $modx->getObject('GcCalendarEvents', $evid);
    header('Content-type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $event->get('title') . '.ics');
    $output = 'BEGIN:VCALENDAR' . PHP_EOL;
    $output .= 'VERSION:2.0' . PHP_EOL;
    $output .= 'PRODID:-//gccalendar//IdeaBank Marketing//EN_US' . PHP_EOL;
    $output .= 'METHOD:PUBLISH' . PHP_EOL;
    $output .= 'CALSCALE:GREGORIA' . PHP_EOL;
    foreach ($dateArr as $dArr) {
        $output .= 'BEGIN:VEVENT' . PHP_EOL;
        $output .= 'DTEND;TZID=America/Chicago:' . $gcCal->dateToCal($dArr->get('end')) . PHP_EOL;
        $output .= 'UID:' . $dArr->get('id') . PHP_EOL;
        $output .= 'DTSTAMP:' . $gcCal->dateToCal(time()) . PHP_EOL;
        $output .= 'LOCATION:' . $event->get('locationname') . ' ' . $event->get('locationaddr') . ' ' . $event->get('locationcity') . ', ' . $event->get('locationstate') . ' ' . $event->get('locationzip') . PHP_EOL;
        $output .= 'DESCRIPTION:' . strip_tags($event->get('notes')) . PHP_EOL;
        $output .= 'URL;VALUE=URI:' . $event->get('link') . PHP_EOL;
        $output .= 'SUMMARY:' . $event->get('title') . PHP_EOL;
        $output .= 'DTSTART;TZID=America/Chicago:' . $gcCal->dateToCal($dArr->get('start')) . PHP_EOL;
        $output .= 'END:VEVENT' . PHP_EOL;
    }
    $output .= 'END:VCALENDAR';
    echo $output;
    die();
} else {
    echo "Please Enter a Valid ID";
}
