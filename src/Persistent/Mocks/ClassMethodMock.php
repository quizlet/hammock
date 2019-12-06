<?hh // strict

namespace Hammock\Persistent\Mocks;

use type Hammock\MockManager;
use type Hammock\{InterceptedCall, MockCallback};

class ClassMethodMock<T> extends PersistentFunctionMock {
	public function __construct(
		protected classname<T> $className,
		protected string $methodName,
		protected MockCallback $callback,
	) {
		MockManager::mockClassMethod($className, $methodName, $callback);
	}

	<<__Override>>
	protected function actuallyGetCalls(): vec<InterceptedCall> {
		return MockManager::getClassMethodCalls(
			$this->className,
			$this->methodName,
		);
	}

	<<__Override>>
	protected function actuallyDeactivate(): void {
		MockManager::unmockClassMethod($this->className, $this->methodName);
	}
}
