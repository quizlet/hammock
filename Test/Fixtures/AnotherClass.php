<?hh // strict

namespace Hammock\Fixtures;

class AnotherClass {
	public function anotherReturnInput<T>(T $input): T {
		return $input;
	}

	public static function anotherStaticReturnInput<T>(T $input): T {
		return $input;
	}
}
