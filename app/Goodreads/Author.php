<?php

namespace App\Goodreads;

class Author {
	public $name;
	public $authorURL;
	public $authorPhotoURL;
	public $booksRead;

	public function __construct($name, $authorURL, $authorPhotoURL, $booksRead){

		$this->name = $name;
		$this->authorURL = $authorURL;
		$this->authorPhotoURL = $authorPhotoURL;
		$this->booksRead = $booksRead;
	}
}
