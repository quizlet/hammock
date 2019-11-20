# Persistent Mocks

Persistent mocks are useful when trying to override a function's behavior with a stub, as well as track the calls into the overridden function. This requires `deactivate_all_persistent_mocks()` to be called when mocks are no longer needed.

