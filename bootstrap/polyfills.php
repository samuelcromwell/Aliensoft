<?php

if (! function_exists('mb_strimwidth')) {
    function mb_strimwidth(
        string $string,
        int $start,
        int $width,
        string $trim_marker = '',
        ?string $encoding = null
    ): string {
        unset($encoding);

        $segment = substr($string, $start, $width);

        if (strlen($string) <= $start + $width) {
            return $segment;
        }

        return $segment.$trim_marker;
    }
}
