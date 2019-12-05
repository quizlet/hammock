<?hh // strict

namespace Hammock\Persistent;

use type Hammock\Interfaces\IDeactivatable;

class PersistentMockRegistry {
	protected static vec<IDeactivatable> $registry = vec[];

	public static function register(IDeactivatable $persistentMock): void {
		// NOTE: When the registry grows beyond a certain threshold,
		// we can prune the registry by removing persistent mocks
		// that have been manually deactivated. This will require
		// an `isDeactivated` interface on `IDeactivatable`.
		self::$registry[] = $persistentMock;
	}

	public static function deactivateAllPersistentMocks(): void {
		foreach (self::$registry as $persistentMock) {
			$persistentMock->deactivate();
		}

		self::$registry = vec[];
	}
}
