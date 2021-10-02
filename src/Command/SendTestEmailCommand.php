<?php
declare(strict_types=1);

namespace Communication\Command;

use Communication\Notification\GenericCommunication;
use Communication\Recipient;
use Communication\RecipientChannels;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mime\Address;

final class SendTestEmailCommand extends Command
{
    protected static $defaultName = 'communication:send-test-email';

    public function __construct(
        private GenericCommunication $notification,
    )  {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'Where to send the test email',
            )
            ->addOption(
                'from',
                'f',
                InputOption::VALUE_OPTIONAL,
                'The email address the test email comes from',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        if ($from = $input->getOption('from')) {
            $from = new Address($from);
        }

        $recipient = (new Recipient())
            ->setEmail($email);
        $recipientChannels = (new RecipientChannels())
            ->addRecipientsToChannel('email', $recipient);

        $this->notification
            ->setRecipientChannels([$recipientChannels])
            ->dispatch('Test Email', 'This is a test', $from);

        return Command::SUCCESS;
    }
}
