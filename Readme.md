[![Build Status](https://api.travis-ci.org/BlueM/PHP-JavaScript-Serializer.svg?branch=master)](https://travis-ci.org/BlueM/PHP-JavaScript-Serializer)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/a2a2401a-906e-4f9d-b889-416890a620ca/mini.png)](https://insight.sensiolabs.com/projects/a2a2401a-906e-4f9d-b889-416890a620ca)

Overview
========

This library provides a simple, dependency-free serialization of a PHP variable value / datastructure to strings which can be interpreted as a JavaScript value.

The most typical use case will be the automated generation of JavaScript code in cases where the amount of data is notably and therefore serializing to JSON would suffer from the deserialization penalty and/or the larger size of JSON data.

While I would have expected there are already some simililar libraries on packagist.org, I did not find any that are *not* bound to a specific use case, dataset or framework. If I missed something, please do not hesitate to contact me.


Installation
-------------
Add `"bluem/javascript-serializer": "^1.0"` to the requirements in your `composer.json` file or run `composer require "bluem/javascript-serializer"` at the shell.

As this library uses [semantic versioning](https://semver.org), you will get fixes and feature additions when running `composer update`, but not changes which break the API.


Usage
----

Instantiate the class, call the `serialize()` method with any variable and use the return type. That’s it.

    $jss = new \BlueM\JavaScriptSerializer();
    $result = $jss->serialize($myVariable);

The following datatypes can be handled:

* `null`
* `string`
* `float`
* `int`
* `array`
* `\DateTime` (converted to a JavaScript `Date` constructor call)
* `object` – if the object implements the `\JsonSerializable` interface, has a public `toArray` method or has a public `__toString` method. (The mentioned order is exactly the order in which the code performs the checks.)


Examples
--------

**Example 1:** Scalar PHP value

Input PHP data:

    $var = 'Hello world';

Value returned from `$serializer->serialize($var)` call will be this string:

    'Hello world'

**Example 2:** Array with numeric keys

Input PHP data:

    $var = ['A', 'B', 3.14, 4711];

Value returned from `$serializer->serialize($var)` call will be this string:

    ['A', 'B', 3.14, 4711]


**Example 3:** Nested array
 
Input PHP data:

    [
        'foo'    => 'bar',
        'nested' => [
            'pi'     => 3.14,
            'key'    => null,
            'abc'    => "String \" with 'quotes'",
            'My key' => 'Hello world',
            'bar'    => [
                'key'  => 'A',
                'code' => 65,
            ],
        ]
    ]

Value returned from `$serializer->serialize($var)` call will be this string:

    {foo: 'bar', nested: {pi: 3.14, key: null, abc: 'String " with \'quotes\'', 'My key': 'Hello world', bar: {key: 'A', code: 65}}}


Known issues
------------
* When creating object properties, strings that can be used as properties without quotes are inserted verbatim, without quotes. However, this decision is based on a very simple RegEx, which not only ignores the existence of ECMAScript 2015 Symbols, but also [other property names that can be used without quotes](https://mothereff.in/js-properties#12e34). This has no influence on using the code in JavaScript, but only on the code size. But chances are you will use some sort of minification, so this should not be a problem.


ToDo
----
* In addition to the current, compact format, a “pretty-print” format should be choosable.


Version History
=================

1.1, 2019-03-28
---------------
* Native PHP `\DateTime` instances are converted to native JavaScript `Date` constructor calls

1.0.1, 2017-10-14
-----------------
* Improve exception message (explicitly mention class name) when object is not serializable.
* Improve doc comments and Readme.

1.0, 2017-05-04
----------------
* First public version. Nothing else to say.


Author & License
=================
This code was written by Carsten Blüm (www.bluem.net) and licensed under the BSD 2-Clause license.
