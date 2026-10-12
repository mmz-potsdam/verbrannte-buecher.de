<?php

// src/Command/ArticleAuthorCommand.php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Lookup author(s) and translator and insert/update corresponding Person.
 */
class ArticleAuthorCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->setName('article:author')
            ->setDescription('Extract Author(s) and Translator')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'TEI file'
            )
            ->addOption(
                'insert-missing',
                null,
                InputOption::VALUE_NONE,
                'If set, missing entries will be added to person'
            )
            ->addOption(
                'update',
                null,
                InputOption::VALUE_NONE,
                'If set, an existing person will be updated'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fname = $input->getArgument('file');

        $fs = new Filesystem();

        if (!$fs->exists($fname)) {
            $output->writeln(sprintf('<error>%s does not exist</error>', $fname));

            return Command::FAILURE;
        }

        $teiHelper = new \TeiEditionBundle\Utils\TeiHelper();

        $article = $teiHelper->analyzeHeader($fname);

        if (false === $article) {
            $output->writeln(sprintf('<error>%s could not be loaded</error>', $fname));
            foreach ($teiHelper->getErrors() as $error) {
                $output->writeln(sprintf('<error>  %s</error>', trim($error->message)));
            }

            return Command::FAILURE;
        }

        if (empty($article->author) && empty($article->translator)) {
            $output->writeln(sprintf('<info>No author or translator found in %s</info>', $fname));

            return Command::FAILURE;
        }

        $persons = !empty($article->author) ? $article->author : [];
        if (!empty($article->translator)) {
            $persons[] = $article->translator;
        }

        $output->writeln($this->jsonPrettyPrint($persons));

        $ret = Command::SUCCESS;

        if ($input->getOption('insert-missing')) {
            foreach ($persons as $author) {
                $person = null;

                $slug = $author->getSlug();
                if (empty($slug)) {
                    // check if we have gnd
                    $gnd = $author->getGnd();

                    if (empty($gnd)) {
                        $ret = Command::FAILURE;

                        $output->writeln(sprintf('<info>Skip author with empty slug and GND in %s</info>', $fname));
                        continue;
                    }

                    $uri = 'https://d-nb.info/gnd/' . $gnd;
                    $this->insertMissingPerson($uri);
                    $person = $this->findPersonByUri($uri);
                }
                else {
                    $person = $this->findPersonBySlug($slug);
                }

                if (is_null($person)) {
                    // neither find nor insert (if GND was given) succeeded
                    $ret = Command::FAILURE;

                    $value = !empty($slug) ? $slug : $gnd;
                    $output->writeln(sprintf(
                        '<error>No user found for %s</error>',
                        trim($value)
                    ));
                }
            }
        }

        return $ret;
    }

    protected function findPersonBySlug($slug)
    {
        return $this->em->getRepository('TeiEditionBundle\Entity\Person')->findOneBySlug($slug);
    }
}
