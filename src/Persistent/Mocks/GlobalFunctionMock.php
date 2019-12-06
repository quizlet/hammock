<?hh // strict

namespace Hammock\Persistent\Mocks;

use type Hammock\MockManager;
use type Hammock\{InterceptedCall, MockCallback};

class GlobalFunctionMock extends PersistentFunctionMock {
	public function __construct(
		protected string $globalFunctionName,
		MockCallback $callback,
	) {
		MockManager::mockGlobalFunction($globalFunctionName, $callback);
	}

  <<__Override>>
	protected function actuallyGetCalls(): vec<InterceptedCall> {
		return MockManager::getGlobalFunctionCalls($this->globalFunctionName);
	}

  <<__Override>>
	protected function actuallyDeactivate(): void {
		MockManager::unmockGlobalFunction($this->globalFunctionName);
	}
}
