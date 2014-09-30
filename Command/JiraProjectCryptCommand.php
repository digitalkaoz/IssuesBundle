<?php
/**
 * dashboard
 */

namespace Rs\IssuesBundle\Command;
use Rs\Issues\Jira\JiraTracker;
use Rs\Issues\Project;
use Rs\IssuesBundle\Synchronizer\JiraSynchronizer;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * JiraProjectCryptCommand
 * @author Robert Schönthal <robert.schoenthal@sinnerschrader.com> 
 */
class JiraProjectCryptCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('issues:jira-crypt')
            ->setDescription('creates an encrypted Jira DSN')
            ->setHelp(<<<EOF
EOF
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getHelper('dialog');
        /** @var DialogHelper $io */

        $username = $io->ask($output, '<comment>User     : </comment>');
        $password = $io->askHiddenResponse($output, '<comment>Password : </comment>');
        $project = $io->ask($output, '<comment>Project  : </comment>');
        $host = $io->ask($output, '<comment>Host    : </comment>');


        $tracker = new JiraTracker();

        $valid = false;

        $tracker->connect($username, $password, $host);
        $p = $tracker->getProject($project);

        if ($p instanceof Project) {
            $valid = true;
        }

        if (true === $valid) {
            $key = JiraSynchronizer::encrypt($username, $password);

            $output->writeln('<info>Paste this in your projects config</info>');
            $output->writeln($key.' '.$host. ' '.$project);
        }
    }
}
