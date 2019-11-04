<?hh // strict

namespace Hammock\Persistent\Mocks;

use type Hammock\MockCallback;

class ObjectMock<T> extends PersistentMethodMockContainer {
	public function __construct(protected T $object) {}

	protected function createMethodMock(
		string $methodName,
		MockCallback $callback,
	): PersistentFunctionMock {
		return new ObjectMethodMock($this->object, $methodName, $callback);
	}
}
