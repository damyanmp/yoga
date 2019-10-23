<?php

namespace Yoga {

    class TraitsTest extends \Yoga\Test {

        public function testIsTraitUsed() {
            $traitsService = Traits::service();
            $o1 = new \TraitTest\Class1;
            $o2 = new \TraitTest\Class2;
            $this->assertEquals(true, $traitsService->isTraitUsed(\TraitTest\Class1::class, \TraitTest\Trait1::class));
            $this->assertEquals(true, $traitsService->isTraitUsed($o1, \TraitTest\Trait1::class));
            $this->assertEquals(true, $traitsService->isTraitUsed($o2, \TraitTest\Trait1::class));
            $this->assertEquals(false, $traitsService->isTraitUsed(\TraitTest\Class1::class, \TraitTest\Trait2::class));
            $this->assertEquals(false, $traitsService->isTraitUsed($o1, \TraitTest\Trait2::class));
            $this->assertEquals(false, $traitsService->isTraitUsed($o2, \TraitTest\Trait2::class));
        }

    }

}

namespace TraitTest {

    trait Trait1 {}

    trait Trait2 {}

    class Class1 {

        use Trait1;

    }

    class Class2 extends Class1 {

    }

}