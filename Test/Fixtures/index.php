<?hh // strict

namespace Hammock\Fixtures;

function return_input<T>(T $input): T {
	return $input;
}

function return_inputs<T1, T2, T3>(
	T1 $input1,
	T2 $input2,
	T3 $input3,
): (T1, T2, T3) {
	return tuple($input1, $input2, $input3);
}
