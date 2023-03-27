<?php
class GcCalendarRemoveProcessor extends modObjectRemoveProcessor {
    public $classKey = 'GcCalendarEvents';
    public $languageTopics = array('gccalendar:default');
    public $objectType = 'gccalendar.GcCalendarEvents';
    public function beforeRemove() {
        $thisid = $this->object->get('id');
        $rcals = array(
                    'evid:=' => $thisid,
                );
                $this->modx->removeCollection('GcCalendarCalsConnect', $rcals);
                $this->modx->removeCollection('GcCalendarCatsConnect', $rcals);
                $this->modx->removeCollection('GcCalendarDates', $rcals);
        return !$this->hasErrors();
    }
}
return 'GcCalendarRemoveProcessor';