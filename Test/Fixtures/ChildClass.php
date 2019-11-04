<?hh // strict

namespace Hammock\Fixtures;

class ChildClass extends TestClass {
	<<__Override>>
	public function overriddenReturnInput<T>(T $input): T {
		return $input;
	}

	<<__Override>>
	public static function overriddenStaticReturnInput<T>(T $input): T {
		return $input;
	}
}
