<?hh // strict

namespace Hammock\Persistent;

use namespace HH\Lib\{C, Vec};
use type Hammock\Interfaces\IDeactivatable;

class PersistentMockRegistry {
	const REGISTRY_SOFT_LIMIT = 100;
	protected static vec<IDeactivatable> $registry = vec[];

	public static function register(IDeactivatable $persistentMock): void {
		// When the registry grows beyond REGISTRY_SOFT_LIMIT size,
		// we prune the registry by removing persistent mocks
		// that have been manually deactivated.
		self::$registry[] = $persistentMock;

		if (C\count(self::$registry) >= self::REGISTRY_SOFT_LIMIT) {
			self::$registry = Vec\filter(self::$registry, $mock ==> !$mock->isDeactivated());
		}
	}

	public static function deactivateAllPersistentMocks(): void {
		foreach (self::$registry as $persistentMock) {
			$persistentMock->deactivate();
		}

		self::$registry = vec[];
	}
}
