<?php
/**
 * @package gcCalendar
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/gccalendarcatsconnect.class.php');
class GcCalendarCatsConnect_mysql extends GcCalendarCatsConnect {}
?>