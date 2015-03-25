<?php
namespace Samurai\Composer;

use TRex\Cli\Executor;

/**
 * Class ComposerTest
 * @package Samurai\Composer
 * @author Raphaël Lefebvre <raphael@raphaellefebvre.be>
 */
class ComposerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateProject()
    {
        $project = new Project();

        $executor = $this->getMockBuilder('TRex\Cli\Executor')->getMock();
        $executor->expects($this->never())
            ->method('flush')
            ->will($this->returnValue('result'));

        $composer = new Composer($project, $executor);

        $this->setExpectedException('\InvalidArgumentException', 'The bootstrap of the project is not defined');
        $composer->createProject();
    }

    public function testCreateProjectWithBootstrap()
    {
        $project = new Project();
        $project->setBootstrapName('vendor/package');

        $executor = $this->getMockBuilder('TRex\Cli\Executor')->getMock();
        $executor->expects($this->once())
            ->method('flush')
            ->with('composer create-project vendor/package')
            ->will($this->returnValue('result'));

        $composer = new Composer($project, $executor);
        $this->assertSame('result', $composer->createProject());
    }

    public function testCreateProjectWithBootstrapAndOptions()
    {
        $project = new Project();
        $project->setBootstrapName('vendor/package');

        $executor = $this->getMockBuilder('TRex\Cli\Executor')->getMock();
        $executor->expects($this->once())
            ->method('flush')
            ->with('composer create-project vendor/package --repository-url=url')
            ->will($this->returnValue('result'));

        $composer = new Composer($project, $executor);
        $this->assertSame('result', $composer->createProject(['repository-url' => 'url']));
    }

    public function testCreateProjectWithDirectoryPath()
    {
        $project = new Project();
        $project->setBootstrapName('vendor/package');
        $project->setDirectoryPath('dir/path');

        $executor = $this->getMockBuilder('TRex\Cli\Executor')->getMock();
        $executor->expects($this->once())
            ->method('flush')
            ->with('composer create-project vendor/package dir/path')
            ->will($this->returnValue('result'));

        $composer = new Composer($project, $executor);
        $this->assertSame('result', $composer->createProject());
    }

    public function testCreateProjectWithDirectoryPathAndOptions()
    {
        $project = new Project();
        $project->setBootstrapName('vendor/package');
        $project->setDirectoryPath('dir/path');

        $executor = $this->getMockBuilder('TRex\Cli\Executor')->getMock();
        $executor->expects($this->once())
            ->method('flush')
            ->with('composer create-project vendor/package dir/path --repository-url=url')
            ->will($this->returnValue('result'));

        $composer = new Composer($project, $executor);
        $this->assertSame('result', $composer->createProject(['repository-url' => 'url']));
    }

    public function testCreateProjectWithVersion()
    {
        $project = new Project();
        $project->setBootstrapName('vendor/package');
        $project->setBootstrapVersion('1.0.0');
        $project->setDirectoryPath('dir/path');

        $executor = $this->getMockBuilder('TRex\Cli\Executor')->getMock();
        $executor->expects($this->once())
            ->method('flush')
            ->with('composer create-project vendor/package dir/path 1.0.0')
            ->will($this->returnValue('result'));

        $composer = new Composer($project, $executor);
        $this->assertSame('result', $composer->createProject());
    }

    public function testCreateProjectWithVersionAndOptions()
    {
        $project = new Project();
        $project->setBootstrapName('vendor/package');
        $project->setBootstrapVersion('1.0.0');
        $project->setDirectoryPath('dir/path');

        $executor = $this->getMockBuilder('TRex\Cli\Executor')->getMock();
        $executor->expects($this->once())
            ->method('flush')
            ->with('composer create-project vendor/package dir/path 1.0.0 --repository-url=url')
            ->will($this->returnValue('result'));

        $composer = new Composer($project, $executor);
        $this->assertSame('result', $composer->createProject(['repository-url' => 'url']));
    }

    public function testGetConfigPath()
    {
        $project = new Project();
        $composer = new Composer($project, new Executor());
        $this->assertSame('composer.json', $composer->getConfigPath());
    }

    public function testGetConfigPathWithDirectoryPath()
    {
        $project = new Project();
        $project->setDirectoryPath('dir/path');
        $composer = new Composer($project, new Executor());
        $this->assertSame('dir/path/composer.json', $composer->getConfigPath());
    }

    public function testGetConfigWithoutFile()
    {
        $project = new Project();
        $composer = new Composer($project, new Executor());
        $this->assertNull($composer->getConfig());
    }

    public function testGetConfigWithFile()
    {
        $project = new Project();
        $project->setDirectoryPath(__DIR__ . '/resources');
        $composer = new Composer($project, new Executor());
        $this->assertSame(
            [
                'name' => 'raphhh/samurai',
                'description' => 'desc',
                'license' => 'MIT',
                'version' => '1.0.0',
                'time' => '1999-12-31',
            ],
            $composer->getConfig()
        );
    }

    public function testValidateConfigWithoutFile()
    {
        $project = new Project();

        $executor = $this->getMockBuilder('TRex\Cli\Executor')->getMock();
        $executor->expects($this->once())
            ->method('flush')
            ->with('composer validate')
            ->will($this->returnValue(false));

        $composer = new Composer($project, $executor);
        $this->assertFalse($composer->validateConfig());
    }

    public function testValidateConfigWithFile()
    {
        $project = new Project();
        $project->setDirectoryPath(__DIR__ . '/resources');

        $executor = $this->getMockBuilder('TRex\Cli\Executor')->getMock();
        $executor->expects($this->once())
            ->method('flush')
            ->with('cd '.__DIR__ .'/resources && composer validate')
            ->will($this->returnValue(true));

        $composer = new Composer($project, $executor);
        $this->assertTrue($composer->validateConfig());
    }

    public function testResetConfig()
    {
        $project = new Project();
        $composer = new Composer($project, new Executor());

        $this->setExpectedException('\RuntimeException', 'Impossible to load the composer config from file "composer.json"');
        $composer->resetConfig();
    }

    public function testResetConfigWithFile()
    {

        $project = new Project();
        $project->setDirectoryPath(__DIR__ . '/resources');
        $composer = new Composer($project, new Executor());
        $fileContent = file_get_contents($composer->getConfigPath());

        $this->assertSame(
            [
                'name' => 'raphhh/samurai',
                'description' => 'desc',
                'license' => 'MIT',
                'version' => '1.0.0',
                'time' => '1999-12-31',
            ],
            $composer->getConfig()
        );

        $composer->resetConfig();
        $this->assertSame(
            [
                'license' => 'MIT',
            ],
            $composer->getConfig()
        );

        file_put_contents($composer->getConfigPath(), $fileContent);
    }

    public function testResetConfigWithOverride()
    {
        $project = new Project();
        $project->setDirectoryPath(__DIR__ . '/resources');
        $composer = new Composer($project, new Executor());
        $fileContent = file_get_contents($composer->getConfigPath());

        $this->assertSame(
            [
                'name' => 'raphhh/samurai',
                'description' => 'desc',
                'license' => 'MIT',
                'version' => '1.0.0',
                'time' => '1999-12-31',
            ],
            $composer->getConfig()
        );

        $project->setName('raphhh/samurai2');
        $project->setDescription('desc2');

        $composer->resetConfig();
        $this->assertSame(
            [
                'name' => 'raphhh/samurai2',
                'description' => 'desc2',
                'license' => 'MIT',
            ],
            $composer->getConfig()
        );

        file_put_contents($composer->getConfigPath(), $fileContent);
    }
}
