<?php
namespace Samurai\Module\Task\Factory;

use Pimple\Container;
use Samurai\Module\ModuleCommand;
use Samurai\Module\Task\Enabling;
use Samurai\Module\Task\Installing;
use Samurai\Module\Task\Listing;
use Samurai\Module\Task\Removing;
use Samurai\Module\Task\Running;
use Samurai\Module\Task\Saving;
use Samurai\Module\Task\Updating;
use Samurai\Task\ITask;
use SimilarText\Finder;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class TaskFactory
 * @package Samurai\Alias\Task\Factory
 * @author Raphaël Lefebvre <raphael@raphaellefebvre.be>
 */
class ModuleManagementTaskFactory
{
    /**
     * @param InputInterface $input
     * @param Container $services
     * @return ITask
     */
    public static function create(InputInterface $input, Container $services)
    {

        if(!$input->getArgument('action')){
            throw new \InvalidArgumentException(sprintf('An action param is required: %s', json_encode(ModuleCommand::$actions)));
        }

        if($input->getArgument('action') === 'list'){
            return new Listing($services);
        }
        if($input->getArgument('action') === 'install'){
            return new Installing($services);
        }
        if($input->getArgument('action') === 'update'){
            return new Updating($services);
        }
        if($input->getArgument('action') === 'rm' || $input->getArgument('action') === 'remove'){
            if(!$input->getArgument('name')){
                throw new \InvalidArgumentException('name param is mandatory for this action');
            }
            return new Removing($services);
        }
        if($input->getArgument('action') === 'enable'){
            if(!$input->getArgument('name')){
                throw new \InvalidArgumentException('name param is mandatory for this action');
            }
            return new Enabling($services);
        }
        if($input->getArgument('action') === 'disable'){
            if(!$input->getArgument('name')){
                throw new \InvalidArgumentException('name param is mandatory for this action');
            }
            return new Enabling($services);
        }
        if($input->getArgument('action') === 'run'){
            return new Running($services);
        }

        $textFinder = new Finder($input->getArgument('action'), ModuleCommand::$actions);
        throw new \InvalidArgumentException(sprintf(
            'Action "%s" not supported. Did you mean "%s"?',
            $input->getArgument('action'),
            $textFinder->first()
        ));
    }
}
