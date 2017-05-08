<?php

namespace TextParser\Tests;

use TextParser\Parser;

class TextParserTest extends \PHPUnit_Framework_TestCase
{
    private function getSimpleText()
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
				
				<div class="text-uppercase">
				    <SPAN class="UPPERCASE">UPPERCASE TEXT</SPAN>
				    <SPAN class="UPPERCASE">UPPERCASE TEXT</SPAN>
                </div>


<span class="text-warning">
I am an error.
</span>


<span class="text-warning">
I am an error.
</span>
				';
    }

    /** @test */
    public function findOne_with_one_parameter_returns_the_text_from_beginning_of_the_text_to_the_first_occurence_of_the_searchtext()
    {
        $text = '<strong>this is a important text</strong>';
        $expected = '<strong';

        $this->assertEquals($expected, Parser::findOne($text, '>'));
    }

    /** @test */
    public function find_one_with_two_parameters_where_the_last_parameter_is_empty_returns_the_text_from_beginnging_of_the_first_occurences_to_the_end_of_the_text()
    {
        $text = 'Hi, my name is -Roger Hegland';
        $expected = 'Roger Hegland';

        $this->assertEquals($expected, Parser::findOne($text, '-', ''));
    }

    /** @test */
    public function findOne_with_two_parameters_returns_the_text_between_the_first_searchtext_at_the_end_and_the_last_searchtext_at_the_beginning()
    {
        $text = $this->getSimpleText();
        $expected = "einfachen Link";

        $this->assertEquals($expected, Parser::findOne($text, 'ng.ch">', "</a>"));
    }

    /** @test */
    public function findOne_with_multiple_parameters_returns_the_text_between_the_second_last_searchtext_at_the_end_and_the_last_searchtext_at_the_beginning()
    {
        $text = $this->getSimpleText();
        $expected = "einfachen Link";

        $this->assertEquals($expected, Parser::findOne($text, '<div', "<a href=", '"', '">', "</a>"));
    }

    /** @test */
    public function findOne_with_multiple_parameters_return_false_when_one_of_the_searchtexts_could_not_be_found()
    {
        $text = $this->getSimpleText();

        // Suchtext vom ersten Parameter wird nicht gefunden
        $this->assertFalse(Parser::findOne($text, 'FindeMichNicht', "<a href=", "</a>"));

        // Suchtext von einem Parametern ausser dem ersten und letzten Parameter wird nicht gefunden
        $this->assertFalse(Parser::findOne($text, '<div', "FindeMichNicht?", '"', '">', "</a>"));

        // Suchtext vom letzten Parameter wird nicht gefunden
        $this->assertFalse(Parser::findOne($text, '<div', "<a href=", '"', '">', "FindeMichNicht"));
    }

    /** @test */
    public function findOne_is_caseinsensitive()
    {
        $text = $this->getSimpleText();
        $expected = 'UPPERCASE TEXT';

        $this->assertEquals($expected, Parser::findOne($text, '<span class="uppercase">', '</span>'));
    }

    /** @test */
    public function findOne_with_empty_needle()
    {
        $text = $this->getSimpleText();

        // empty needle on the last position -> OK
        $this->assertEquals($text, Parser::findOne($text, ''));
        $this->assertEquals('
I am an error.
</span>
				', Parser::findOne($text, '<span class="text-warning">', '<span class="text-warning">', ''));

        // empty needle not on the last position -> NOT OK
        $this->assertFalse(Parser::findOne($text, '</span>', '', '<'));
        $this->assertFalse(Parser::findOne($text, '</span>', '', ''));
    }

    /** @test */
    public function findOne_with_linebreak()
    {
        $text = $this->getSimpleText();
        $expected = '<span class="text-warning">
I am an error.
';

        $search = '</div>


';
        $this->assertEquals($expected, Parser::findOne($text, $search, '</span>'));
        $this->assertEquals($expected, Parser::findOne($text, '</div>'.PHP_EOL.PHP_EOL.PHP_EOL, '</span>'));
    }

    /** @test */
    public function findMany_with_two_parameters_returns_the_texts_between_the_first_searchtext_at_the_end_and_the_last_searchtext_at_the_beginning()
    {
        $text = $this->getSimpleText();

        // Ein Text
        $this->assertEquals([ 'einfachen Link', 'Bing Schweiz' ], Parser::findMany($text, '</a>', '.bing.ch">'));

        // Mehrere Texte
        $this->assertEquals([ 'http://www.bing.ch', 'http://www.bing.ch', 'http://www.google.ch', 'http://www.duckduckgo.com' ], Parser::findMany($text, '">', '<a href="'));
    }

    /** @test */
    public function findMany_with_multiple_parameters_returns_the_texts_between_the_second_last_searchtext_at_the_end_and_the_last_searchtext_at_the_beginning()
    {
        $text = $this->getSimpleText();

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
        $text = $this->getSimpleText();

        // Suchtext vom ersten Parameter wird nicht gefunden
        $this->assertEquals([], Parser::findMany($text, '</a>', 'FindeMichNicht', '<a href='));

        // Suchtext von einem Parametern ausser dem ersten und letzten Parameter wird nicht gefunden
        $this->assertEquals([], Parser::findMany($text, '</a>', '<div', 'FindeMichNicht?', '"', '">'));

        // Suchtext vom letzten Parameter wird nicht gefunden
        $this->assertEquals([], Parser::findMany($text, 'FindeMichNicht', '<div', '<a href=', '"', '">'));
    }

    /** @test */
    public function findMany_is_caseinsensitive()
    {
        $text = $this->getSimpleText();
        $expected = [ 'UPPERCASE TEXT', 'UPPERCASE TEXT' ];

        $this->assertEquals($expected, Parser::findMany($text, '</span>', '<span class="uppercase">'));
    }

    /** @test */
    public function findMany_with_oneOrMore_empty_needles_returns_an_empty_array()
    {
        $text = $this->getSimpleText();

        $this->assertEquals([], Parser::findMany($text, '>', ''));
        $this->assertEquals([], Parser::findMany($text, '', '>'));
        $this->assertEquals([], Parser::findMany($text, '', '', '>'));
        $this->assertEquals([], Parser::findMany($text, '', '>', '>'));
        $this->assertEquals([], Parser::findMany($text, '>', '>', ''));
        $this->assertEquals([], Parser::findMany($text, '>', '', '>'));
        $this->assertEquals([], Parser::findMany($text, '', ''));
        $this->assertEquals([], Parser::findMany($text, '', '', ''));
    }

    /** @test */
    public function findMany_with_linebreak()
    {
        $text = $this->getSimpleText();
        $expected = [
            '<span class="text-warning">
I am an error.
',
            '<span class="text-warning">
I am an error.
'
        ];

        $search = '


';

        $this->assertEquals($expected, Parser::findMany($text, '</span>', $search));
    }
}