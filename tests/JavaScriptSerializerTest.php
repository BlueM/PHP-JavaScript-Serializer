<?php

namespace BlueM;

use PHPUnit\Framework\TestCase;

class SerializableClass implements \JsonSerializable
{
    /**
     * @var
     */
    private $serializationReturnValue;

    public function __construct($serializationReturnValue)
    {
        $this->serializationReturnValue = $serializationReturnValue;
    }

    public function jsonSerialize()
    {
        return $this->serializationReturnValue;
    }
}

class ClassWithToArrayMethod
{
    public function toArray(): array
    {
        return ['foo' => 'Bar'];
    }
}

class ClassWithToStringMethod
{
    public function __toString(): string
    {
        return 'foo';
    }
}

/**
 * @group unit
 * @covers \BlueM\JavaScriptSerializer
 */
class JavaScriptSerializerTest extends TestCase
{
    /**
     * @test
     */
    public function nullIsSerializedToAStringContainingNull()
    {
        $jss = new JavaScriptSerializer();
        static::assertSame('null', $jss->serialize(null));
    }

    /**
     * @test
     */
    public function anIntegerIsSerializedToAStringContainingTheInteger()
    {
        $jss = new JavaScriptSerializer();
        static::assertSame('123891', $jss->serialize(123891));
    }

    /**
     * @test
     */
    public function aFloatIsSerializedToAStringContainingTheFloat()
    {
        $jss = new JavaScriptSerializer();
        static::assertSame('17.38', $jss->serialize(17.38));
    }

    /**
     * @test
     */
    public function trueIsSerializedToAStringContainingTrue()
    {
        $jss = new JavaScriptSerializer();
        static::assertSame('true', $jss->serialize(true));
    }

    /**
     * @test
     */
    public function falseIsSerializedToAStringContainingFalse()
    {
        $jss = new JavaScriptSerializer();
        static::assertSame('false', $jss->serialize(false));
    }

    /**
     * @test
     */
    public function aStringIsSerializedToAStringWhereBackslashesAndSingleQuotesAreEscaped()
    {
        $jss = new JavaScriptSerializer();

        $input = <<<'INPUT'
<p>Foobar Foo'bar Foo"ba\\r Foo\'bar
Süßholz € ➔</p>
INPUT;

        $expected = <<<'EXPECTED'
'<p>Foobar Foo\'bar Foo"ba\\\\r Foo\\\'bar\nSüßholz € ➔</p>'
EXPECTED;

        static::assertSame($expected, $jss->serialize($input));
    }

    /**
     * @test
     */
    public function anEmptyArrayIsSerializedToAStringContainingSquareBrackets()
    {
        $jss = new JavaScriptSerializer();
        static::assertSame('[]', $jss->serialize([]));
    }

    /**
     * @test
     */
    public function anIndexedArrayIsSerializedToAStringContainingAListOfValuesInSquareBrackets()
    {
        $jss = new JavaScriptSerializer();

        $expected = <<<'EXPECTED'
[1, 2, 3, 'abc', '5', 92.38, 'Foo\'Bar', 'Foo"Bar', {myprop: 'Myvalue'}]
EXPECTED;

        static::assertSame(
            $expected,
            $jss->serialize([1, 2, 3, 'abc', '5', 92.38, 'Foo\'Bar', 'Foo"Bar', ['myprop' => 'Myvalue']])
        );
    }

    /**
     * @test
     */
    public function anAssociatedArrayIsSerializedToAStringContainingAJavascriptObject()
    {
        $jss = new JavaScriptSerializer();

        $expected = <<<'EXPECTED'
{foo: 'Bar'}
EXPECTED;

        static::assertSame(
            $expected,
            $jss->serialize(['foo' => 'Bar'])
        );
    }

    /**
     * @test
     */
    public function aNestedDataStructureIsSerializedToAJavascriptObject()
    {
        $jss = new JavaScriptSerializer();

        $expected = <<<'EXPECTED'
{__default: {id: 1, 'non-ASCII \'key': 'Value', foo: [2, 3.14, 128], i18n: {key1: 'A', key1Code: 65}}}
EXPECTED;

        $input = [
            '__default' =>
                [
                    'id'            => 1,
                    "non-ASCII 'key" => 'Value',
                    'foo'           => [2, 3.14, 128],
                    'i18n'          =>
                        [
                            'key1'     => 'A',
                            'key1Code' => 65,
                        ],
                ]
        ];

        static::assertSame(
            $expected,
            $jss->serialize($input)
        );

    }

    /**
     * @test
     */
    public function anInstanceOfAClassContainingAToArrayMethodIsConvertedUsingThatMethod()
    {
        $jss = new JavaScriptSerializer();
        static::assertSame("{foo: 'Bar'}", $jss->serialize(new ClassWithToArrayMethod()));
    }

    /**
     * @test
     */
    public function anInstanceOfAClassContainingAToStringMethodIsConvertedUsingThatMethod()
    {
        $jss = new JavaScriptSerializer();
        static::assertSame('foo', $jss->serialize(new ClassWithToStringMethod()));
    }

    /**
     * @test
     */
    public function aStringReturnedFromanInstanceOfAJsonSerializableClassIsSerialized()
    {
        $jss = new JavaScriptSerializer();
        static::assertSame('--serialized--', $jss->serialize(new SerializableClass('--serialized--')));
    }

    /**
     * @test
     */
    public function anArrayReturnedFromanInstanceOfAJsonSerializableClassIsSerialized()
    {
        $jss = new JavaScriptSerializer();
        static::assertSame("{'a': 'B'}", $jss->serialize(new SerializableClass(['a' => 'B'])));
    }

    /**
     * @test
     */
    public function aBooleanReturnedFromanInstanceOfAJsonSerializableClassIsSerialized()
    {
        $jss = new JavaScriptSerializer();
        static::assertSame('true', $jss->serialize(new SerializableClass(true)));
    }

    /**
     * @test
     */
    public function aFloatReturnedFromanInstanceOfAJsonSerializableClassIsSerialized()
    {
        $jss = new JavaScriptSerializer();
        static::assertSame('3.14', $jss->serialize(new SerializableClass(3.14)));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot serialize a variable of type resource
     */
    public function anUnserializableVariableThrowsAnException()
    {
        $jss = new JavaScriptSerializer();
        static::assertSame("{'foo': 'Bar'}", $jss->serialize(fopen(__FILE__, 'r')));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage DateTime cannot be serialized
     * @expectedExceptionMessage neither implements JsonSerializable, nor
     */
    public function anInstanceOfAnUnserializableClassThrowsAnException()
    {
        $jss = new JavaScriptSerializer();
        static::assertSame("{'foo': 'Bar'}", $jss->serialize(new \DateTime()));
    }
}
