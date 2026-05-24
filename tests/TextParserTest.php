<?php

namespace TextParser\Tests;

use PHPUnit\Framework\TestCase;
use TextParser\Parser;

class TextParserTest extends TestCase
{
    private function getSimpleText(): string
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
                
                <script>
                    let settings = [{value:"6.0",label:"Zimmer"},{value:"125",label:"Wohnflaeche"},{value:"2025",label:"Baujahr"}];
                </script>


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
        $this->assertEquals($expected, Parser::fO($text, 'ng.ch">', "</a>"));
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
        $expected = '<span class="text-warning">'.PHP_EOL.'I am an error.'.PHP_EOL;
        $text = '<div>content</div>'.PHP_EOL.PHP_EOL.PHP_EOL.$expected.'</span>';

        $search = '</div>'.PHP_EOL.PHP_EOL.PHP_EOL;
        $this->assertEquals($expected, Parser::findOne($text, $search, '</span>'));
    }

    /** @test */
    public function bFindOne_returns_the_text_between_the_previous_searchtext_and_the_anchor()
    {
        $text = $this->getSimpleText();

        $this->assertEquals('6.0', Parser::findOneBackwards($text, '",label:"Zimmer"', '"'));
        $this->assertEquals('6.0', Parser::bFindOne($text, '",label:"Zimmer"', '"'));
        $this->assertEquals('6.0', Parser::bfO($text, '",label:"Zimmer"', '"'));
    }

    /** @test */
    public function findOneBackwards_is_caseinsensitive()
    {
        $text = $this->getSimpleText();

        $this->assertEquals('6.0', Parser::findOneBackwards($text, '",LABEL:"ZIMMER"', '"'));
    }

    /** @test */
    public function findOneBackwards_can_search_backwards_over_multiple_markers()
    {
        $text = $this->getSimpleText();

        $this->assertEquals(
            '6.0',
            Parser::findOneBackwards($text, ',label:"Wohnflaeche"', 'label:"Zimmer"', '"', '"')
        );
    }

    /** @test */
    public function findOneBackwards_starts_at_the_last_anchor_when_the_anchor_occurs_multiple_times()
    {
        $breadcrumbs = '
            <ul class="breadcrumbs" itemscope itemtype="https://schema.org/BreadcrumbList">
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <a itemprop="item" href="/">
                        <span itemprop="name">Quoka</span>
                    </a>
                    <meta itemprop="position" content="1" />
                </li>
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <a itemprop="item" href="/anzeigen/immobilienmarkt/immobilien/bauernhaeuser-hoefe-gueter/">
                        <span itemprop="name">Bauernh&#228;user, H&#246;fe, G&#252;ter</span>
                    </a>
                    <meta itemprop="position" content="5" />
                </li>
            </ul>
        ';

        $this->assertEquals(
            'Bauernh&#228;user, H&#246;fe, G&#252;ter',
            Parser::bFindOne($breadcrumbs, '<meta itemprop="position"', '</span>', '">')
        );
    }

    /** @test */
    public function findOneBackwards_returns_false_when_a_backwards_searchtext_could_not_be_found()
    {
        $text = 'value:6.0",label:"Zimmer"';

        $this->assertFalse(Parser::findOneBackwards($text, '",label:"Zimmer"', '"'));
    }

    /** @test */
    public function findOneBackwards_returns_false_when_less_than_two_searchtexts_are_given()
    {
        $text = 'value:"6.0",label:"Zimmer"';

        $this->assertFalse(Parser::findOneBackwards($text));
        $this->assertFalse(Parser::findOneBackwards($text, '",label:"Zimmer"'));
    }

    /** @test */
    public function findOneBackwards_returns_false_when_a_searchtext_is_empty()
    {
        $text = 'value:"6.0",label:"Zimmer"';

        $this->assertFalse(Parser::findOneBackwards($text, '",label:"Zimmer"', ''));
        $this->assertFalse(Parser::findOneBackwards($text, '', '"'));
    }

    /** @test */
    public function findMany_with_two_parameters_returns_the_texts_between_the_first_searchtext_at_the_end_and_the_last_searchtext_at_the_beginning()
    {
        $text = $this->getSimpleText();

        // Ein Text
        $this->assertEquals([ 'einfachen Link', 'Bing Schweiz' ], Parser::findMany($text, '</a>', '.bing.ch">'));
        $this->assertEquals([ 'einfachen Link', 'Bing Schweiz' ], Parser::fM($text, '</a>', '.bing.ch">'));

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
    public function findMany_moves_to_the_end_of_the_current_match_before_searching_again()
    {
        $text = 'prefix <span>prefix</span> prefix <span>prefix</span>';

        $this->assertEquals([ 'prefix', 'prefix' ], Parser::findMany($text, '</span>', '<span>'));
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
