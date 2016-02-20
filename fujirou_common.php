<?php

class FujirouCommon
{
    /**
     * given regular expression, return the first matched string
     */
    public static function getFirstMatch($string, $pattern)
    {
        if (1 === preg_match($pattern, $string, $matches)) {
            return $matches[1];
        }
        return false;
    }

    /**
     * given regular expression, return all matched first group in an array
     */
    public static function getAllFirstMatch($string, $pattern) {
        $ret = preg_match_all($pattern, $string, $matches);
        if ($ret > 0) {
            return $matches[1];
        } else {
            return $ret;
        }
    }

    /**
     * given prefix and suffix, return the shortest matched string.
     * returned string including prefix and suffix
     */
    public static function getSubString($string, $prefix, $suffix)
    {
        $start = strpos($string, $prefix);
        if ($start === false) {
            return $string;
        }

        $end = strpos($string, $suffix, $start);
        if ($end === false) {
            return $string;
        }

        if ($start >= $end) {
            return $string;
        }

        return substr($string, $start, $end - $start + strlen($suffix));
    }

    /**
     * remove CR and LF from string
     */
    public static function toOneLine($string)
    {
        return str_replace(array("\n", "\r"), '', $string);
    }

    /**
     * decode HTML entity using UTF-8 encoding
     */
    public static function decodeHTML($string)
    {
        return html_entity_decode($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * convert size with unit to bytes
     */
    public static function convertSize($string) {
        $pattern = '/([0-9\.]+ *([a-zA-Z]*))/';
        $number;
        $unit;
        $unitTable = array('Bytes', 'KB', 'MB', 'GB', 'TB');

        if (1 === preg_match($pattern, $string, $matches)) {
            $number = $matches[1];
            $unit = $matches[2];
        }

        foreach ($unitTable as $idx => $unitStr) {
            if (0 === strcasecmp($unit, $unitStr)) {
                $unitSize = pow(1024, $idx);
                break;
            }
        }

        $size = floatval($number) * $unitSize;

        return round($size);
    }

    public static function removeCdata($string)
    {
        $string = str_replace('<![CDATA[', '', $string);
        $string = str_replace(']]>', '', $string);
        return $string;
    }
}

// vim: expandtab ts=4
?>
