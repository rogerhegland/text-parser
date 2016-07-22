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
        $numberOfSearchTexts = count($searchTexts);
        $index = 0;
        while (isset( $searchTexts[$index] )) {
            $searchText = $searchTexts[$index];
            $lastParameter = $numberOfSearchTexts - 1 == $index;

            $strposResult = strpos($text, $searchText);
            if ($strposResult === false) {
                return false;
            }

            if ($lastParameter) {
                $text = substr($text, 0, $strposResult);
            } else {
                $text = substr($text, $strposResult + strlen($searchText));
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
        $foundTexts = [ ];
        $findOneParameters[] = $text;
        $findOneParameters = array_merge($findOneParameters, $searchTexts);
        $findOneParameters[] = $endText;
        while (( $found = call_user_func_array([ self::class, 'findOne' ], $findOneParameters) ) !== false) {
            $foundTexts[] = $found;

            foreach ($searchTexts as $searchText) {
                $text = substr_replace($text, '', strpos($text, $searchText), strlen($searchText));
            }

            $text = $found !== '' ? substr_replace($text, '', strpos($text, $found), strlen($found)) : $text;
            $text = substr_replace($text, '', strpos($text, $endText), strlen($endText));

            $findOneParameters[0] = $text;
        }

        return $foundTexts;
    }
}