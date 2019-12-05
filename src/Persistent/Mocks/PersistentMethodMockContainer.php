<?hh // strict

namespace Hammock\Persistent\Mocks;

use Hammock\Exceptions\HammockException;
use Hammock\Interfaces\{IFunctionMock, IMethodMockContainer, IDeactivatable};
use function Hammock\{get_noop_callback, get_spy_callback};
use namespace HH\Lib\{C, Dict, Str};
use type Hammock\MockCallback;

abstract class PersistentMethodMockContainer
	implements IMethodMockContainer, IDeactivatable {
	protected bool $isDeactivated = false;

	abstract protected function createMethodMock(
		string $methodName,
		MockCallback $callback,
	): PersistentFunctionMock;

	protected dict<string, PersistentFunctionMock> $methodMocks = dict[];

	public function mockMethod(
		string $methodName,
		MockCallback $callback,
	): PersistentFunctionMock {
		$methodMock = $this->createMethodMock($methodName, $callback);
		$this->methodMocks[$methodName] = $methodMock;

		return $methodMock;
	}

	public function spyMethod(string $methodName): IFunctionMock {
		return $this->mockMethod($methodName, get_spy_callback());
	}

	public function noopMethod(string $methodName): IFunctionMock {
		return $this->mockMethod($methodName, get_noop_callback());
	}

	public function getMethodMock(string $methodName): IFunctionMock {
		if (!C\contains_key($this->methodMocks, $methodName)) {
			throw new HammockException(
				Str\format("There is no mock for the method `%s`.", $methodName),
			);
		}

		return $this->methodMocks[$methodName];
	}

	public function deactivate(): void {
		foreach ($this->methodMocks as $methodMock) {
			$methodMock->deactivate();
		}

		$this->methodMocks = dict[];
	}
}
