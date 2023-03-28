<?php
//**  STARTING DATA **//

//Call the Service
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
$output = '';

$now = strtotime('Today 12:01:00AM');
$lastev = strtotime('+1 week');

//limit conext key
$did =  $modx->resource->get('id');
$document = $modx->getObject('modResource', $did);
$key = $document->get('context_key');
$bcat = $modx->getOption('cat', $scriptProperties, null);
$cid = (isset($_GET['cid']) && is_numeric($_GET['cid']))?$_GET['cid']:$bcat;
$bcal = $modx->getOption('cal', $scriptProperties, null);
$cal = (isset($_GET['cal']) && is_numeric($_GET['cal']))?$_GET['cal']:$bcal;

//start Query
$hqueryOptions = array();
$equeryOptions = array();
//$hqueryOptions[] = array('islive:='=>'1');
$hqueryOptions[] = array('end:>'=>$now,'start:<'=>$lastev);
//PageNumber determined by javascript file
$requested_page = (isset($_GET['page_num']) && is_numeric($_GET['page_num']))?$_GET['page_num']:1;

//** TPLS **//

//Tpl for list wrapper
$listTpl = $modx->getOption('listTpl', $scriptProperties, 'gcCalListRSS');

//Tpl for each Item in list
$itemTpl = $modx->getOption('itemTpl', $scriptProperties, 'gcCalItemRSS');



//** SCRIPT OPTIONS **//

$ajaxResourceId = $modx->getOption('ajaxResourceId', $scriptProperties, null);

//limit set in snippet call create offset
$limit = $modx->getOption('limit', $scriptProperties, 999);
$datelimit = $modx->getOption('datelimit', $scriptProperties, null);
if ($datelimit != null) {
    $hqueryOptions[] = array('end:<'=>strtotime($datelimit));
}
$offset = ($requested_page - 1) * $limit;

//get category
$subid = (isset($_GET['cid']))?htmlspecialchars($_GET['cid']):null;



//** PROCESSING *//

//getAllEvents in this Context//


if ($cal == null) {
    $cals = $modx->newQuery('GcCalendarCals');
    $cals->select(array('id'));
    $cals->where(array('key:='=>$key));
    $calsArr = $modx->getIterator('GcCalendarCals', $cals);
} else {
    $calsArr = explode(",", $cal);
}

if (!empty($calsArr)) {
    $calid = array();
    foreach ($calsArr as $cArr) {
        $calid[] = ($cal == null)?$cArr->get('id'):$cArr;
    }
    //getEventId's in this Context

    $calevs = $modx->newQuery('GcCalendarCalsConnect');
    $calevs->select(array('evid'));
    $calevs->where(array('calid:IN'=>$calid));
    $calevsArr = $modx->getIterator('GcCalendarCalsConnect', $calevs);

    if (!empty($calevsArr)) {
        $cevid = array();
        foreach ($calevsArr as $ceArr) {
            $cevid[] = $ceArr->get('evid');
        }
        $hqueryOptions[] = array('evid:IN'=>$cevid);
        if ($cid != null) {
            //$cqueryOptions[] = array('catsid'=>$cid);
            $cats = $modx->newQuery('GcCalendarCatsConnect');
            $cats->select('evid');
            $cats->where(array('catsid'=>$cid));
            $cats->distinct();
            $catsItt = $modx->getIterator('GcCalendarCatsConnect', $cats);
            if (!empty($catsItt)) {
                $ccevid = array();
                foreach ($catsItt as $cI) {
                    $ccevid[] = $cI->get('evid');
                }
                $hqueryOptions[] = array('AND:evid:IN'=>$ccevid);
            }
        }

        $dates = $modx->newQuery('GcCalendarDates');
        $dates->where($hqueryOptions);
        $dates->limit($limit, $offset);
        $dates->sortby('start', 'ASC');
        $dateArr = $modx->getIterator('GcCalendarDates', $dates);
        if (!empty($dateArr)) {
            $evitems = '';
            $eo = 0;
            $idx = 0;
            foreach ($dateArr as $dArr) {
                $eventDet = array();
                $evid = $dArr->get('evid');
                $event = $modx->getObject('GcCalendarEvents', $evid);

                $eventDet['r'] = $dArr->get('id');
                $eventDet['id'] = $evid;
                $eventDet['eo'] = $eo;
                $eventDet['idx'] = $idx;
                $eventDet['ical'] = $modx->makeUrl(
                    (!empty($ajaxResourceId) ? $ajaxResourceId : $did),
                    '',
                    array('id' => $evid, 'ics'=>1),
                    'full'
                );
                $eventDet['start'] = $dArr->get('start');
                $eventDet['span'] = (date("m.d.y", $dArr->get('start')) == date("m.d.y", $dArr->get('end'))) ?
                    date("m.d.y", $dArr->get('start')) :
                    date("m.d.y", $dArr->get('start'))." - ".date("m.d.y", $dArr->get('end'));
                $eventDet['end'] =  $dArr->get('end');
                $eventDet['ad'] = $event->get('ad');
                $eventDet['islive'] = $event->get('islive');
                $eventDet['notes'] = $event->get('notes');
                $eventDet['title'] = $event->get('title');
                $eventDet['locationname'] = $event->get('locationname');
                $eventDet['locationaddr'] = $event->get('locationaddr');
                $eventDet['pubDate'] = date("a, d b Y H:M:S z", strtotime("-1 week", $dArr->get('start')));
                $eventDet['infoURL'] =  $modx->makeUrl(
                    (!empty($ajaxResourceId) ? $ajaxResourceId : $did),
                    '',
                    array('detail' => $eventDet['id'], 'r'=>$eventDet['r']),
                    'full'
                );

                $evitems.= $modx->getChunk($itemTpl, $eventDet);
                $eo = ($eo == 0)?1:0;
                $idx++;
            }
            $eventsArr = array('events'=>$evitems);
            $output .= $modx->getChunk($listTpl, $eventsArr);
        }
    }
}
return $output;
