<?hh // strict

namespace Hammock\Persistent\Mocks;

use type Hammock\{InterceptedCall, MockCallback, MockManager};

class ObjectMethodMock<T> extends PersistentFunctionMock {
	public function __construct(
		protected T $object,
		protected string $methodName,
		protected MockCallback $callback,
	) {
		MockManager::mockObjectMethod($object, $methodName, $callback);
	}

  <<__Override>>
	protected function actuallyGetCalls(): vec<InterceptedCall> {
		return MockManager::getObjectMethodCalls($this->object, $this->methodName);
	}

  <<__Override>>
	protected function actuallyDeactivate(): void {
		MockManager::unmockObjectMethod($this->object, $this->methodName);
	}
}
