<?php

namespace TextParser\Tests;

use TextParser\Exceptions\TextNotFoundException;
use TextParser\Parser;

class TextParserTest extends \PHPUnit_Framework_TestCase
{
    private function setSimpleText()
    {
        return
            '
				<div id="wrapper">
					Ich bin ein Text mit einem <a href="http://www.bing.ch">einfachen Link</a> darin.
					<br />Wieso
					Denn auch nicht?
					<p>
                        Hallo
					</p>
					<div id="list">
					    <ul>
					        <li><a href="http://www.bing.ch">Bing Schweiz</a></li>
					        <li><a href="http://www.google.ch">Google Schweiz</a></li>
					        <li><a href="http://www.duckduckgo.com">DuckDuckGo</a></li>
                        </ul>
                    </div>
                    <div class="empty"></div>
                    <div class="empty"></div>
                    <div class="empty"></div>
                    <div id="empty"></div>
				</div>
				';
    }

    /** @test */
    public function find_one_with_two_parameters_returns_the_text_between_the_first_searchtext_at_the_end_and_the_last_searchtext_at_the_beginning()
    {
        $text = $this->setSimpleText();
        $expectedResult = "einfachen Link";

        $this->assertEquals($expectedResult, Parser::findOne($text, 'ng.ch">', "</a>"));
    }

    /** @test */
    public function find_one_with_multiple_parameters_returns_the_text_between_the_second_last_searchtext_at_the_end_and_the_last_searchtext_at_the_beginning()
    {
        $text = $this->setSimpleText();
        $expectedResult = "einfachen Link";

        $this->assertEquals($expectedResult, Parser::findOne($text, '<div', "<a href=", '"', '">', "</a>"));
    }

    /** @test */
    public function find_one_with_multiple_parameters_return_false_when_one_of_the_searchtexts_could_not_be_found()
    {
        $text = $this->setSimpleText();

        // Suchtext vom ersten Parameter wird nicht gefunden
        $this->assertFalse(Parser::findOne($text, 'FindeMichNicht', "<a href=", "</a>"));

        // Suchtext von einem Parametern ausser dem ersten und letzten Parameter wird nicht gefunden
        $this->assertFalse(Parser::findOne($text, '<div', "FindeMichNicht?", '"', '">', "</a>"));

        // Suchtext vom letzten Parameter wird nicht gefunden
        $this->assertFalse(Parser::findOne($text, '<div', "<a href=", '"', '">', "FindeMichNicht"));
    }

    /** @test */
    public function findMany_with_two_parameters_returns_the_texts_between_the_first_searchtext_at_the_end_and_the_last_searchtext_at_the_beginning()
    {
        $text = $this->setSimpleText();

        // Ein Text
        $this->assertEquals([ 'einfachen Link', 'Bing Schweiz' ], Parser::findMany($text, '</a>', '.bing.ch">'));

        // Mehrere Texte
        $this->assertEquals([ 'http://www.bing.ch', 'http://www.bing.ch', 'http://www.google.ch', 'http://www.duckduckgo.com' ], Parser::findMany($text, '">', '<a href="'));
    }

    /** @test */
    public function findMany_with_multiple_parameters_returns_the_texts_between_the_second_last_searchtext_at_the_end_and_the_last_searchtext_at_the_beginning()
    {
        $text = $this->setSimpleText();

        // Ein Text
        $this->assertEquals([ 'einfachen Link', 'Bing Schweiz' ], Parser::findMany($text, '</a>', 'http', '.bing.ch">'));

        // Ein Text, jedoch leer
        $this->assertEquals([ '' ], Parser::findMany($text, '</div>', '<div id="empty">'));

        // Mehrere Texte
        $this->assertEquals([ 'http://www.bing.ch', 'http://www.google.ch', 'http://www.duckduckgo.com' ], Parser::findMany($text, '">', '<li>', '<a href="'));

        // Mehrere Text, jedoch leere
        $this->assertEquals([ '', '', '' ], Parser::findMany($text, '</div>', '<div class="empty">'));
    }

    /** @test */
    public function findMany_with_multiple_parameters_returns_an_empty_array_when_one_of_the_searchtexts_could_not_be_found()
    {
        $text = $this->setSimpleText();

        // Suchtext vom ersten Parameter wird nicht gefunden
        $this->assertEquals([ ], Parser::findMany($text, '</a>', 'FindeMichNicht', '<a href='));

        // Suchtext von einem Parametern ausser dem ersten und letzten Parameter wird nicht gefunden
        $this->assertEquals([ ], Parser::findMany($text, '</a>', '<div', 'FindeMichNicht?', '"', '">'));

        // Suchtext vom letzten Parameter wird nicht gefunden
        $this->assertEquals([ ], Parser::findMany($text, 'FindeMichNicht', '<div', '<a href=', '"', '">'));
    }
}