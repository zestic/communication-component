<?php
declare(strict_types=1);

namespace Communication\Context;

use Symfony\Component\Mime\Address;

interface CommunicationContextInterface
{
    public function getFrom(): Address;
    public function setFrom($from);
    public function getRecipients(): array;
    public function setRecipients($recipients);
}
