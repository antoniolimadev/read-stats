<?php

namespace App\Goodreads;

class Book {
	public $title;
	public $author;
	public $year;
	public $pages;
	public $started_at;
	public $read_at;
	public $timeToRead;
	public $yearRead;
	public $averageRating;
	public $ratingCount;
	public $userRating;
	public $websiteURL;
	public $coverURL;
	public $authorURL;
	public $authorPhotoURL;

	public function __construct($title, $author, $year, $pages, $started_at, $read_at, $timeToRead, $yearRead,
								$averageRating, $ratingCount, $userRating, $websiteURL, $coverURL, $authorURL, $authorPhotoURL){

		$this->title = (string) $title;
		$this->author = (string) $author;
		$this->year = (int) $year;
		$this->pages = (int) $pages;
		$this->started_at = $started_at;
		$this->read_at = $read_at;
		$this->timeToRead = $timeToRead;
		$this->yearRead = $yearRead;
		$this->averageRating = (Double) $averageRating;
		$this->ratingCount = (int) $ratingCount;
		$this->userRating = (Double) $userRating;
		$this->websiteURL = $websiteURL;
		$this->coverURL = $coverURL;
		$this->authorURL = $authorURL;
		$this->authorPhotoURL = $authorPhotoURL;
	}
}
