<?php
//**  STARTING DATA **//

//Call the Service
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
$output = '';

if (!($gcCal instanceof GcCalendar)) {
    return '';
}
$theme = $modx->getOption('theme', $scriptProperties, 'default');
$modx->regClientCSS($gcCal->config['assetsUrl'] . 'themes/' . $theme . '/css/mxcalendar.css');
$modx->regClientStartupScript($gcCal->config['assetsUrl'] . 'js/web/gc-calendar.js?v=20230114');

//limit conext key
$did = $modx->resource->get('id');
$document = $modx->getObject('modResource', $did);
$key = $document->get('context_key');
$bcat = $modx->getOption('cat', $scriptProperties, null);
$cid = (isset($_GET['cid'])) ? $_GET['cid'] : $bcat;
$cid = explode(",", $cid);
// make sure they are all numeric
foreach ($cid as $key => $value) {
    if (!is_numeric($value)) {
        unset($cid[$key]);
    }
}
$bcal = $modx->getOption('cal', $scriptProperties, null);
$cal = (isset($_GET['cal']) && is_numeric($_GET['cal'])) ? $_GET['cal'] : $bcal;

$ajaxResourceId = $modx->getOption('ajaxResourceId', $scriptProperties, null);
$detail = (isset($_GET['detail']) && is_numeric($_GET['detail'])) ? $_GET['detail'] : null;
$r = (isset($_GET['r']) && is_numeric($_GET['r'])) ? $_GET['r'] : null;
$detailTpl = $modx->getOption('detailTpl', $scriptProperties, 'gcCaldetail');

$selectorAdditional = [];
if (isset($_GET['calendar'])) {
    $selectorAdditional['calendar'] = $_GET['calendar'];
}
if (isset($_GET['list'])) {
    $selectorAdditional['list'] = $_GET['list'];
}

$selector = ($bcat == null) ?
    '<select aria-label="Select Category" id="calselect" style="display:none; height:auto !important;" data-gcc="/[[~' . $ajaxResourceId . ']]" data-loc="/[[~' . $did . ']]" multiple></select>' :
    '<div style="display:none; visibility:hidden;"><select id="calselect" style="display:none; height:auto !important;" data-gcc="/[[~' . $ajaxResourceId . ']]" data-loc="/[[~' . $did . ']]" multiple><option select="selected" value="' . $bcat . '"></select></div>';
