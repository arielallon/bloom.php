bloom.php
=========

A PHP implementation of a Bloom filter.


A Bloom filter is a space-efficient (and therefore potentially time-efficient since you can keep it in-memory) data-structure to give you a high probability test of whether a set contains an element.
It has the potential for false positives, but never false negatives. That is, if the bloom filter says it's not there, it's definitely not; if the bloom filter says it's there, it [very] probably is.

You can read more about Bloom filters here: http://en.wikipedia.org/wiki/Bloom_filter