<?hh // strict

namespace Hammock\Persistent\Mocks;

use type Hammock\MockCallback;

class ClassMock<T> extends PersistentMethodMockContainer {
	public function __construct(protected classname<T> $className) {}

	protected function createMethodMock(
		string $methodName,
		MockCallback $callback,
	): PersistentFunctionMock {
		return new ClassMethodMock($this->className, $methodName, $callback);
	}
}
