<?php

namespace {

    class PicklerTest extends \Yoga\Test {

        public function testFormatReadableJson() {
            $pickler = \Yoga\Pickler::service();
            $time = '16-Jun-1976 12:34:56pm';
            \Yoga\DateTime::freezeTime(strtotime($time));
            $testObject = new \PicklerTest\TestSubNamespace\TestClass;
            $expectedLines = [
                '{                                   // \\PicklerTest\\TestSubNamespace\\TestClass',
                '    int: 1,                         // int',
                '    float: 2.1,                     // float',
                '    string: "three",                // string',
                '    time: 203776496,                // ' . $time,
                '    arrayInt: [',
                '        4,                          // int',
                '        5,                          // int',
                '        6                           // int',
                '    ],',
                '    arrayMixed: [',
                '        "very[\'tricky\'] \"seven\"", // string',
                '        [',
                '            "eight",                // string',
                '            "nine"                  // string',
                '        ],',
                '        {                           // \\PicklerTest\\TestSubNamespace\\TestClass2',
                '            int2: 22                // int',
                '        }',
                '    ],',
                '    object: {                       // \\PicklerTest\\TestSubNamespace\\TestClass3',
                '        int3: 333                   // int',
                '    },',
                '    enum: 2,                        // \\PicklerTest\\TestSubNamespace\\TestEnum::TEST2',
                '    emptyArray: []',
                '}'
            ];
            $this->assertEquals(
                $expectedLines,
                $pickler->getReadableJsonLines($testObject)
            );
        }

    }

}

namespace PicklerTest\TestSubNamespace {

    class TestClass {
        public $int = 1;
        public $float = 2.1;
        public $string = 'three';
        public $time;
        public $arrayInt = [4, 5, 6];
        public $arrayMixed;
        public $object;
        public $enum;
        public $emptyArray = [];

        public function __construct() {
            $this->time = new \Yoga\DateTime;
            $this->arrayMixed = [
                'very[\'tricky\'] "seven"',
                ['eight', 'nine'],
                new TestClass2
            ];
            $this->object = new TestClass3;
            $this->enum = TestEnum::wrap(TestEnum::TEST2);
        }
    }

    class TestClass2 {
        public $int2 = 22;
    }

    class TestClass3 {
        public $int3 = 333;
    }

    class TestEnum extends \Yoga\Enum {
        const TEST1 = 1;
        const TEST2 = 2;
    }

}