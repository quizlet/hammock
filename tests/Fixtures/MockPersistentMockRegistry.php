<?hh // strict

namespace Hammock\Fixtures;

use type Hammock\Interfaces\IDeactivatable;
use type Hammock\Persistent\PersistentMockRegistry;

class MockPersistentMockRegistry extends PersistentMockRegistry {
	public static function getRegistry(): vec<IDeactivatable> {
		return self::$registry;
	}
}
