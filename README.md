# ObjRef - Remote Objects for PHP

ObjRef is a easy way to access php objetcs in another process or over the network.

Both processes need the same (or at minimum, a compatible interface) set of classes. All calls to remote objects are marshalled via a auto-generated proxy class and transfered to the other side.

You can mark objects with a `@\ObjRef\TransferObject` annotation to mark them as transferable. These objects get serialized and transfered. References in this transferobject that are no transferobjects itself are replaced by proxies.

Check out my symfony [RemoteBundle](https://github.com/UweM/ObjRef-RemoteBundle) for a symfony implementation and the [ExampleBundle](https://github.com/UweM/ObjRef-ExampleBundle) for a working demo

## Installation
Just run `composer require uwem/objref`
## Testing
The library has fully unit tests. Start `vendor/phpunit/phpunit/phpunit` to run the tests

