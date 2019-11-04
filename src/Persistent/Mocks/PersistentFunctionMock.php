<?hh // strict

namespace Hammock\Persistent\Mocks;

use HH\Lib\C;
use Hammock\Exceptions\HammockException;
use Hammock\Interfaces\{IFunctionMock, IDeactivatable};
use type Hammock\InterceptedCall;

abstract class PersistentFunctionMock implements IFunctionMock, IDeactivatable {
	abstract protected function actuallyGetCalls(): vec<InterceptedCall>;
	abstract protected function actuallyDeactivate(): void;

	protected bool $isDeactivated = false;

	public function getCalls(): vec<InterceptedCall> {
		$this->throwIfDeactivated();

		return $this->actuallyGetCalls();
	}

	public function getNumCalls(): int {
		$this->throwIfDeactivated();

		return $this->actuallyGetCalls() |> C\count($$);
	}

	public function getArgsForCall(int $i): vec<mixed> {
		$this->throwIfDeactivated();

		$numCalls = $this->getNumCalls();

		if ($i < 0 || $i >= $numCalls) {
			throw new HammockException(
				"Cannot access index {$i} of calls (total number of calls: {$numCalls}).",
			);
		}

		return $this->actuallyGetCalls()[$i]['args'];
	}

	public function deactivate(): void {
		if ($this->isDeactivated) {
			return;
		}

		$this->actuallyDeactivate();
		$this->isDeactivated = true;
	}

	protected function throwIfDeactivated(): void {
		if ($this->isDeactivated) {
			throw new HammockException(
				"This function mock has been deactivated. Further interaction with this function mock is prohibited.",
			);
		}
	}
}
