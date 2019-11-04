<?hh // strict

namespace Hammock\Persistent\Mocks;

use Hammock\MockManager;
use type Hammock\{InterceptedCall, MockCallback};

class ObjectMethodMock<T> extends PersistentFunctionMock {
	public function __construct(
		protected T $object,
		protected string $methodName,
		protected MockCallback $callback,
	) {
		MockManager::mockObjectMethod($object, $methodName, $callback);
	}

	protected function actuallyGetCalls(): vec<InterceptedCall> {
		return MockManager::getObjectMethodCalls($this->object, $this->methodName);
	}

	protected function actuallyDeactivate(): void {
		MockManager::unmockObjectMethod($this->object, $this->methodName);
	}
}
