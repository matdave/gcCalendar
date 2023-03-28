<?php
/**
 * Resolve creating db tables
 *
 * THIS RESOLVER IS AUTOMATICALLY GENERATED, NO CHANGES WILL APPLY
 *
 * @package gccalendar
 * @subpackage build
 *
 * @var mixed $object
 * @var modX $modx
 * @var array $options
 */

if ($object->xpdo) {
    $modx =& $object->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $modelPath = $modx->getOption('gccalendar.core_path', null, $modx->getOption('core_path') . 'components/gccalendar/') . 'model/';
            
            $modx->addPackage('gccalendar', $modelPath, null);


            $manager = $modx->getManager();

            $manager->createObjectContainer('GcCalendarCals');
            $manager->createObjectContainer('GcCalendarCats');
            $manager->createObjectContainer('GcCalendarDates');
            $manager->createObjectContainer('GcCalendarEvents');
            $manager->createObjectContainer('GcCalendarCatsConnect');
            $manager->createObjectContainer('GcCalendarCalsConnect');

            break;
    }
}

return true;