<?hh // strict

namespace Hammock\Interfaces;

use type Hammock\InterceptedCall;

interface IFunctionMock {
	public function getCalls(): vec<InterceptedCall>;
	public function getNumCalls(): int;
	public function getArgsForCall(int $i): vec<mixed>;
}
