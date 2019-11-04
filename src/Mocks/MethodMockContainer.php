<?hh // strict

namespace Hammock\Mocks;

use Hammock\Interfaces\{IFunctionMock, IMethodMockContainer};
use Hammock\MockCallback;
use Hammock\Persistent\Mocks\PersistentMethodMockContainer;

class MethodMockContainer implements \IDisposable, IMethodMockContainer {
	public function __construct(
		protected PersistentMethodMockContainer $delegate,
	) {}

	public function mockMethod(
		string $methodName,
		MockCallback $callback,
	): IFunctionMock {
		return $this->delegate->mockMethod($methodName, $callback);
	}

	public function spyMethod(string $methodName): IFunctionMock {
		return $this->delegate->spyMethod($methodName);
	}

	public function noopMethod(string $methodName): IFunctionMock {
		return $this->delegate->noopMethod($methodName);
	}

	public function getMethodMock(string $methodName): IFunctionMock {
		return $this->delegate->getMethodMock($methodName);
	}

	public function __dispose(): void {
		$this->delegate->deactivate();
	}
}
