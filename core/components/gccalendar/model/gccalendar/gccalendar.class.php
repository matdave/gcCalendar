<?php

class GcCalendar
{
    public $modx;
    public $config = array();
    public function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;

        $basePath = $this->modx->getOption(
            'gccalendar.core_path',
            $config,
            $this->modx->getOption('core_path').'components/gccalendar/'
        );
        $assetsUrl = $this->modx->getOption(
            'gccalendar.assets_url',
            $config,
            $this->modx->getOption('assets_url').'components/gccalendar/'
        );
        $managerURL = $this->modx->getOption('manager_url');
        $this->config = array_merge(array(
            'basePath' => $basePath,
            'corePath' => $basePath,
            'managerPath' => $managerURL,
            'modelPath' => $basePath.'model/',
            'processorsPath' => $basePath.'processors/',
            'templatesPath' => $basePath.'templates/',
            'chunksPath' => $basePath.'elements/chunks/',
            'jsUrl' => $assetsUrl.'js/',
            'cssUrl' => $assetsUrl.'css/',
            'assetsUrl' => $assetsUrl,
            'connectorUrl' => $assetsUrl.'connector.php',
        ), $config);
        $this->modx->addPackage('gccalendar', $this->config['modelPath']);
    }

    public function getEventCalendarDateRange($activeMonthOnlyEvents = false): array
    {
        $startDate = $_REQUEST['dt'] ?: $this->strFormatTime('%Y-%m');
        $mStartDate = $this->strFormatTime('%Y-%m', strtotime($startDate)) . '-01 00:00:01';
        $startDOW = $this->strFormatTime('%u', strtotime($mStartDate));
        $lastDayOfMonth = $this->strFormatTime('%Y-%m', strtotime($mStartDate)) .
            '-' .
            date('t', strtotime($mStartDate)) .
            ' 23:59:59';
        $startMonthCalDate = $startDOW <= 6 ?
            strtotime('- ' . $startDOW . ' day', strtotime($mStartDate)) :
            strtotime($mStartDate);
        $endMonthCalDate = strtotime('+ 6 weeks', $startMonthCalDate);
        if ($activeMonthOnlyEvents) {
            return array('start' => strtotime($mStartDate), 'end' => strtotime($lastDayOfMonth));
        } else {
            return array('start' => $startMonthCalDate, 'end' => $endMonthCalDate);
        }
    }

    public function getCatTitles(): array
    {
        $catTitles = array();
        $cats = $this->modx->newQuery('GcCalendarCats');
        $catItt = $this->modx->getIterator('GcCalendarCats', $cats);
        $catItt->rewind();
        if ($catItt->valid()) {
            foreach ($catItt as $ct) {
                $id = $ct->get('id');
                $catTitles[$id] = $ct->get('ctitle');
            }
        }
        return $catTitles;
    }

    public function getCatItems($events = array()): array
    {
        $evCats = array();
        $catItems = $this->modx->newQuery('GcCalendarCatsConnect');
        if (!empty($events)) {
            $catItems->where(array('evid:IN' => $events));
        }
        $catItems->limit(9999);
        $catItt = $this->modx->getIterator('GcCalendarCatsConnect', $catItems);
        $catItt->rewind();
        if ($catItt->valid()) {
            foreach ($catItt as $ci) {
                $id = $ci->get('catsid');
                $evCats[$id] = $ci->get('catsid');
            }
        }
        return $evCats;
    }

    public function getCalItems($cals = array(), $items = array()): array
    {
        $evIDs = array();
        $cal = $this->modx->newQuery('GcCalendarCalsConnect');
        if (!empty($cals)) {
            $cal->where(array('calid:IN' => $cals));
        }
        if (!empty($items)) {
            $cal->where(array('evid:IN' => $items));
        }
        $cal->limit(9999);
        $calItt = $this->modx->getIterator('GcCalendarCalsConnect', $cal);
        $calItt->rewind();
        if ($calItt->valid()) {
            foreach ($calItt as $ci) {
                $id = $ci->get('evid');
                $evIDs[$id] = $id;
            }
        }
        return array_values($evIDs);
    }

    public function getEventDetails($ids = array())
    {
        $events = array();
        $ev = $this->modx->newQuery('GcCalendarEvents');
        $ev->where(array('id:IN' => $ids));
        $ev->limit(9999);
        $evItt = $this->modx->getIterator('GcCalendarEvents', $ev);
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

    public function getEventDates($ids = array()): array
    {
        $dates = array();
        $dt = $this->modx->newQuery('GcCalendarDates');
        $dt->where(array('evid:IN' => $ids));
        $dt->sortby('start', 'ASC');
        $dt->limit(9999);
        $dtItt = $this->modx->getIterator('GcCalendarDates', $dt);
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

    public function wpse63611EscIcalText($text = '')
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

    // requires 24-hour time (see RFC 5545 section 3.3.12 for info).
    public function dateToCal($timestamp): string
    {
        return date('Ymd\THis', $timestamp);
    }

    // Escapes a string of characters
    public function escapeString($string)
    {
        return preg_replace('/([\,;])/', '\\\$1', $string);
    }

    public function strFormatTime(string $format, $timestamp = null, ?string $locale = null): string
    {
        if (null === $timestamp) {
            $timestamp = new \DateTime;
        } elseif (is_numeric($timestamp)) {
            $timestamp = date_create('@' . $timestamp);

            if ($timestamp) {
                $timestamp->setTimezone(new \DateTimezone(date_default_timezone_get()));
            }
        } elseif (is_string($timestamp)) {
            $timestamp = date_create($timestamp);
        }

        if (!($timestamp instanceof \DateTimeInterface)) {
            throw new \InvalidArgumentException(
                '$timestamp argument is neither a valid UNIX timestamp, a valid date-time string or a DateTime object.'
            );
        }

        $locale = substr((string) $locale, 0, 5);

        $intl_formats = [
            '%a' => 'EEE',  // An abbreviated textual representation of the day Sun through Sat
            '%A' => 'EEEE', // A full textual representation of the day Sunday through Saturday
            '%b' => 'MMM',  // Abbreviated month name, based on the locale  Jan through Dec
            '%B' => 'MMMM', // Full month name, based on the locale January through December
            '%h' => 'MMM',  // Abbreviated month name, based on the locale (an alias of %b) Jan through Dec
        ];

        $intl_formatter = function (\DateTimeInterface $timestamp, string $format) use ($intl_formats, $locale) {
            $tz = $timestamp->getTimezone();
            $date_type = \IntlDateFormatter::FULL;
            $time_type = \IntlDateFormatter::FULL;
            $pattern = '';

            // %c = Preferred date and time stamp based on locale
            // Example: Tue Feb 5 00:45:10 2009 for February 5, 2009 at 12:45:10 AM
            if ($format == '%c') {
                $date_type = \IntlDateFormatter::LONG;
                $time_type = \IntlDateFormatter::SHORT;
            } elseif ($format == '%x') {
                // %x = Preferred date representation based on locale, without the time
                // Example: 02/05/09 for February 5, 2009
                $date_type = \IntlDateFormatter::SHORT;
                $time_type = \IntlDateFormatter::NONE;
            } elseif ($format == '%X') {
                // Localized time format
                $date_type = \IntlDateFormatter::NONE;
                $time_type = \IntlDateFormatter::MEDIUM;
            } else {
                $pattern = $intl_formats[$format];
            }

            return (new \IntlDateFormatter($locale, $date_type, $time_type, $tz, null, $pattern))->format($timestamp);
        };

        // Same order as https://www.php.net/manual/en/function.strftime.php
        $translation_table = [
            // Day
            '%a' => $intl_formatter,
            '%A' => $intl_formatter,
            '%d' => 'd',
            '%e' => function ($timestamp) {
                return sprintf('% 2u', $timestamp->format('j'));
            },
            '%j' => function ($timestamp) {
                // Day number in year, 001 to 366
                return sprintf('%03d', $timestamp->format('z')+1);
            },
            '%u' => 'N',
            '%w' => 'w',

            // Week
            '%U' => function ($timestamp) {
                // Number of weeks between date and first Sunday of year
                $day = new \DateTime(sprintf('%d-01 Sunday', $timestamp->format('Y')));
                return sprintf('%02u', 1 + ($timestamp->format('z') - $day->format('z')) / 7);
            },
            '%V' => 'W',
            '%W' => function ($timestamp) {
                // Number of weeks between date and first Monday of year
                $day = new \DateTime(sprintf('%d-01 Monday', $timestamp->format('Y')));
                return sprintf('%02u', 1 + ($timestamp->format('z') - $day->format('z')) / 7);
            },

            // Month
            '%b' => $intl_formatter,
            '%B' => $intl_formatter,
            '%h' => $intl_formatter,
            '%m' => 'm',

            // Year
            '%C' => function ($timestamp) {
                // Century (-1): 19 for 20th century
                return floor($timestamp->format('Y') / 100);
            },
            '%g' => function ($timestamp) {
                return substr($timestamp->format('o'), -2);
            },
            '%G' => 'o',
            '%y' => 'y',
            '%Y' => 'Y',

            // Time
            '%H' => 'H',
            '%k' => function ($timestamp) {
                return sprintf('% 2u', $timestamp->format('G'));
            },
            '%I' => 'h',
            '%l' => function ($timestamp) {
                return sprintf('% 2u', $timestamp->format('g'));
            },
            '%M' => 'i',
            '%p' => 'A', // AM PM (this is reversed on purpose!)
            '%P' => 'a', // am pm
            '%r' => 'h:i:s A', // %I:%M:%S %p
            '%R' => 'H:i', // %H:%M
            '%S' => 's',
            '%T' => 'H:i:s', // %H:%M:%S
            '%X' => $intl_formatter, // Preferred time representation based on locale, without the date

            // Timezone
            '%z' => 'O',
            '%Z' => 'T',

            // Time and Date Stamps
            '%c' => $intl_formatter,
            '%D' => 'm/d/Y',
            '%F' => 'Y-m-d',
            '%s' => 'U',
            '%x' => $intl_formatter,
        ];

        $out = preg_replace_callback('/(?<!%)(%[a-zA-Z])/', function ($match) use ($translation_table, $timestamp) {
            if ($match[1] == '%n') {
                return "\n";
            } elseif ($match[1] == '%t') {
                return "\t";
            }

            if (!isset($translation_table[$match[1]])) {
                throw new \InvalidArgumentException(sprintf('Format "%s" is unknown in time format', $match[1]));
            }

            $replace = $translation_table[$match[1]];

            if (is_string($replace)) {
                return $timestamp->format($replace);
            } else {
                return $replace($timestamp, $match[1]);
            }
        }, $format);

        $out = str_replace('%%', '%', $out);
        return $out;
    }

    public function createDateRangeArray($strDateFrom, $strDateTo): array
    {
        // takes two dates formatted as YYYY-MM-DD and creates an
        // inclusive array of the dates between the from and to dates.

        // could test validity of dates here but I'm already doing
        // that in the main script

        $aryRange = array();

        $iDateFrom = mktime(1, 0, 0, substr($strDateFrom, 5, 2), substr($strDateFrom, 8, 2), substr($strDateFrom, 0, 4));
        $iDateTo = mktime(1, 0, 0, substr($strDateTo, 5, 2), substr($strDateTo, 8, 2), substr($strDateTo, 0, 4));

        if ($iDateTo >= $iDateFrom) {
            array_push($aryRange, date('Y-m-d', $iDateFrom)); // first entry
            while ($iDateFrom < $iDateTo) {
                $iDateFrom += 86400; // add 24 hours
                array_push($aryRange, date('Y-m-d', $iDateFrom));
            }
        }
        return $aryRange;
    }
}
