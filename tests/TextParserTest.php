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
				</div>
				';
    }

    /** @test */
    public function findOneWithTwoParametersReturnsTheTextBetweenTheFirstSearchtextAtTheEndAndTheLastSearchtextAtTheBeginning()
    {
        $text = $this->setSimpleText();
        $expectedResult = "einfachen Link";

        $this->assertEquals($expectedResult, Parser::findOne($text, 'ng.ch">', "</a>"));
    }

    /** @test */
    public function findOneWithMultipleParametersReturnsTheTextBetweenTheSecondLastSearchtextAtTheEndAndTheLastSearchtextAtTheBeginning()
    {
        $text = $this->setSimpleText();
        $expectedResult = "einfachen Link";

        $this->assertEquals($expectedResult, Parser::findOne($text, '<div', "<a href=", '"', '">', "</a>"));
    }

    /** @test */
    public function findOneWithMultipleParametersThrowTextNotFoundExceptionWhenOneOfTheSearchtextsIsNotFound()
    {
        $this->expectException(TextNotFoundException::class);

        $text = $this->setSimpleText();

        // Suchtext vom ersten Parameter wird nicht gefunden
        Parser::findOne($text, 'FindeMichNicht', "<a href=", "</a>");

        // Suchtext von einem Parametern ausser dem ersten und letzten Parameter wird nicht gefunden
        Parser::findOne($text, '<div', "FindeMichNicht?", '"', '">', "</a>");

        // Suchtext vom letzten Parameter wird nicht gefunden
        Parser::findOne($text, '<div', "<a href=", '"', '">', "FindeMichNicht");
    }

    /** @test */
    public function findManyWithTwoParametersReturnsTheTextsBetweenTheFirstSearchtextAtTheEndAndTheLastSearchtextAtTheBeginning()
    {
        $text = $this->setSimpleText();

        // Ein Text
        $this->assertEquals([ 'einfachen Link', 'Bing Schweiz' ], Parser::findMany($text, '</a>', '.bing.ch">'));

        // Mehrere Texte
        $this->assertEquals([ 'http://www.bing.ch', 'http://www.bing.ch', 'http://www.google.ch', 'http://www.duckduckgo.com' ], Parser::findMany($text, '">', '<a href="'));
    }

    /** @test */
    public function findManyWithMultipleParametersReturnsTheTextsBetweenTheSecondLastSearchtextAtTheEndAndTheLastSearchtextAtTheBeginning()
    {
        $text = $this->setSimpleText();

        // Ein Text
        $this->assertEquals([ 'einfachen Link', 'Bing Schweiz' ], Parser::findMany($text, '</a>', 'http', '.bing.ch">'));

        // Mehrere Texte
        $this->assertEquals([ 'http://www.bing.ch', 'http://www.google.ch', 'http://www.duckduckgo.com' ], Parser::findMany($text, '">', '<li>', '<a href="'));
    }

    /** @test */
    public function findManyWithMultipleParametersThrowTextNotFoundExceptionWhenOneOfTheSearchtextsIsNotFound()
    {
        $this->expectException(TextNotFoundException::class);

        $text = $this->setSimpleText();

        // Suchtext vom ersten Parameter wird nicht gefunden
        Parser::findMany($text, '</a>', 'FindeMichNicht', '<a href=');

        // Suchtext von einem Parametern ausser dem ersten und letzten Parameter wird nicht gefunden
        Parser::findMany($text, '</a>', '<div', 'FindeMichNicht?', '"', '">');

        // Suchtext vom letzten Parameter wird nicht gefunden
        Parser::findMany($text, 'FindeMichNicht', '<div', '<a href=', '"', '">');
    }
}