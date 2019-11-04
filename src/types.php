<?hh // strict

namespace Hammock;

type MockCallback = (function(vec<mixed>): mixed);

// Data about an individual call to a mock function.
type InterceptedCall = shape(
	'args' => vec<mixed>,
	'object' => mixed,
);
