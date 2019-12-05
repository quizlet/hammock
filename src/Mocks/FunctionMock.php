<?hh // strict

namespace Hammock\Mocks;

use type Hammock\InterceptedCall;
use type Hammock\Interfaces\IFunctionMock;
use type Hammock\Persistent\Mocks\PersistentFunctionMock;

class FunctionMock implements \IDisposable, IFunctionMock {
	public function __construct(protected PersistentFunctionMock $delegate) {}

	public function getCalls(): vec<InterceptedCall> {
		return $this->delegate->getCalls();
	}

	public function getNumCalls(): int {
		return $this->delegate->getNumCalls();
	}

	public function getArgsForCall(int $i): vec<mixed> {
		return $this->delegate->getArgsForCall($i);
	}

	public function __dispose(): void {
		$this->delegate->deactivate();
	}
}
