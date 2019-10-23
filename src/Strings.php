<?php

namespace Yoga;

/**
 * @method static Strings service()
 */
class Strings extends Service {

    public function ellipsis($s, $maxLength) {
        if (strlen($s) > $maxLength) {
            $s = substr($s, 0, $maxLength - 3);
            if ($p = strrpos($s, ' ')) {
                $s = substr($s, 0, $p);
            }
            $s .= '...';
        }
        return $s;
    }

    public function escapeShellPath($s) {
        return str_replace(' ', '\\ ', $s);
    }

    public function ending($n, $english, $isSeparateTriads = true) {
        $result = $n;
        if ($isSeparateTriads) {
            $result = number_format($result);
        }
        $result .= ' ' . $this->pluralIfNeeded($n, $english);
        return $result;
    }

    public function pluralIfNeeded($n, $english) {
        if ($n == 1) {
            return $english;
        }
        return $this->plural($english);
    }

    public function plural($english) {
        $ss = strtolower($english);
        if (
            (substr($ss, -2) == 'es')
            || (substr($ss, -2) == 'ts')
            || (substr($ss, -2) == 'ds')
            || (substr($ss, -2) == 'ms')
            || (substr($ss, -7) == 'history')
            || (substr($ss, -7) == 'content')
            || (substr($ss, -2) == 'rs')
            || (substr($ss, -2) == 'gs')
            || (substr($ss, -2) == 'ls')
        ) {
            return $english;
        }
        if (substr($ss, -1) == 'y' && substr($ss, -2, 1) != 'a') {
            return substr($english, 0, -1) . 'ies';
        }
        if (
            substr($ss, -1) == 's'
            || substr($ss, -2) == 'sh'
            || substr($ss, -2) == 'ch'
            || substr($ss, -1) == 'x'
        ) {
            return $english . 'es';
        }
        return $english . 's';
    }

    /**
     * Returns array of 2 elements [$email, $name]
     * @param string $fullEmail
     * @return string[]
     */
    public function parseFullEmail($fullEmail) {
        $a = explode(' ', $fullEmail);
        if (count($a) == 1) {
            return [$a[0], null];
        }
        $email = $a[count($a) - 1];
        $email = substr($email, 1, strlen($email) - 2);
        unset($a[count($a) - 1]);
        $name = join(' ', $a);
        return [$email, $name];
    }

    public function getRandomString($length = 32) {
        return substr(md5(mt_rand()), 0, $length);
    }

    public function formatCsvLine(array $a) {
        $result = '';
        foreach ($a as $field) {
            if ($result) {
                $result .= ',';
            }
            $result .= $this->escapeCsv($field);
        }
        return $result;
    }

    public function escapeCsv($s) {
        $s = str_replace('"', '""', $s);
        if (false !== strpos($s, ',') || false !== strpos($s, '"') || strpos($s, "\n")) {
            $s = '"' . $s . '"';
        }
        return $s;
    }

    public function unescapeCsv($s) {
        if (substr($s, 0, 1) == '"') {
            $l = strlen($s);
            if (substr($s, $l - 1, 1) == '"') {
                $s = substr($s, 1, $l - 2);
            }
        }
        return str_replace('""', '"', $s);
    }

    /**
     * @param $s
     * @return string[]
     */
    public function parseCsvLine($s) {
        $result = [];
        $l = strlen($s);
        $fragment = '';
        $isQuotes = false;
        for ($i = 0; $i < $l; $i++) {
            if (!$isQuotes && ',' == $s[$i]) {
                $result[] = $this->unescapeCsv($fragment);
                $fragment = '';
            } else {
                $fragment .= $s[$i];
            }
            if ('"' == $s[$i]) {
                $isQuotes = !$isQuotes;
            }
        }
        $result[] = $this->unescapeCsv($fragment);
        return $result;
    }

}
