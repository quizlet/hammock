<?hh // strict

namespace Hammock\Persistent\Mocks;

use namespace HH\Lib\{C, Str};
use type Hammock\Exceptions\HammockException;
use type Hammock\Interfaces\{IFunctionMock, IDeactivatable};
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
				Str\format("Cannot access index %d of calls (total number of calls: %d).", $i, $numCalls),
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
