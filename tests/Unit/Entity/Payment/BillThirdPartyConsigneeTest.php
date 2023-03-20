<?php

declare(strict_types=1);

namespace Rawilk\Ups\Tests\Unit\Entity\Payment;

use Rawilk\Ups\Entity\Address\Address;
use Rawilk\Ups\Entity\Payment\BillThirdPartyConsignee;
use Rawilk\Ups\Entity\Payment\ThirdParty;
use Rawilk\Ups\Tests\TestCase;

class BillThirdPartyConsigneeTest extends TestCase
{
    /** @test */
    public function converts_to_xml(): void
    {
        $expected = <<<'XML'
        <BillThirdPartyConsignee>
            <AccountNumber>123</AccountNumber>
            <ThirdParty>
                <Address>
                    <City>foo</City>
                </Address>
            </ThirdParty>
        </BillThirdPartyConsignee>
        XML;

        $entity = new BillThirdPartyConsignee([
            'account_number' => '123',
            'third_party' => new ThirdParty([
                'address' => new Address([
                    'city' => 'foo',
                ]),
            ]),
        ]);

        self::assertXmlStringEqualsXmlString(
            $expected,
            $entity->toSimpleXml(null, false)->asXML()
        );
    }
}
