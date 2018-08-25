<?php
    $keywords = ['野菜', '-にんじん', '-きゃべつ', 'じゃがいも', 'すいか',];
    var_dump(filterMinusWord1($keywords));
    var_dump(filterMinusWord2($keywords));

    /**
     * @param array $array
     * @return array
     */
    function filterMinusWord1(array $array):array
    {
        $filtered = [];
        foreach ($array as $i => $value) {
            if (!preg_match('/^-.*/', $value)) {
                $filtered[] = $value;
            }
        }
        return $filtered;
    }

    /**
     * @param array $keywords
     * @return array
     */
    function filterMinusWord2(array $keywords):array
    {
        $filtered = array_filter($keywords, (function($keyword) {
            return !preg_match('/^-.*/', $keyword);
        }));
        return array_values($filtered);
    }
