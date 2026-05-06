<?php

namespace TextParser;

class Parser
{
    /**
     * @param string $text
     * @param string ...$searchTexts
     *
     * @return string|bool
     */
    public static function findOne(string $text, string ...$searchTexts): bool|string
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
     * @param string ...$searchTexts
     *
     * @return string|bool
     */
    public static function fO(string $text, string ...$searchTexts): bool|string
    {
        return self::findOne($text, ...$searchTexts);
    }

    /**
     * @param string $text
     * @param string ...$searchTexts
     *
     * @return string|bool
     */
    public static function bFindOne(string $text, string ...$searchTexts): bool|string
    {
        return self::findOneBackwards($text, ...$searchTexts);
    }

    /**
     * @param string $text
     * @param string ...$searchTexts
     *
     * @return string|bool
     */
    public static function bfO(string $text, string ...$searchTexts): bool|string
    {
        return self::findOneBackwards($text, ...$searchTexts);
    }

    /**
     * @param string $text
     * @param string ...$searchTexts
     *
     * @return string|bool
     */
    public static function findOneBackwards(string $text, string ...$searchTexts): bool|string
    {
        $numberOfSearchTexts = count($searchTexts);
        if ($numberOfSearchTexts < 2) {
            return false;
        }

        foreach ($searchTexts as $searchText) {
            if ( ! $searchText) {
                return false;
            }
        }

        $index = 0;
        while (isset($searchTexts[$index])) {
            $searchText = $searchTexts[$index];
            $lastParameter = $numberOfSearchTexts - 1 == $index;

            $striposResult = $index === 0
                ? stripos($text, $searchText)
                : strripos($text, $searchText);
            if ($striposResult === false) {
                return false;
            }

            if ($index === 0) {
                $text = substr($text, 0, $striposResult);
            } elseif ($lastParameter) {
                $text = substr($text, $striposResult + strlen($searchText));
            } else {
                $text = substr($text, 0, $striposResult);
            }

            $index++;
        }

        return $text;
    }

    /**
     * @param string $text
     * @param string $endText
     * @param string ...$searchTexts
     *
     * @return array
     */
    public static function findMany(string $text, string $endText, string ...$searchTexts): array
    {
        if ( ! $endText) {
            return [];
        }

        $foundTexts = [];
        while (( $found = self::findForwardMatch($text, $endText, ...$searchTexts) ) !== false) {
            $foundTexts[] = $found['text'];
            $text = substr($text, $found['end']);
        }

        return $foundTexts;
    }

    /**
     * @param string $text
     * @param string $endText
     * @param string ...$searchTexts
     *
     * @return array
     */
    public static function fM(string $text, string $endText, string ...$searchTexts): array
    {
        return self::findMany($text, $endText, ...$searchTexts);
    }

    /**
     * @param string $text
     * @param string $endText
     * @param string ...$searchTexts
     *
     * @return array{text: string, end: int}|false
     */
    private static function findForwardMatch(string $text, string $endText, string ...$searchTexts): bool|array
    {
        $offset = 0;
        foreach ($searchTexts as $searchText) {
            if ( ! $searchText) {
                return false;
            }

            $striposResult = stripos($text, $searchText, $offset);
            if ($striposResult === false) {
                return false;
            }

            $offset = $striposResult + strlen($searchText);
        }

        $striposResult = stripos($text, $endText, $offset);
        if ($striposResult === false) {
            return false;
        }

        return [
            'text' => substr($text, $offset, $striposResult - $offset),
            'end'  => $striposResult + strlen($endText),
        ];
    }
}
