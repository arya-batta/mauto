<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Test\Model;

use Mautic\EmailBundle\Model\TransportType;

class TransportTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTransportTypes()
    {
        $transportType = new TransportType();

        $expected = [
            'le.transport.amazon'       => 'le.email.config.mailer_transport.amazon',
            'le.transport.elasticemail' => 'le.email.config.mailer_transport.elasticemail',
            'gmail'                         => 'le.email.config.mailer_transport.gmail',
            'le.transport.mandrill'     => 'le.email.config.mailer_transport.mandrill',
            'le.transport.mailjet'      => 'le.email.config.mailer_transport.mailjet',
            'smtp'                          => 'le.email.config.mailer_transport.smtp',
            'mail'                          => 'le.email.config.mailer_transport.mail',
            'le.transport.postmark'     => 'le.email.config.mailer_transport.postmark',
            'le.transport.sendgrid'     => 'le.email.config.mailer_transport.sendgrid',
            'le.transport.sendgrid_api' => 'le.email.config.mailer_transport.sendgrid_api',
            'sendmail'                      => 'le.email.config.mailer_transport.sendmail',
            'le.transport.sparkpost'    => 'le .email.config.mailer_transport.sparkpost',
        ];

        $this->assertSame($expected, $transportType->getTransportTypes());
    }

    public function testSmtpService()
    {
        $transportType = new TransportType();

        $expected = '"smtp"';

        $this->assertSame($expected, $transportType->getSmtpService());
    }

    public function testAmazonService()
    {
        $transportType = new TransportType();

        $expected = '"le.transport.amazon"';

        $this->assertSame($expected, $transportType->getAmazonService());
    }

    public function testMailjetService()
    {
        $transportType = new TransportType();

        $expected = '"le.transport.mailjet"';

        $this->assertSame($expected, $transportType->getMailjetService());
    }

    public function testRequiresLogin()
    {
        $transportType = new TransportType();

        $expected = '"le.transport.mandrill",
                "le.transport.mailjet",
                "le.transport.sendgrid",
                "le.transport.elasticemail",
                "le.transport.amazon",
                "le.transport.postmark",
                "gmail"';

        $this->assertSame($expected, $transportType->getServiceRequiresLogin());
    }

    public function testDoNotNeedLogin()
    {
        $transportType = new TransportType();

        $expected = '"mail",
                "sendmail",
                "le.transport.sparkpost",
                "le.transport.sendgrid_api"';

        $this->assertSame($expected, $transportType->getServiceDoNotNeedLogin());
    }

    public function testRequiresPassword()
    {
        $transportType = new TransportType();

        $expected = '"le.transport.elasticemail",
                "le.transport.sendgrid",
                "le.transport.amazon",
                "le.transport.postmark",
                "le.transport.mailjet",
                "gmail"';

        $this->assertSame($expected, $transportType->getServiceRequiresPassword());
    }

    public function testDoNotNeedPassword()
    {
        $transportType = new TransportType();

        $expected = '"mail",
                "sendmail",
                "le.transport.sparkpost",
                "le.transport.mandrill",
                "le.transport.sendgrid_api"';

        $this->assertSame($expected, $transportType->getServiceDoNotNeedPassword());
    }

    public function testRequiresApiKey()
    {
        $transportType = new TransportType();

        $expected = '"le.transport.sparkpost",
                "le.transport.mandrill",
                "le.transport.sendgrid_api"';

        $this->assertSame($expected, $transportType->getServiceRequiresApiKey());
    }
}
