<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rs\IssuesBundle\Command;

use Rs\IssuesBundle\Storage\Storage;
use Rs\IssuesBundle\Synchronizer\Synchronizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to synchronize Repositories
 *
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class SynchronizeCommand extends Command
{
    /**
     * @var Storage
     */
    private $storage;
    /**
     * @var Synchronizer[]
     */
    private $synchronizers = array();

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('issues:sync')
            ->setDescription('Synchronize configured repositories into a storage')
            ->setHelp(<<<EOF
EOF
            );
    }

    /**
     * @param Storage $storage
     */
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;

        parent::__construct();
    }

    /**
     * @param Synchronizer $synchronizer
     */
    public function addSynchronizer(Synchronizer $synchronizer)
    {
        $this->synchronizers[] = $synchronizer;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->storage->cleanup();

        foreach ($this->synchronizers as $synchronizer) {
            $synchronizer->synchronize(function($message) use($output) {
                $message = preg_replace('/"(.*)"/', '<info>$1</info>', $message);
                $message = preg_replace('/\!(.*)\!/', '<error>$1</error>', $message);

                $output->writeln($message);
            });
        }
    }
}
