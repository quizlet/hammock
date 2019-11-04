<?hh // strict

namespace Hammock\Fixtures;

trait TestTrait {
	public static function traitStaticReturnInput<T>(T $input): T {
		return $input;
	}
}
