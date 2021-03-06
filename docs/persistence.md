
## Persisting "Objects"

For simple use cases an `ActionPersister` class is available. This allows for storing, updating
or deleting records via API calls: POST, PUT and DELETE. The basic implementation makes use
of form-data and sends a standard request. The implementation can be customised or swapped
out entirely.

The persister works with `ApiActionInterface` objects that should provide:

 * the hydrating class
 * the route and parameters (must be valid in the Connection passed to the persister)
 * the properties to change / send to the API
 
Unlike the `Model`, the `ActionPersister` is not keyed to a particular class. This is defined
on the action. Custom actions can be passed, provided they implement the interface. For
updates and deletes, the route parameter values are hashed together to act as an id value for
logging / exception purposes.

Errors and exceptions from all methods are converted to `ActionPersisterException` instances.
For errors derived from a JSON decoded response, the errors are parsed out and made available
via the `->getErrors()` method. The original response is kept in the exception.

`store` and `update` will attempt to return a hydrated object - provided that the API returns
the representation after the action is performed.

For complex persistence requirements, implement your own solution.

It is recommended to either extend the `AbstractAction` or implement your own typed actions
for your specific use cases. Strongly typing constructors / arguments will ensure that any
API requests will be verified before they are dispatched, reducing the number of round trips
to persist changes.

### Persisting "null" values

Sometimes it is advantageous to be able to send "null" as the value for a field. Unfortunately
the Symfony HttpClient uses `http_build_query` under the hood to normalise the body data. This
function will strip all keys with null values, however it will leave false, 0 and empty string
as-is.

Your options in this case are:

 * substitute empty string or another value to stand in for null
 * send a JSON payload through a custom request call (use `['json' => [..array of data..]]`)
