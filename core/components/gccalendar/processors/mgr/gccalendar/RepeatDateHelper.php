<?php
/**
 * Project: gcCalendar
 * Purpose: A parser helper file to do a few little things based on mxCalendar Helper
 **/

//-- Make reoccuring event list
function _getRepeatDates(
    $frequencymode = 0,
    $interval = 1,
    $frequency = '1',
    $startDate = null,
    $endDate = null,
    $onwd = array(0,1,2,3,4,5,6),
    $occType = 'UNIX',
    $options = ''
) {
    global $modx;
    
    //-- Date Output Format
    //$dateFormat = 'D n-j-Y';
    //'Y-m-d h:i a';
    //-- Time Output Format
    //$timeFormat = 'h:ia';
    //-- Date Time Display (full=Date+Time,date=Date,time=Time)
    //$dateTimeFormat = 'full';
    //-- Set Max Occurances not to exceed the end date
    //$frequency = 365;
    //-- Set the reoccurance mode (2=Months,0=Days,3=Years,1=Weeks)
    //$frequencymode = 'w';
    //-- The span (interval) between reoccurances
    $interval = (int)$interval;
    //-- Event Start Date
    //$startDate = '2010-01-11 18:00:00';
    //-- Event End Date
    //$endDate = '2010-06-11 19:30:00';
    //-- Holder of all events
    $ar_Recur = array();
    //-- Enable the debugger (Manager)
    $debug = false;

    $x = 0;
    
    $theParameter = array('MODE'=>$frequencymode,
        'interval'=>$interval,
        'frequency'=>$frequency,
        'StartDate'=>$startDate,
        'EndDate'=>$endDate,
        'OnWeedkDay'=>$onwd);
    if ($debug) {
        echo "Date repeat function paramters are:<br />";
        foreach ($theParameter as $key => $val) {
            echo $key.'=>'.$val.'<br />'.PHP_EOL;
        }
    }

    //-- Check the Date and build the repeat dates
    //-- prior to PHP 5.1.0 you would compare with -1, instead of false
    if (($timestamp = $startDate) === false) {
        return false;
    } else {
        switch ($frequencymode) {
            case 0: //Daily
                while (++$x) {
                    $occurance = mktime(
                        date('H', $startDate),
                        date('i', $startDate),
                        0,
                        date('m', $startDate),
                        date('d', $startDate) + ($x * $interval),
                        date('y', $startDate)
                    );
                    if ($occurance <= $endDate && $x < $frequency && $startDate < $occurance) {
                        $ar_Recur[] = $occurance;
                    } else {
                        break;
                    }
                }
                break;
            case 2: //Monthly
                $occurance = $startDate;
            
                //$modx->log(modX::LOG_LEVEL_ERROR,'[mxHelper] mxFormBuilder _getRpeatDate:[options]<br />'.$options);
            
                $options = !empty($options) ? json_decode($options, true) : '';
                while (++$x) {
                    if (!empty($options)) {
                        switch ($options['type']) {
                            case 'dow':
                                // Day of week is simply the same day of a week
                                $occurance = strtotime(
                                    $options['week']." "._strFormatTime('%A', $occurance)." of next month",
                                    $occurance
                                );
                                $occurance = mktime(
                                    date('H', $startDate),
                                    date('i', $startDate),
                                    0,
                                    date('m', $occurance),
                                    date('d', $occurance),
                                    date('y', $occurance)
                                );
                                break;
                            case 'dom':
                                $occurance = strtotime("next month", $occurance);
                                $occurance = mktime(
                                    date('H', $startDate),
                                    date('i', $startDate),
                                    0,
                                    date('m', $occurance),
                                    date('d', $occurance),
                                    date('y', $occurance)
                                );
                                break;
                            default:
                                $occurance = mktime(
                                    date('H', $startDate),
                                    date('i', $startDate),
                                    0,
                                    date('m', $startDate)+($x*$interval),
                                    date('d', $startDate),
                                    date('y', $startDate)
                                );
                                break;
                        }
                    } else {
                        $occurance = mktime(
                            date('H', $startDate),
                            date('i', $startDate),
                            0,
                            date('m', $startDate)+($x*$interval),
                            date('d', $startDate),
                            date('y', $startDate)
                        );
                    }

                    if ($occurance <= $endDate && $x < $frequency && $startDate < $occurance) {
                        $ar_Recur[] = $occurance;
                        if ($debug) {
                            echo $occurance."< -is less than -> ".$endDate.'<br />';
                        }
                    } else {
                        if ($debug) {
                            echo $occurance."||-is eq or greater than -||".$endDate.'<br />';
                        }
                        break;
                    }
                }
                break;
            case 1: //Weekly
                $valid = true;
                            
                //-- Get the first repeat Day of Week if the same as start date's Day of Week
                $curWeek = $startWeek = _strFormatTime('%W', $startDate);
                $occurance = _strFormatTime('%Y-%m-%d %H:%M:%S', $startDate);
                $originalTime = _strFormatTime(' %H:%M:%S', $startDate);
                $nextWeek = _strFormatTime('%Y-%m-%d %H:%M:%S', strtotime('next monday', $startDate));
                if ($debug) {
                    echo 'Current Week of the Start Date: '.$curWeek.'<br />';
                }
                //-- Loop through days until the end of current week
                while ($curWeek == $startWeek) {
                    $occurance = _strFormatTime('%Y-%m-%d %H:%M:%S', strtotime('next day', strtotime($occurance)));
                    $curWeek= _strFormatTime('%W', strtotime($occurance));

                    //-- Get occurance day of week int
                    $thisDOW = _strFormatTime('%w', strtotime("next day", strtotime($occurance)));

                    //-- Get the valid date formated of occurance
                    $occDate = _strFormatTime('%Y-%m-%d', strtotime("next day", strtotime($occurance))).$originalTime;

                    //-- Check if the date is one of the assigned and less than the end date
                    if (in_array($thisDOW, $onwd) &&
                        $curWeek == $startWeek &&
                        strtotime($occDate) < strtotime($nextWeek) &&
                        strtotime($occDate) > strtotime($startDate)
                    ) {
                        if ($debug) {
                            echo $occDate .
                                " MATCH on $thisDOW (start week) :: CurWk=$curWeek :: StartWk=$startWeek :: NextWk=$nextWeek<br />";
                        }
                        $ar_Recur[] = ($occType == 'UNIX' ? strtotime($occDate) : $occDate);
                    } else {
                        if ($debug  && $curWeek == $startWeek && strtotime($occDate) < strtotime($nextWeek)) {
                            echo $occDate." (start week)<br />";
                        }
                    }
                }

                $startDate  = date('Y-m-d H:i:s', strtotime(' last mon ', strtotime($occurance)));
                if ($debug) {
                    echo '<strong>Start date MONDAY of that week: </strong>: '.$startDate.'<br />';
                }
                $startDate = date('Y-m-d H:i:s', strtotime(' + '.($interval).' week', strtotime($startDate)));
                if ($debug) {
                    echo '<strong>Next Valid Repeat Week Start Date: </strong>: '.$startDate.'<br />'.
                         'Modified start: '.$startDate.' with adjusted interval: '.($interval).' <br />'.
                         'Frequency: '.$frequency.' with the max repeat of: '.($frequency*7).'<br />';
                }

                //-- Created a new loop to limit the possibility of almost endless loop
                $newDate = strtotime($startDate);
                $x=1;
                while ($newDate <= $endDate) {
                    if ($debug) {
                        echo "x={$x}<br />";
                    }
                    $occurance = $newDate; //date('Y-m-d H:i:s', c);

                    $lastweek=sprintf("%02d", (_strFormatTime('%W', $newDate)));
                    if ($debug) {
                        echo 'Week of: '.$lastweek."<br />";
                    }
                    $year = _strFormatTime('%Y', $occurance);
                    for ($i=0; $i<=6; $i++) {
                        //-- Get occurance day of week int
                        $thisDOW = _strFormatTime('%w', strtotime("+{$i} day", $occurance));

                        //-- Get the valid date formated of occurance
                        $occDate = _strFormatTime('%Y-%m-%d', strtotime("+{$i} day", $occurance)).$originalTime;

                        //-- Check if the date is one of the assigned and less than the end date
                        if (in_array($thisDOW, $onwd) && strtotime($occDate) <= $endDate) {
                            if ($debug) {
                                echo $occDate." MATCH on $thisDOW <br />";
                            }
                            $ar_Recur[] = ($occType == 'UNIX' ? strtotime($occDate) : $occDate);
                        } else {
                            if ($debug) {
                                echo $occDate."<br />";
                            }
                        }

                        //-- If the date is past the end date end the loop
                        if (strtotime($occDate) >= $endDate) {
                            if ($debug) {
                                echo "\t".strtotime($occDate) .' is greater than '. $endDate."<br />";
                            }
                            $valid = false; //-- End the loop
                            break;
                        }
                        //-- Reset the date for while loop validation
                        $newDate = strtotime(' + '.$interval.' weeks', $occurance);
                    }
                    $x++;
                    if (!$valid || $x > $frequency) {
                        break;
                    }
                }
                if ($debug) {
                    echo '<strong><em>'.count($ar_Recur).'<em> total matches dates added.</strong>';
                }
                break;
            case 3: //Yearly
                while (++$x) {
                    $occurance = mktime(
                        date('H', $startDate),
                        date('i', $startDate),
                        0,
                        date('m', $startDate),
                        date('d', $startDate),
                        date('y', $startDate)+($x*$interval)
                    );
                    if ($occurance <= $endDate && $x < $frequency && $startDate < $occurance) {
                        $ar_Recur[] = $occurance;
                        if ($debug) {
                            echo $occurance."< -is less than -> ".$endDate.'<br />';
                        }
                    } else {
                        if ($debug) {
                            echo $occurance."||-is eq or greater than -||".$endDate.'<br />';
                        }
                        break;
                    }
                }
                break;
        }
        //-- Display the results to validate
        if ($debug) {
            echo "THE OCC DATES:<br />";
            print_r($ar_Recur);
        }
        if (isset($curTZ)) {
            date_default_timezone_set($curTZ);
        }
        return implode(',', $ar_Recur);
    }
}
function _strFormatTime(string $format, $timestamp = null, ?string $locale = null): string
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
