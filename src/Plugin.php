<?php

namespace DJWP\WPPreCommitHook;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\AliasPackage;
use Composer\Package\PackageInterface;
use Composer\Package\RootpackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\ProcessBuilder;

class Plugin implements PluginInterface, EventSubscriberInterface {


	/**
	 * @var Composer
	 */
	private $composer;

	/**
	 * @var IOInterface
	 */
	private $io;

	/**
	 * Triggers the plugin's main functionality.
	 *
	 * Makes it possible to run the plugin as a custom command.
	 *
	 * @param Event $event
	 *
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 * @throws LogicException
	 * @throws ProcessFailedException
	 * @throws RuntimeException
	 */
	public static function run( Event $event ) {
		$io                 = $event->getIO();
		$composer           = $event->getComposer();
		$instance           = new static();
		$instance->io       = $io;
		$instance->composer = $composer;
		$instance->init();
//		$instance->onDependenciesChangedEvent();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \RuntimeException
	 * @throws LogicException
	 * @throws RuntimeException
	 * @throws ProcessFailedException
	 */
	public function activate( Composer $composer, IOInterface $io ) {
		$this->composer = $composer;
		$this->io       = $io;
		$this->init();
	}

	/**
	 * Prepares the plugin so it's main functionality can be run.
	 *
	 * @throws \RuntimeException
	 * @throws LogicException
	 * @throws ProcessFailedException
	 * @throws RuntimeException
	 */
	private function init() {
		// Added to copy the files to .git/hooks.
		$instance = new static();
		$instance->onDependenciesChangedEvent();
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents() {
		return array(
			ScriptEvents::POST_INSTALL_CMD => array(
				array( 'onDependenciesChangedEvent', 0 ),
			),
			ScriptEvents::POST_UPDATE_CMD  => array(
				array( 'onDependenciesChangedEvent', 0 ),
			),
		);
	}

	/**
	 * Entry point for post install and post update events.
	 *
	 * @todo Copy a Windows-version of the pre-commit script on WIN platforms.
	 *
	 * @throws \InvalidArgumentException
	 * @throws RuntimeException
	 * @throws LogicException
	 * @throws ProcessFailedException
	 */
	public function onDependenciesChangedEvent() {
		$targetDir = getcwd() . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR . 'hooks';
		if ( ! is_dir( $targetDir ) ) {
			mkdir( $targetDir, 0775, true );
		}

		// Commit hooks to be installed.
		$commit_hooks = [
			'commit-msg',
			'post-checkout',
			'pre-commit',
			'pre-push',
			'prepare-commit-msg',
			'include.sh',
			'compile.sh'
		];

		if ( 'WIN' === strtoupper( substr( PHP_OS, 0, 3 ) ) ) {
			print( 'Windows will not run bash scripts. If you have cygwin installed, please copy them manually from the /src folder to your .git/hooks .' );
			// We currently don’t have a Windows version to install, so let’s just not break anything for now.
		} else {
			foreach ( $commit_hooks as $hook ) {
				print( 'Install commit hook: ' . $hook );
				copy( __DIR__ . DIRECTORY_SEPARATOR . $hook, $targetDir . DIRECTORY_SEPARATOR . $hook );
				chmod( $targetDir . DIRECTORY_SEPARATOR . $hook, 0775 );
			}
		}
	}

}
