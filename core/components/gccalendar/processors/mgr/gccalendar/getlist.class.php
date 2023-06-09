<?php
class GcCalendarGetListProcessor extends modObjectGetListProcessor
{
    public $classKey = 'GcCalendarEvents';
    public $languageTopics = array('gccalendar:default');
    public $defaultSortField = 'start';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'gccalendar.GcCalendarEvents';
    public function prepareRow(xPDOObject $object)
    {
        $repeatmo = $object->get('repeatonmo');
        $repeatmo = explode(',', $repeatmo);
        $repeatmoArr = array();
        $repeatmoArr['type'] = (!empty($repeatmo))?$repeatmo[0]:null;
        $repeatmoArr['week'] = (count($repeatmo) > 1)?$repeatmo[1]:null;
        $object->set('startymd', ((date('Y-m-d', $object->get('start')))));
        $object->set('starthis', ((date('g:i A', $object->get('start')))));
        $object->set('endymd', ((date('Y-m-d', $object->get('end')))));
        $object->set('endhis', ((date('g:i A', $object->get('end')))));
        $object->set('startRAW', $object->get('start'));
        $object->set('start', (date('Y-m-d g:i A', $object->get('start'))));
        $object->set('end', (date('Y-m-d g:i A', $object->get('end'))));
        $object->set('repeatenddate', ((date('Y-m-d', $object->get('repeatenddate')))));
        $object->set('repeatonc', ','.$object->get('repeaton').',');
        $object->set('repeatonmo', $repeatmoArr);
        $object->set('islive', (($object->get('islive')==1)?true:false));
        $object->set('ad', (($object->get('ad')==1)?true:false));
        return $object->toArray();
    }
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $query = $this->getProperty('searchbox');
        $combo = $this->getProperty('combobox');
        $history = $this->getProperty('historical');
        if (!empty($query)) {
            $qu = array(
                'title:LIKE' => '%'.preg_replace('/[^a-rtzA-Z0-9]/', '%', $query).'%'
            );

            $c->where($qu);
        }
        if (!empty($combo)) {
            $calfilt = $this->modx->newQuery('GcCalendarCalsConnect');
            $calfilt->where(array('calid:='=>$combo));
            $cals = $this->modx->getCollection('GcCalendarCalsConnect', $calfilt);
            $cids = array();
            foreach ($cals as $ca) {
                $cids[] = $ca->get('evid');
            }

            $tp = array(
                'id:IN' => $cids
            );

            $c->where($tp);
        }
        $stime = strtotime('-1 day');
        $tim = ($history == 1) ?
            array('start:<=' => $stime,'AND:end:<=' => $stime,'or:repeatenddate:<=' => $stime ) :
            array('start:>=' => $stime,'OR:end:>=' => $stime,'OR:repeatenddate:>=' => $stime );

        $c->where($tim);
        return $c;
    }
}
return 'GcCalendarGetListProcessor';
