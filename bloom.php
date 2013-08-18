<?php

class BloomFilter 
{
	
	// Our filter is a bit array, represented as an string.
	protected $_filter = "";
	
	protected $_size = 0;
	protected $_numberOfHashes = 0;

	public function __construct($size=null, $numberOfHashes=3)
	{
		// Sanity check and defaults for $size
		if (is_null($size)) {
			$size = PHP_INT_MAX/8;
		} elseif (!is_int($size) || $size <= 0) {
			throw new Exception("Size must be a positive integer.");
		}
		
		// Init internal vars
		$this->_size = $size*8;
		$this->_numberOfHashes = $numberOfHashes;
		
		// Prime the filter with nulls
		for ($i = 0; $i < $size; $i++) {
			$this->_filter .= chr(0);
		}
	}	
	
	/**
	 *  Adds the given $value to the filter.
	 *  
	 *  @param $value
	 */
	public function addValue($value)
	{
		$hashes = $this->_hash($value);
		foreach ($hashes as $hash) {
			$this->_string[$hash["idx"]] = chr(ord($this->_string[$hash["idx"]]) | $hash["mask"]);
		}
	}
	
	/**
	 *  Checks if the given $value has ever been added to the filter.
	 *  Bloom filter has the potential for false positives, but never false negatives.
	 *
	 *  @param $value
	 *  @return bool
	 */
	public function hasValue($value)
	{
		$hashes = $this->_hash($value);
		foreach ($hashes as $hash) {
			if ( (ord($this->_string[$hash["idx"]]) & $hash["mask"]) == 0 ) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 *  Creates and returns an array of hashes based on the provided $value.
	 *  The number of hashes provided in the array is determined by the second parameter
	 *  provided in the constructor.
	 *
	 *  The format of the return array is:
	 *  array( 0 => array( 'idx'=> _ , 'mask'=> _ ),
	 *         1 => array( 'idx'=> _ , 'mask'=> _ ),
	 *         ...
	 *        );
	 *
	 *  @param $value
	 *  @return array
	 */
	protected function _hash($value)
	{
		$results = array();
		
		for ($i = 0; $i < $this->_numberOfHashes; $i++) {
			// Get unique md5 of the input.
			// We append $i to the value before md5'ing. Since md5 snowballs, this should
			// be ok in terms of the resultant distribution of hashes.
			$md5 = md5($value . $i);
			
			// Convert second half of the hash to decimal. 
			// (longer than that spills into a float).
			$dec = hexdec(substr($md5, -15));
			
			// Mod the decimal representation by the maximum size of our filter.
			// Provided in the first param of the constructor.
			$modded = $dec % $this->_size;
			
			// Add values to $results array.
			$results[] = array(
			                    // This is the index of the char in the filter string
			                    // that we want to hit. You can think of it as a bucket.
								// (Since each char is a byte, but _size was in bits, 
								// we divide by eight).
								"idx" => floor($modded / 8),
								
								// This is the byte-sized bitmask we want to use against
								// the character we run into with 'idx'. 
								// (We mod the decimal by 8. Then we left-shift a 1 that 
								// many spots. That gurantuees that our byte is all zeros
								// except for that single 1).
								"mask" => 1 << $dec % 8
								);
		}
		
		return $results;
	}
	
	/**
	 *  A stringify. Cause why not?
	 *  The results will be pretty unintelligible as many of the chars in the string will 
	 *  not map to printable ASCII.
	 *
	 *  @return string
	 */
	public function __toString()
	{
		return $this->_filter;
	}

}