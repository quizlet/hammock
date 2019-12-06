<?hh // strict

namespace Hammock;

use namespace HH\Lib\Str;
use type Hammock\Exceptions\{HammockException, PassThroughException};
use type Hammock\MockCallback;

function get_spy_callback(): MockCallback {
	return $args ==> {
		throw new PassThroughException();
	};
}

function get_noop_callback(): MockCallback {
	return $args ==> {};
}

function get_declaring_class_name<Td, T as Td>(
	classname<T> $className,
	string $methodName,
): classname<Td> {
	if (!\method_exists($className, $methodName)) {
		throw new HammockException(
			Str\format(
				"The method `%s::%s` does not exist.",
				$className,
				$methodName,
			),
		);
	}

	$parentClassName = \get_parent_class($className);

	if (
		$parentClassName === false || !\method_exists($parentClassName, $methodName)
	) {
		return $className;
	}

	// NOTE: At this point, we can assume one of two things: Either
	// the class overrides an ancestor's method, or the method is
	// declared by an ancestor. Hence, there would be a performance
	// hit from using a reflection method for overridden methods.

	return (new \ReflectionMethod($className, $methodName))->class;
}
