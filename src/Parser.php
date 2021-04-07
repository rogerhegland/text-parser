<?php

namespace TextParser;

class Parser
{
    /**
     * @param string $text
     * @param string  ...$searchTexts
     *
     * @return string|bool
     */
    public static function findOne($text, ...$searchTexts)
    {
        $numberOfSearchTexts = count($searchTexts);

        $checkedSearchTexts = 0;
        foreach ($searchTexts as $searchText) {
            $checkedSearchTexts++;
            // the last searchtext can be an empty string - all others must have an value
            if ( ! $searchText && $numberOfSearchTexts != $checkedSearchTexts) {
                return false;
            }
        }

        $index = 0;
        while (isset($searchTexts[$index])) {
            $searchText = $searchTexts[$index];
            $lastParameter = $numberOfSearchTexts - 1 == $index;

            $striposResult = ( $index + 1 == $numberOfSearchTexts && $searchText === '' )
                ? strlen($text)
                : stripos($text, $searchText);
            if ($striposResult === false) {
                return false;
            }

            if ($lastParameter) {
                $text = substr($text, 0, $striposResult);
            } else {
                $text = substr($text, $striposResult + strlen($searchText));
            }

            $index++;
        }

        return $text;
    }

    /**
     * @param string $text
     * @param string $endText
     * @param string  ...$searchTexts
     *
     * @return array
     */
    public static function findMany($text, $endText, ...$searchTexts)
    {
        if ( ! $endText) {
            return [];
        }

        $foundTexts = [];
        $findOneParameters[] = $text;
        $findOneParameters = array_merge($findOneParameters, $searchTexts);
        $findOneParameters[] = $endText;
        while (( $found = call_user_func_array([ self::class, 'findOne' ], $findOneParameters) ) !== false) {
            $foundTexts[] = $found;

            foreach ($searchTexts as $searchText) {
                $text = substr_replace($text, '', stripos($text, $searchText), strlen($searchText));
            }

            $text = $found !== '' ? substr_replace($text, '', stripos($text, $found), strlen($found)) : $text;
            $text = substr_replace($text, '', stripos($text, $endText), strlen($endText));

            $findOneParameters[0] = $text;
        }

        return $foundTexts;
    }
}