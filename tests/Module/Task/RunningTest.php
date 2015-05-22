<?php
namespace Samurai\Module\Task;

use Pimple\Container;
use Samurai\Module\Module;
use Samurai\Module\Modules;
use Samurai\Task\ITask;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class RunningTest
 * @package Samurai\Module\Task
 * @author Raphaël Lefebvre <raphael@raphaellefebvre.be>
 */
class RunningTest extends \PHPUnit_Framework_TestCase
{

    public function testExecuteForAll()
    {
        $moduleManager = $this->provideModuleManagerForAll();

        $services = new Container();
        $services['module_manager'] = function() use($moduleManager){
            return $moduleManager;
        };

        $input = $this->provideInput([]);
        $output = new BufferedOutput();

        $task = new Running($services);
        $this->assertSame(ITask::NO_ERROR_CODE, $task->execute($input, $output));

        $this->assertSame("Running 2 module(s)\nABC", $output->fetch());
    }

    public function testExecuteForOne()
    {
        $moduleManager = $this->provideModuleManagerForOne();

        $services = new Container();
        $services['module_manager'] = function() use($moduleManager){
            return $moduleManager;
        };

        $input = $this->provideInput(['name' => 'moduleA']);
        $output = new BufferedOutput();

        $task = new Running($services);
        $this->assertSame(ITask::NO_ERROR_CODE, $task->execute($input, $output));

        $this->assertSame("Running the module \"name of B\"\nAB", $output->fetch());
    }

    /**
     * @return \Samurai\Module\ModuleManager
     */
    private function provideModuleManagerForAll()
    {

        $moduleManager = $this->getMockBuilder('Samurai\Module\ModuleManager')->disableOriginalConstructor()->getMock();

        $moduleManager->expects($this->once())
            ->method('getAll')
            ->will($this->returnValue($this->providesModules()));

        return $moduleManager;
    }

    /**
     * @return \Samurai\Module\ModuleManager
     */
    private function provideModuleManagerForOne()
    {

        $moduleManager = $this->getMockBuilder('Samurai\Module\ModuleManager')->disableOriginalConstructor()->getMock();

        $moduleManager->expects($this->once())
            ->method('has')
            ->with('moduleA')
            ->will($this->returnValue(true));

        $moduleManager->expects($this->once())
            ->method('get')
            ->with('moduleA')
            ->will($this->returnValue($this->providesModules()[0]));

        return $moduleManager;
    }

    /**
     * @return Modules
     */
    private function providesModules()
    {
        $modules = new Modules();

        $moduleA = new Module();
        $moduleA->setName('name of A');
        $moduleA->setTasks([
            'Samurai\Module\resources\TaskA',
            'Samurai\Module\resources\TaskB',
        ]);
        $modules[] = $moduleA;

        $moduleB = new Module();
        $moduleA->setName('name of B');
        $moduleB->setTasks([
            'Samurai\Module\resources\TaskC',
        ]);
        $modules[] = $moduleB;

        return $modules;
    }

    /**
     * @param array $args
     * @return ArrayInput
     */
    private function provideInput(array $args)
    {
        return new ArrayInput(
            $args,
            new InputDefinition([
                new InputArgument('name'),
            ])
        );
    }
}
