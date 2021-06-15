<?php

namespace CNCLTD;

use DateTimeInterface;

class Utils
{
    public static function isJSONBroken($mixed)
    {
        json_encode($mixed);
        return json_last_error() !== JSON_ERROR_NONE;
    }

    public static function findBrokenJSON($array)
    {
        if (!$array) {
            return;
        }
        if (!self::isJSONBroken($array)) {
            return null;
        }
        foreach ($array as $key => $value) {
            $test = [$key => $value];
            if (!self::isJSONBroken($test)) {
                continue;
            }
            return $key;
        }
        return;
    }

    public static function utf8ize($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                if (is_string($value) && $value) {
                    $test = json_encode($value);
                    if (!$test) {
                        var_dump($key, $value);
                        exit;
                    }
                }
                $mixed[$key] = self::utf8ize($value);

            }
        } elseif (is_string($mixed)) {
            return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
        }
        return $mixed;
    }

    /**
     * @param DateTimeInterface|null $dateTime
     * @param string $format
     * @return string|null
     */
    public static function dateTimeToString(?DateTimeInterface $dateTime, $format = DATE_MYSQL_DATETIME): ?string
    {
        if (!$dateTime) {
            return null;
        }
        return $dateTime->format($format);
    }

    public static function generateStrongPassword($length = 15, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = array();
        if (strpos($available_sets, 'l') !== false) $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        if (strpos($available_sets, 'u') !== false) $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        if (strpos($available_sets, 'd') !== false) $sets[] = '23456789';
        if (strpos($available_sets, 's') !== false) $sets[] = '!@#$%&*?';
        $all      = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[self::tweak_array_rand(str_split($set))];
            $all      .= $set;
        }
        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); $i++) $password .= $all[self::tweak_array_rand($all)];
        $password = str_shuffle($password);
        if (!$add_dashes) return $password;
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while (strlen($password) > $dash_len) {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }
//take a array and get random index, same function of array_rand, only diference is
// intent use secure random algoritn on fail use mersene twistter, and on fail use defaul array_rand
    private static function tweak_array_rand($array)
    {
        if (function_exists('random_int')) {
            return random_int(0, count($array) - 1);
        } elseif (function_exists('mt_rand')) {
            return mt_rand(0, count($array) - 1);
        } else {
            return array_rand($array);
        }
    }

    public static function getCurrentChangelogVersion()
    {
        $changelog = file_get_contents(BASE_DRIVE . '/CHANGELOG.md');
        $re        = '/\[(v\d+\.\d+\.\d+)\]/m';
        preg_match($re, $changelog, $matches);
        return $matches[1];
    }

    public static function truncate($reason,
                                    $length = 100
    )
    {
        return mb_substr(
            self::stripEverything($reason),
            0,
            $length,
            "utf-8"
        );

    }

    /**
     * strip html tages
     *
     * @param mixed $description
     * @return string
     */
    public static function stripEverything($description)
    {
        $description = str_replace(
            "\r\n",
            '',
            trim($description)
        );
        $description = str_replace(
            "\r",
            '',
            trim($description)
        );
        $description = str_replace(
            "\n",
            '',
            $description
        );
        $description = str_replace(
            "\t",
            '',
            $description
        );
        $description = str_replace(
            '<br />',
            "",
            $description
        );
        $description = str_replace(
            '<br/>',
            "",
            $description
        );
        $description = str_replace(
            '<BR/>',
            "",
            $description
        );
        $description = str_replace(
            '<BR>',
            "",
            $description
        );
        $description = str_replace(
            '<p>',
            "",
            $description
        );
        $description = str_replace(
            '</p>',
            "",
            $description
        );
        $description = str_replace(
            '<P>',
            "",
            $description
        );
        $description = str_replace(
            '</P>',
            "",
            $description
        );
        $description = str_replace(
            '&nbsp;',
            " ",
            $description
        );
        $description = str_replace(
            '&quot;',
            "'",
            $description
        );
        $description = strip_tags($description);
        $description = trim($description);
        $description = htmlspecialchars_decode($description);
        return $description;

    }
}