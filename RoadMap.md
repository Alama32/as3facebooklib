# Introduction #

Here is a brief outline of the development plans for this library.


# Details #

## Iteration 1 ##

Complete unit tests for full api, and fix any errors highlighted.


## Iteration 2 ##

Fully expose the FQLQuery api and ensure it is usable and extensible.

## Iteration 3 ##

Introduce Components to represent the FBML tags.

## Iteration 4 ##

Develop an ActiveRecord style layer on top, ala Ruby on Rails.
The aim is to enable something along these lines:

```
var person:Person = new Person(1234);
var lastAlbum:Album = person.albums.find("last");
```

Where the calls to the service are handled behind the scenes.