$selector .= '<small>Hold Ctrl or Shift to select multiple categories</small>';
if ($detail != null && $r != null) {
    $gcevent = $modx->getObject('GcCalendarEvents', $detail);
    if (empty($gcevent)) {
        return '';
    }
    $gctime = $modx->getObject('GcCalendarDates', $r);
    if (empty($gctime)) {
        return '';
    }
    $gcevent->set('ical', $modx->makeUrl($ajaxResourceId, '', array('item_id' => $gcevent->get('id'), 'ics' => 1)));
    $gcevent->set('start', $gctime->get('start'));
    $gcevent->set('end', $gctime->get('end'));
    $gcevent->set('notes', preg_replace(
        "~<a\s+href=[\'|\"]mailto:(.*?)[\'|\"].*?>.*?<\/a>~",
        "$1",
        $gcevent->get('notes')
    ));
    $gcevent->set('r', $r);
    $eDetails = $gcevent->toArray();
    $output .= $modx->getChunk($detailTpl, $eDetails);
} else {
    //** Initial Time TPLS & Functions **//
    $mode = 'calendar';

    $dayTpl = $modx->getOption('dayTpl', $scriptProperties, 'gcCalday');
    $weekTpl = $modx->getOption('weekTpl', $scriptProperties, 'gcCalweek');
    $monthTpl = $modx->getOption('monthTpl', $scriptProperties, 'gcCalmonth');
    $headingTpl = $modx->getOption('headingTpl', $scriptProperties, 'gcCalheading');

    $getList = (isset($_GET['list']) && is_numeric($_GET['list'])) ? $_GET['list'] : 0;
    $list = (int) $modx->getOption('list', $scriptProperties, $getList);
    if ($list == 1 && !isset($_GET['dt'])) {
        $mode = 'list';
    }

    $getCalendar = (isset($_GET['calendar']) && is_numeric($_GET['calendar'])) ? $_GET['calendar'] : 0;
    $calendar = (int) $modx->getOption('calendar', $scriptProperties, $getCalendar);
    if ($calendar == 1 && !isset($_GET['dt'])) {
        $mode = 'calendar';
    }

    $getICS = (isset($_GET['ics']) && is_numeric($_GET['ics'])) ? $_GET['ics'] : 0;
    $ical = $modx->getOption('ical', $scriptProperties, $getICS);
    if ($ical == 1 && !isset($_GET['dt']) && isset($_GET['item_id'])) {
        $mode = 'ical';
    }

    $getSelect = (isset($_GET['select']) && is_numeric($_GET['select'])) ? 1 : 0;
    $select = $modx->getOption('select', $scriptProperties, $getSelect);
    if ($select == 1) {
        $mode = 'select';
    }
    if (!isset($_GET['select']) && !isset($_GET['dt']) && !isset($_GET['ics'])) {
        echo $selector;
    }

    $modalView = $modx->getOption('modalView', $scriptProperties, false);
    $activeMonthOnlyEvents = $modx->getOption('activeMonthOnlyEvents', $scriptProperties, 0);
    $dr = $gcCal->getEventCalendarDateRange($activeMonthOnlyEvents);
    $elStartDate = $dr['start'];
    $elEndDate = $dr['end'];

    //start Query
    $hqueryOptions = array();
    $equeryOptions = array();
    $hqueryOptions[] = array('start:<=' => $elEndDate, 'end:>=' => $elStartDate);


    $time_start = microtime(true);

    $startDate = $_GET['dt'] ? $_GET['dt'] : $gcCal->strFormatTime('%Y-%m-%d');
    $mStartDate = $gcCal->strFormatTime('%Y-%m', strtotime($startDate)) . '-01 00:00:01';
    $mCurMonth = $gcCal->strFormatTime('%m', strtotime($mStartDate));
    $nextMonth = $gcCal->strFormatTime('%Y-%m', strtotime('+1 month', strtotime($mStartDate)));
    $prevMonth = $gcCal->strFormatTime('%Y-%m', strtotime('-1 month', strtotime($mStartDate)));
    $startDOW = $gcCal->strFormatTime('%u', strtotime($mStartDate));
    $lastDayOfMonth = $gcCal->strFormatTime('%Y-%m', strtotime($mStartDate)) . '-' . date('t', strtotime($mStartDate)) . ' 23:59:59';
    $endDOW = $gcCal->strFormatTime('%u', strtotime($lastDayOfMonth));

    $out = '';
    $startMonthCalDate = $startDOW <= 6 ?
        strtotime('- ' . $startDOW . ' day', strtotime($mStartDate)) :
        strtotime($mStartDate);
    $endMonthCalDate = strtotime('+ ' . (6 - $endDOW) . ' day', strtotime($lastDayOfMonth));


    $calFilter = $_GET['calf'] ?? $modx->getOption('calendarFilter', $scriptProperties, null); //-- Defaults to show all calendars

    $headingLabel = strtotime($mStartDate);
    $globalParams = array('calf' => $calFilter);
    $todayLink = $modx->makeUrl($ajaxResourceId, '', array_merge($globalParams, array('dt' => $gcCal->strFormatTime('%Y-%m'))));
    $listLink = $modx->makeUrl($ajaxResourceId, '', array_merge($globalParams, array('list' => 1, 'fc' => 1)));
    $calendarLink = $modx->makeUrl($ajaxResourceId, '', array_merge($globalParams, array('calendar' => 1, 'fc' => 1)));
    $prevLink = $modx->makeUrl($ajaxResourceId, '', array_merge($globalParams, array('dt' => $prevMonth)));
    $nextLink = $modx->makeUrl($ajaxResourceId, '', array_merge($globalParams, array('dt' => $nextMonth)));

    $days = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
    $dayNum = array(7, 1, 2, 3, 4, 5, 6);
    $heading = '';

    switch ($mode) {
        case 'calendar':
            if ($cal == null) {
                $cals = $modx->newQuery('GcCalendarCals');
                $cals->select(array('id'));
                $cals->where(array('key:=' => $key));
                $calsArr = $modx->getIterator('GcCalendarCals', $cals);
            } else {
                $calsArr = explode(",", $cal);
            }
            $arrEventDates = array();
            if (!empty($calsArr)) {
                $calid = array();
                foreach ($calsArr as $cArr) {
                    $calid[] = ($cal == null) ? $cArr->get('id') : $cArr;
                }

                //getEventId's in this Context

                $calevs = $modx->newQuery('GcCalendarCalsConnect');
                $calevs->select(array('evid'));
                $calevs->where(array('calid:IN' => $calid));
                $calevsArr = $modx->getIterator('GcCalendarCalsConnect', $calevs);

                if (!empty($calevsArr)) {
                    $cevid = array();
                    foreach ($calevsArr as $ceArr) {
                        $cevid[] = $ceArr->get('evid');
                    }
                    $hqueryOptions[] = array('evid:IN' => $cevid);
                    if ($cid != null) {
                        //$cqueryOptions[] = array('catsid'=>$cid);
                        $cats = $modx->newQuery('GcCalendarCatsConnect');
                        $cats->select('evid');
                        $cats->where(array('catsid:IN' => $cid));
                        $cats->distinct();
                        $catsItt = $modx->getIterator('GcCalendarCatsConnect', $cats);
                        if (!empty($catsItt)) {
                            $ccevid = array();
                            foreach ($catsItt as $cI) {
                                $ccevid[] = $cI->get('evid');
                            }
                            $hqueryOptions[] = array('evid:IN' => $ccevid);
                        }
                    }

                    $dates = $modx->newQuery('GcCalendarDates');
                    $dates->where($hqueryOptions);
                    // $dates->limit($limit,$offset);
                    $dates->sortby('start', 'ASC');
                    $dateArr = $modx->getIterator('GcCalendarDates', $dates);
                    if (!empty($dateArr)) {
                        $evitems = '';
                        $eo = 0;
                        $idx = 0;
                        $evitems = array();
                        foreach ($dateArr as $dArr) {
                            $evid = $dArr->get('evid');
                            $repid = $dArr->get('id');
                            $event = $modx->getObject('GcCalendarEvents', $evid);

                            $eventDet['id'] = $evid;
                            $eventDet['eo'] = $eo;
                            $eventDet['idx'] = $idx;
                            $eventDet['start'] = $dArr->get('start');
                            $eventDet['end'] = $dArr->get('end');
                            $eventDet['ad'] = $event->get('ad');
                            $eventDet['islive'] = $event->get('islive');
                            $eventDet['title'] = $event->get('title');
                            $evitems[] = $eventDet;
                            $eo = ($eo == 0) ? 1 : 0;
                            $arrEventsDetail[$evid . '-' . $repid] = $eventDet;
                            $arrEventDates[$evid . '-' . $repid] = array(
                                'date' => $eventDet['start'],
                                'end' => $eventDet['end'], 'ad' => $eventDet['ad'], 'eventId' => $evid, 'eventRepId' => $repid, 'repeatId' => 0);
                            $idx++;
                        }
                    } else {
                        $output .= 'No upcoming events!';
                    }
                } else {
                    $output .= 'No items in this Calendar!';
                }
            } else {
                $output .= 'No Calendars assigned to this site!';
            }

            if ($output == '') {
                for ($i = 0; $i < 7; $i++) {
                    $thisDOW = str_replace(
                        $dayNum,
                        $days,
                        strtolower(
                            $gcCal->strFormatTime('%u', strtotime('+ ' . $i . ' day', $startMonthCalDate))
                        )
                    );
                    $heading .= $modx->getChunk(
                        $headingTpl,
                        array('dayOfWeekId' => '', 'dayOfWeekClass' => 'mxcdow', 'dayOfWeek' => $thisDOW)
                    );
                }
                //-- Set additional day placeholders for week
                $phHeading = array(
                    'weekId' => ''
                , 'weekClass' => ''
                , 'days' => $heading
                );
                $weeks = '';
                //-- Start the Date loop
                $var = 0;
                foreach ($arrEventDates as $e) {
                    //Get original event (parent) details
                    $oDetails = $arrEventsDetail[$e['eventId'] . '-' . $e['eventRepId']];
                    $oDetails['startdate'] = $e['date'];
                    $oDetails['enddate'] = $e['end'];
                    if (($oDetails['startdate'] >= $elStartDate || $oDetails['enddate'] >= $elStartDate) &&
                        $oDetails['enddate'] <= $elEndDate
                    ) {
                        $oDetails['startdate_fstamp'] = $oDetails['startdate'];
                        $oDetails['enddate_fstamp'] = $oDetails['enddate'];

                        $oDetails['detailURL'] = $modx->makeUrl(
                            (
                                !empty($ajaxResourceId) && (bool)$modalView === true ?
                                $ajaxResourceId :
                                $did
                            ),
                            '',
                            array('detail' => $e['eventId'], 'r' => $e['eventRepId'])
                        );
                        if ($gcCal->strFormatTime('%Y-%m-%d', $e['date']) ==
                            $gcCal->strFormatTime('%Y-%m-%d', $e['end'])
                        ) {
                            $events[$gcCal->strFormatTime('%Y-%m-%d', $e['date'])][] = $oDetails;
                        } else {
                            $spandates = $gcCal->createDateRangeArray(
                                $gcCal->strFormatTime('%Y-%m-%d', $e['date']),
                                $gcCal->strFormatTime('%Y-%m-%d', $e['end'])
                            );
                            foreach ($spandates as $spD) {
                                $events[$spD][] = $oDetails;
                            }
                        }
                        // $output.= $e['date']. '<br/>';
                    }
                }
                do {
                    // Week Start date
                    $iWeek = strtotime('+ ' . $var . ' week', $startMonthCalDate);
                    $diw = 0;
                    $days = '';
                    do {
                        // Get the week's days
                        $iDay = strtotime('+ ' . $diw . ' day', $iWeek);
                        $thisMonth = $gcCal->strFormatTime('%m', $iDay);

                        $eventList = '';
                        if (isset($events[$gcCal->strFormatTime('%Y-%m-%d', $iDay)]) &&
                            count($events[$gcCal->strFormatTime('%Y-%m-%d', $iDay)])
                        ) {
                            //-- Echo each event item
                            $e = $events[$gcCal->strFormatTime('%Y-%m-%d', $iDay)];

                            foreach ($e as $el) {
                                $el['start'] = ($el['ad'] != 1) ?
                                    $gcCal->strFormatTime('%l:%M %p', $el['start']) :
                                    'All Day';
                                $event_html = '<div id="' . $el['id'] .
                                    '" class="' . $el['eventClass'] .
                                    '">' . $el['start'] .
                                    '<span class="title startdate "><a aria-label="' . $el['detailURL'] .
                                    ' ' . $el['title'] .
                                    '" href="/' . $el['detailURL'] .
                                    '" class="gccalevent" >' . $el['title'] .
                                    '</a></span></div>';
                                $eventList .= $event_html;
                            }
                        }

                        //-- Set additional day placeholders for day
                        $isToday = ($gcCal->strFormatTime('%m-%d') == $gcCal->strFormatTime('%m-%d', $iDay)) ?
                            'today ' :
                            '';
                        $dayMonthName = $gcCal->strFormatTime('%b', $iDay);
                        $dayMonthDay = $gcCal->strFormatTime('%d', $iDay);
                        $dayMonthDay = ($gcCal->strFormatTime('%d', $iDay) == 1 ?
                            $gcCal->strFormatTime('%b ', $iDay) . (substr($dayMonthDay, 0, 1) == '0' ?
                                ' ' . substr($dayMonthDay, 1) :
                                $dayMonthDay) :
                            (substr($dayMonthDay, 0, 1) == '0' ?
                                ' ' . substr($dayMonthDay, 1) :
                                $dayMonthDay));
                        $phDay = array(
                            'dayOfMonth' => $dayMonthDay
                            , 'dayOfMonthID' => 'dom-' . $gcCal->strFormatTime('%b%A%d', $iDay)
                            , 'events' => $eventList
                            , 'fulldate' => $gcCal->strFormatTime('%m/%d/%Y', $iDay)
                            , 'tomorrow' => $gcCal->strFormatTime('%m/%d/%Y', strtotime('+1 day', $iDay))
                            , 'yesterday' => $gcCal->strFormatTime('%m/%d/%Y', strtotime('-1 day', $iDay))
                            , 'class' => $isToday . ($mCurMonth == $thisMonth ? '' : ' ncm')
                        );
                        $days .= $modx->getChunk($dayTpl, $phDay);
                    } while (++$diw < 7);


                    //-- Set additional day placeholders for week
                    $phWeek = array(
                        'weekId' => 'mxcWeek' . $var
                    , 'weekClass' => $gcCal->strFormatTime('%A%d', $iDay)
                    , 'days' => $days
                    );
                    //$weeks.=$chunkWeek->process($phWeek);
                    $weeks .= $modx->getChunk($weekTpl, $phWeek);
                } while (++$var < 6); //Only advance 5 weeks giving total of 6 weeks

                //
                $time_end = microtime(true);
                $time = $time_end - $time_start;
                //echo '<p>mxCalendar=>makeEventCalendar() processed in '.$time.'</p>';

                //-- Set additional day placeholders for month
                $phMonth = array(
                    'containerID' => $gcCal->strFormatTime('%a', $iDay)
                    , 'containerClass' => $gcCal->strFormatTime('%a%Y', $iDay)
                    , 'weeks' => $heading . $weeks
                    , 'headingLabel' => $headingLabel
                    , 'todayLink' => $todayLink
                    , 'todayLabel' => 'Today'
                    , 'listLink' => $listLink
                    , 'prevLink' => $prevLink
                    , 'nextLink' => $nextLink
                    , 'cid' => $cid
                    , 'cal' => $cal
                );
                //return $chunkMonth->process($phMonth);
                $output .= $modx->getChunk($monthTpl, $phMonth);
            }
            break;

        case 'list':
            $_GET['cid'] == $cid;
            $_GET['fc'] = 1;
            $xtrainfo = ($bcat != null) ? '&cid=' . $bcat : '';
            include($corePath . 'elements/snippets/gcCalList.php');
            break;

        case 'ical':
            include($corePath . 'elements/snippets/gcCaliCal.php');
            break;

        case 'select':
            $_GET['cid'] == $cid;
            include($corePath . 'elements/snippets/gcCalSelect.php');
            break;
    }
}
echo $output;
