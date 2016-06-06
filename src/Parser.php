<?php

namespace TextParser;

use TextParser\Exceptions\TextNotFoundException;

class Parser
{
    /**
     * @param string $text
     * @param array  ...$searchTexts
     *
     * @return string
     * @throws TextNotFoundException
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
                throw new TextNotFoundException;
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
     * @throws TextNotFoundException
     */
    public static function findMany($text, $endText, ...$searchTexts)
    {
        $foundTexts = [ ];
        try {
            $findOneParameters[] = $text;
            $findOneParameters = array_merge($findOneParameters, $searchTexts);
            $findOneParameters[] = $endText;
            while ($found = call_user_func_array([ self::class, 'findOne' ], $findOneParameters)) {
                $foundTexts[] = $found;

                foreach ($searchTexts as $searchText) {
                    $text = substr_replace($text, '', strpos($text, $searchText), strlen($searchText));
                }
                $text = substr_replace($text, '', strpos($text, $found), strlen($found));
                $text = substr_replace($text, '', strpos($text, $endText), strlen($endText));

                $findOneParameters[0] = $text;
            }
        } catch (TextNotFoundException $ex) {
            if ( ! count($foundTexts)) {
                throw new TextNotFoundException;
            }
        }

        return $foundTexts;
    }
}