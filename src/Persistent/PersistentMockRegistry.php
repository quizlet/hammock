<?hh // strict

namespace Hammock\Persistent;

use namespace HH\Lib\{C, Vec};
use type Hammock\Interfaces\IDeactivatable;

class PersistentMockRegistry {
	const int REGISTRY_SOFT_LIMIT = 100;
	const int PRUNE_INTERVAL = 10;
	protected static vec<IDeactivatable> $registry = vec[];
	protected static int $pruneAttempts = 0;

	public static function register(IDeactivatable $persistentMock): void {
		self::$registry[] = $persistentMock;

		// When the registry grows beyond REGISTRY_SOFT_LIMIT size,
		// we prune the registry every PRUNE_INTERVAL calls to register
		// by removing persistent mocks that have been manually deactivated.
		//
		// If the number of items in the registry drops below REGISTRY_SOFT_LIMIT,
		// the number of attempts is reset to zero.
		if (C\count(self::$registry) >= self::REGISTRY_SOFT_LIMIT) {
			if (self::$pruneAttempts % self::PRUNE_INTERVAL == 0) {
				self::$registry = Vec\filter(self::$registry, $mock ==> !$mock->isDeactivated());
			}

			self::$pruneAttempts += 1;
		} else {
			self::$pruneAttempts = 0;
		}
	}

	public static function deactivateAllPersistentMocks(): void {
		foreach (self::$registry as $persistentMock) {
			$persistentMock->deactivate();
		}

		self::$registry = vec[];
	}
}
