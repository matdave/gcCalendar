<?php

$list = array();
$userWUG_arr = $modx->user->getUserGroupNames();
$userid = $modx->user->get('id');
$ug = $modx->newQuery('modUserGroup');
$ug->where(array(
    'name:IN' => $userWUG_arr,
    'AND:name:NOT LIKE' => 'Cloud%',
));
$gc_groups = $modx->getIterator('modUserGroup', $ug);
if (is_array($gc_groups) && count($gc_groups)) {
    foreach ($gc_groups as $mxg) {
        $webContextAccess = $modx->newQuery('modAccessContext');
        $webContextAccess->where(array(
            'principal' => $mxg->get('id'),
            'AND:target:!=' => 'mgr',
        ));
        $gc_cntx = $modx->getIterator('modAccessContext', $webContextAccess);

        if (is_array($gc_cntx) && count($gc_cntx)) {
            foreach ($gc_cntx as $acl) {
                if (!in_array($acl->get('target'), $list)) {
                    $list[] =$acl->get('target');
                }
            }
        }
    }
}
if ($modx->user->isMember('Administrator')) {
    $list[] = array('key'=>'');
}

/* build query */
$c = $modx->newQuery('GcCalendarCals');

$c->select(array(
    'id',
    'title'
));
$query = $modx->getOption('query', $scriptProperties, '');
if (isset($query)) {
    /*prepopulate ids*/
    $ids = preg_replace("/[^a-zA-Z0-9\s]/", "", $query);
    if (!is_numeric($ids)) {
        if ($modx->user->isMember('Administrator')) {
            $c->where(array('title:LIKE' => '%'.preg_replace('/[^a-rtzA-Z0-9]/', '%', $query).'%'));
        } else {
            $c->where(array('key:IN'=>$list,'AND:title:LIKE' => '%'.preg_replace('/[^a-rtzA-Z0-9]/', '%', $query).'%'));
        }
    }
}
$c->sortby('title', 'ASC');
$categories = $modx->getCollection('GcCalendarCals', $c);

/* iterate */
$clist = array();
foreach ($categories as $gccc) {
    $clist[] = $gccc->toArray();
}
return $this->outputArray($clist, sizeof($clist));
