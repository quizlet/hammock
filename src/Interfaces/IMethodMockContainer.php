<?hh // strict

namespace Hammock\Interfaces;

use type Hammock\MockCallback;

interface IMethodMockContainer {
	public function mockMethod(
		string $methodName,
		MockCallback $callback,
	): IFunctionMock;

	public function spyMethod(string $methodName): IFunctionMock;
	public function noopMethod(string $methodName): IFunctionMock;
	public function getMethodMock(string $methodName): IFunctionMock;
}
