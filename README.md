# text-parser
It allows you to cut the needed part of a text in given text

## examples

### Find one
```html
<div class="id1">
	<table>
		<thead>
			<tr>
				<th>company</th>
				<th>urls</th>
				<th>zipcode & city</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Hegland GmbH</td>
				<td>
				    <ul>
				        <li>http://www.companylink1.ch</li>
				        <li>http://www.companylink2.ch</li>
				        <li>http://www.companylink3.ch</li>
                    </ul>
                </td>
				<td>8400 Winterthur</td>
			</tr>
		</tbody>		
	</table>
</div>
<ul>
<li>http://www.link1.ch</li>
<li>http://www.link2.ch</li>
<li>http://www.link3.ch</li>
</ul>
<div class="id2">
           ^^^^^^
	<table>
		<thead>
			<tr>
				<th>name</th>
				<th>street</th>
				<th>zipcode & city</th>
			</tr>
		</thead>
		<tbody>
		^^^^^^^
			<tr>
				<td>Roger Hegland</td>
				^^^^=============^^^^^
				<td>Châtelstrasse 13</td>
				<td>8355 Aadorf</td>
			</tr>
		</tbody>		
	</table>
</div>
```

In the following example we get the name "Roger Hegland":

```php
$name = Parser::findOne($text, '"id2">', '<tbody>', '<td>', '</td>');

/*
result = (string) 'Roger Hegland'
*/
```

### Find one backwards

Use `findOneBackwards()` when you know the text on the right side and want to search backwards for the left boundary.
The first search text is the right anchor. Each following search text is searched backwards from the start of the current match.

```php
$text = '<script>
    let settings = [{value:"6.0",label:"Zimmer"},{value:"125",label:"Wohnflaeche"},{value:"2025",label:"Baujahr"}];
</script>';

$rooms = Parser::findOneBackwards($text, '",label:"Zimmer"', '"');

/*
result = (string) '6.0'
*/
```

`bFindOne()` is available as a short alias for `findOneBackwards()`.

### Aliases

The descriptive method names are the primary API. Short aliases are available for compact parser calls:

```php
Parser::fO($text, '<td>', '</td>'); // findOne()
Parser::fM($text, '</a>', '<li>', '">'); // findMany()
Parser::bfO($text, '",label:"Zimmer"', '"'); // findOneBackwards()
```

### Find many

Please notice, that the first parameter is used for the end search.

In the following example we get all link names:

```html
<div class="id1">
	<table>
		<thead>
			<tr>
				<th>company</th>
				<th>urls</th>
				<th>zipcode & city</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Hegland GmbH</td>
				<td>
				    <ul>
				        <li><a href="http://www.companylink1.ch">companylink1</a></li>
				        ^^^^                                   ^^============^^^^
				        <li><a href="http://www.companylink2.ch">companylink2</a></li>
				        ^^^^                                   ^^============^^^^
				        <li><a href="http://www.companylink3.ch">companylink3</a></li>
				        ^^^^                                   ^^============^^^^
                    </ul>
                </td>
				<td>8400 Winterthur</td>
			</tr>
		</tbody>		
	</table>
</div>
<ul>
<li><a href="http://www.link1.ch">link1</a></li>
^^^^                            ^^=====^^^^
<li><a href="http://www.link2.ch">link2</a></li>
^^^^                            ^^=====^^^^
<li><a href="http://www.link3.ch">link3</a></li>
^^^^                            ^^=====^^^^
</ul>
<div class="id2">
	<table>
		<thead>
			<tr>
				<th>name</th>
				<th>street</th>
				<th>zipcode & city</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Roger Hegland</td>
				<td>Châtelstrasse 13</td>
				<td>8355 Aadorf</td>
			</tr>
		</tbody>		
	</table>
</div>
```

```php
Parser::findMany($text, '</a>', '<li>', '">' );

/*
result = array
[
    'companylink1',
    'companylink2',
    'companylink3',
    'link1',
    'link2',
    'link3'
]
*/
```

If you only need the link names in the table you can do something like this:

```html
<div class="id1">
	<table>
		<thead>
			<tr>
				<th>company</th>
				<th>urls</th>
				<th>zipcode & city</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Hegland GmbH</td>
				<td>
				    <ul>
				        <li><a href="http://www.companylink1.ch">companylink1</a></li>
				        ^^^^                                   ^^============^^^^
				        <li><a href="http://www.companylink2.ch">companylink2</a></li>
				        ^^^^                                   ^^============^^^^
				        <li><a href="http://www.companylink3.ch">companylink3</a></li>
				        ^^^^                                   ^^============^^^^
                    </ul>
                </td>
				<td>8400 Winterthur</td>
			</tr>
		</tbody>
	</table>
</div>
<ul>
<li><a href="http://www.link1.ch">link1</a></li>
<li><a href="http://www.link2.ch">link2</a></li>
<li><a href="http://www.link3.ch">link3</a></li>
</ul>
<div class="id2">
	<table>
		<thead>
			<tr>
				<th>name</th>
				<th>street</th>
				<th>zipcode & city</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Roger Hegland</td>
				<td>Châtelstrasse 13</td>
				<td>8355 Aadorf</td>
			</tr>
		</tbody>		
	</table>
</div>
```


```php
$text = Parser::findOne($text, '<tbody>', '</tbody>' );
Parser::findMany($text, '</a>', '<li>', '">' );

/*
result = array
[
    'companylink1',
    'companylink2',
    'companylink3',
]
*/
```
