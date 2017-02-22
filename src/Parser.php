<?php

namespace TextParser;

class Parser
{
    /**
     * @param string $text
     * @param array  ...$searchTexts
     *
     * @return string|bool
     */
    public static function findOne($text, ...$searchTexts)
    {
        foreach ($searchTexts as $searchText) {
            if ( ! $searchText) {
                return false;
            }
        }

        $numberOfSearchTexts = count($searchTexts);
        $index = 0;
        while (isset($searchTexts[$index])) {
            $searchText = $searchTexts[$index];
            $lastParameter = $numberOfSearchTexts - 1 == $index;

            $striposResult = stripos($text, $searchText);
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
     * @param array  ...$searchTexts
     *
     * @return array
     */
    public static function findMany($text, $endText, ...$searchTexts)
    {
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