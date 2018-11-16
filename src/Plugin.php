<?php

namespace DJWP\WPPreCommitHook;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\LibraryInstaller;
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

/**
 * Class Plugin
 * @package DJWP\WPPreCommitHook
 */
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
	 * @var String specify vendor directory from composer.json.
	 */
	private $vendorDir;

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
		// Added to copy the files to .git/hooks.
		$instance = new static();
		$instance->onDependenciesChangedEvent( $event );
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
	 * @param Event $event
	 *
	 * @throws \RuntimeException
	 * @throws LogicException
	 * @throws ProcessFailedException
	 * @throws RuntimeException
	 */
	private function init() {
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
	 *
	 * Find the relative file system path between two file system paths
	 *
	 * @param  string $frompath Path to start from
	 * @param  string $topath Path we want to end up in
	 *
	 * @return string             Path leading from $frompath to $topath
	 */
	private function find_relative_path( String $frompath, String $topath ) {
		$from    = explode( DIRECTORY_SEPARATOR, $frompath ); // Folders/File
		$to      = explode( DIRECTORY_SEPARATOR, $topath ); // Folders/File
		$relpath = '';

		$i = 0;
		// Find how far the path is the same
		while ( isset( $from[ $i ] ) && isset( $to[ $i ] ) ) {
			if ( $from[ $i ] != $to[ $i ] ) {
				break;
			}
			$i ++;
		}
		$j = count( $from ) - 1;
		// Add '..' until the path is the same
		while ( $i <= $j ) {
			if ( ! empty( $from[ $j ] ) ) {
				$relpath .= '..' . DIRECTORY_SEPARATOR;
			}
			$j --;
		}
		// Go to folder from where it starts differing
		while ( isset( $to[ $i ] ) ) {
			if ( ! empty( $to[ $i ] ) ) {
				$relpath .= $to[ $i ] . DIRECTORY_SEPARATOR;
			}
			$i ++;
		}

		// Strip last separator
		return substr( $relpath, 0, - 1 );
	}

	/**
	 * Entry point for post install and post update events.
	 *
	 * @todo Copy a Windows-version of the pre-commit script on WIN platforms.
	 *
	 * @param Event $event
	 *
	 * @throws \InvalidArgumentException
	 * @throws RuntimeException
	 * @throws LogicException
	 * @throws ProcessFailedException
	 */
	public function onDependenciesChangedEvent( Event $event ) {

		$configVal = $event->getComposer()->getConfig()->get( 'config' );
		var_dump($configVal);
		$vendorDir = ( isset( $configVal['vendor-dir'] ) ) ? $configVal['vendor-dir'] : 'vendor';
		var_dump( "Vendor dir: " . $vendorDir );

		// Find TargetDir from the
		$targetDir = getcwd() . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR . 'hooks';

		// Relative path between the value set as vendor-dir and /.git in the local repo.
		$path_diff = $this->find_relative_path( getcwd() . DIRECTORY_SEPARATOR . $vendorDir, $targetDir );
		$path_diff .= '.git' . DIRECTORY_SEPARATOR . 'hooks';

		if ( ! is_dir( $path_diff ) ) {
			mkdir( $path_diff, 0775, true );
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
			print( "Install commit hooks \n" );
			foreach ( $commit_hooks as $hook ) {
				print( ' ' . $hook . ' ' );
				copy( __DIR__ . DIRECTORY_SEPARATOR . $hook, $path_diff . DIRECTORY_SEPARATOR . $hook );
				chmod( $path_diff . DIRECTORY_SEPARATOR . $hook, 0775 );
			}
		}
	}

}
