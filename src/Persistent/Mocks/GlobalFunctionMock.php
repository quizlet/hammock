<?hh // strict

namespace Hammock\Persistent\Mocks;

use Hammock\MockManager;
use type Hammock\{InterceptedCall, MockCallback};

class GlobalFunctionMock extends PersistentFunctionMock {
	public function __construct(
		protected string $globalFunctionName,
		MockCallback $callback,
	) {
		MockManager::mockGlobalFunction($globalFunctionName, $callback);
	}

	protected function actuallyGetCalls(): vec<InterceptedCall> {
		return MockManager::getGlobalFunctionCalls($this->globalFunctionName);
	}

	protected function actuallyDeactivate(): void {
		MockManager::unmockGlobalFunction($this->globalFunctionName);
	}
}
